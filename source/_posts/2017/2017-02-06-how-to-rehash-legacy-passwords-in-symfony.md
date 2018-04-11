---
id: 18
layout: post
title: "How to rehash legacy passwords in Symfony"
perex: "You need to import users from an old project, but but don't want to bother them with resetting their passwords just because you want to use bcrypt. Fortunately, there is a solution."
author: 14
lang: en
related_items: [26]
tweet: "Post from Community Blog: How to rehash legacy passwords in #Symfony #security"
---

*NB: Michal Špaček has suggested slightly better and more secure way of [rehashing passwords](https://pehapkari.cz/blog/2017/02/06/how-to-rehash-legacy-passwords-in-symfony/#comment-3153608090) in the comments, [check it out](https://pehapkari.cz/blog/2017/02/06/how-to-rehash-legacy-passwords-in-symfony/#comment-3153608090).*

So you've decided to send a legacy project to his well-deserved retirement and write a nice, clean code instead. But there is an asset you cannot throw away. **Users**.

If you care about web security at least a bit and you haven't lived in a cave for the last couple of years, you might have heard of a fact that **storing users passwords in plaintext is a bad thing**. So even your legacy project did hopefully use a hashing algorithm. But since you care about the security, you would like [to use bcrypt](https://security.stackexchange.com/questions/4781/do-any-security-experts-recommend-bcrypt-for-password-storage) for all users. Right now.

Well, **you might reset all passwords** and send users an e-mail requesting for resetting their passwords. Or do so automatically when they log in.

Wrong.

Users don't care and you should not bother them with requests to change their password unless really necessary (read: you've been hacked). Moreover, you don't need them to change the password. **You just need to rehash it.**

## Check the password twice
Unless you hashed your legacy passwords by a really bad algorithm (say [md5](https://en.wikipedia.org/wiki/MD5#Security)), you can just keep them in use until users log in into a new application and **rehash them to bcrypt on-the-fly**.

1. First, check all passwords by bcrypt.
2. If the check fails, try a legacy algorithm.
3. If that works, logs the user in and rehash his password to bcrypt, so next time he will log in after the first check.
4. Otherwise, login just fails normally.

First things first - you definitely need to **rewrite (or copy-paste) your legacy algorithm** as a service:

```yaml
# app/config/services.yml

app.legacy_encoder:
    class: AppBundle\Security\Encoder\LegacyEncoder
    autowire: true
```

You might want to handle this service as a lazy one, so the encoder is initialized only when really used. [Read more on lazy services](https://symfony.com/doc/current/service_container/lazy_services.html) and how to define them in the official documentation.

BCrypt algorithm is implemented as a standard encoder in Symfony. Let's **extend it** in our own service:

```yaml
# app/config/services.yml

app.password_encoder:
    class: AppBundle\Security\PasswordEncoder
    autowire: true
```

Now create a skeleton of the custom encoder. Of course, we need to inject services as well as bcrypt and legacy encoders in ours so we can use their methods we need.

```php
// src/AppBundle/Security/PasswordEncoder.php

namespace AppBundle\Security;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

final class PasswordEncoder implements PasswordEncoderInterface
{

    /**
     * @var BCryptPasswordEncoder
     */
    private $bcrypt;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LegacyEncoder
     */
    private $legacyEncoder;

    public function __construct(BCryptPasswordEncoder $bcrypt, EventDispatcherInterface $dispatcher, LegacyEncoder $legacyEncoder)
    {
        $this->bcrypt = $bcrypt;
        $this->dispatcher = $dispatcher;
        $this->legacyEncoder = $legacyEncoder;
    }

    public function encodePassword($raw, $salt) {
        return $this->bcrypt->encodePassword($raw, $salt);
    }

    // ...

}
```

**And now the fun part.** Let's rewrite the *isPasswordValid* method so it does what we want.

```php
// src/AppBundle/Security/PasswordEncoder.php

// ...

public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
{
    // check using the bcrypt algorithm first
    if ($this->bcrypt->isPasswordValid($encoded, $raw, $salt)) {
        return true;
    }

    // prevent legacy fallback when it's obvious that the password
    // has been hashed using bcrypt (hash starts with '$2y$')
    if (substr($encoded, 0, 4) === '$2y$') {
        return false;
    }

    // legacy algorithm check
    return $this->legacyEncoder->isPasswordValid($encoded, $raw, $salt);
}
```

**Simple as that.** Oh wait. But we still need to take care of rehashing!


## Rehashing passwords dynamically

If we are sure that a password has been hashed using the legacy algorithm, just **notify another custom service that will rehash it.** First, add these lines into the *isPasswordValid* method:

```php
// src/AppBundle/Security/PasswordEncoder.php

public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
{
    // ...

    // If password is encoded using the legacy algorithm, rehash it to bcrypt
    if ($result) {
        $this->dispatcher->dispatch('app.legacy_user', new GenericEvent($raw));
    }

    return $result;
}
```

**Now you can subscribe** to the *app.legacy_user* event and save the password temporarily into a service property. When the login is finished successfully, **take that password and save it into the *User* entity.**

Here's an example how to achieve that:

```php
// src/AppBundle/Security/PasswordUpdateManager.php

namespace AppBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

final class PasswordUpdateManager implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var string
     */
    private $passwordForRehash;

    public function __construct(
        EntityManagerInterface $entityManager,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->entityManager = $entityManager;
        $this->encoderFactory = $encoderFactory;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            'app.legacy_user' => 'storePasswordForRehash',
            SecurityEvents::INTERACTIVE_LOGIN => 'rehashPassword',
        ];
    }

    public function storePasswordForRehash(GenericEvent $event)
    {
        // just store the password for later use
        $this->passwordForRehash = $event->getSubject();
    }

    public function rehashPassword(InteractiveLoginEvent $event)
    {
        // this method will be triggered after each login, continue only if there is a legacy password
        if (!$this->passwordForRehash) {
            return;
        }

        // get the logged in user
        $user = $event->getAuthenticationToken()->getUser();

        // load a correct password encoder
        $encoder = $this->encoderFactory->getEncoder($user);

        // rehashing happens here
        $newPassword = $encoder->encodePassword($this->passwordForRehash, $user->getSalt());

        // now save the new password into the database
        $user->setPassword($newPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
```

And as always, don't forget to add the service definition into the *services.yml* file with its dependencies and *kernel.event_subscriber* tag.

```yaml
# app/config/services.yml

app.password_update_manager:
    class: AppBundle\Security\PasswordUpdateManager
    tags:
      - { name: kernel.event_subscriber }
    autowire: true
```

## Let's improve it
Depending on your system, this might be just a beginning.

Maybe you use **different kind of logins**, not just the interactive login. Then you need to extend the solution so the password rehashing will not be skipped. Also, you can make rehashing **completely automatic** using [Doctrine listeners](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html), so the rehashing is done each time a user changes his password.

Do you have any suggestion for an improvement? Feel free to drop a comment below!
And check out [my blog focused on Symfony and PHP development](https://ikvasnica.com/blog/).

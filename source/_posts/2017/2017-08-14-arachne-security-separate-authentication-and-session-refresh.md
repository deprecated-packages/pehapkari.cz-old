---
id: 36
layout: post
title: "Arachne/Security - Separate Authentication and Session Refresh"
perex: |
    In many cases [Nette/Security](https://github.com/nette/security) lacks the API needed for certain tasks.
    Experienced Nette users therefore often recommend using some custom solution instead.
    In this article I'll go over the known problems with user authentication and how [Arachne/Security](https://github.com/Arachne/Security) can help you solve them.
author: 5
lang: en
related_items: [37, 38, 39, 40]
tweet: "Post from Community Blog: Arachne/Security - Separate Authentication and Session Refresh #security #nettefw #symfony"
---

## Separate Authentication

For e-shops and many other applications you may need **completely separated accounts for administrators and for custommers**. While it could be solved with authorization instead, separate authentication is often preferred.

Nette/Security does not have a good support for this. Quite often programmers don't use the `Nette\Security\User` at all and instead work directly with `Nette\Http\UserStorage`. With Arachne/Security you will use `Arachne\Security\Authentication\Firewall` instead of `Nette\Security\User` - however unlike `Nette\Security\User` it is common to use multiple separate firewalls.

A firewall is basically a shield that protects a part of your application. It is an equivalent of `Nette\Security\User` with slightly different API:

- `Firewall::login(IIdentity $identity)` - unlike `User::login()` you should check the user credentials beforehand and only call this method if the user authenticated successfully.
- `Firewall::logout()` - same as `User::logout()`.
- `Firewall::getIdentity()` - use instead of `User::isLoggedIn()` and `User::getIdentity()`. Unlike `User::getIdentity()` it will only return a non-expired `IIdentity`.

```yaml
arachne.security:
    arachne.serviceCollections: Arachne\ServiceCollections\DI\ServiceCollectionsExtension
    arachne.security: Arachne\Security\DI\SecurityExtension

arachne.security:
    firewalls:
        - admin
        - front
```

The above configuration will create two separate `Arachne\Security\Authentication\Firewall` services. For autowiring you might want to improve it with your own classes.

```yaml
arachne.security:
    firewalls:
        admin: App\Module\AdminModule\Security\AdminFirewall
        front: App\Module\FrontModule\Security\FrontFirewall
```

```php
namespace App\Module\AdminModule\Security;

use Arachne\Security\Authentication\Firewall;

class AdminFirewall extends Firewall
{
    // This class is just for autowiring. You don't need to implement any methods here.
}
```

```php
namespace App\Module\FrontModule\Security;

use Arachne\Security\Authentication\Firewall;

class FrontFirewall extends Firewall
{
    // This class is just for autowiring. You don't need to implement any methods here.
}
```


## Session Refresh

In Nette/Security when a user signs in his identity is by default serialized and stored in session and is valid until he signs out again or is signed out automatically because of expiration. The problem is that if you change the privileges of a user or remove his account his session will not be updated with the new privileges or terminated right away. The user will still have the same roles he had when he signed in. Also in case an attacker steals the user's password or cookie the attacker can still use his active session even after the user's password is changed.

In Nette this is considered a feature because to solve it you would have to check the database whether the session is still valid each time he sends a request. I consider this a security issue so I added an optional mechanism to Arachne/Security to deal with this and refresh the session or force-logout the user.

You can enable this mechanism by implementing `Arachne\Security\Authentication\IdentityValidatorInterface`. **Your implementation gets the user's identity and should return an updated identity or null to force logout.**

Here is an example how to register and implement it. It is based on Doctrine and simply creates new `Identity` every time or forces logout if the user was deleted.

```yaml
services:
    admin.identityValidator:
        class: App\Module\AdminModule\Security\AdminIdentityValidator
        tags:
            arachne.security.identityValidator: admin
```

```php
namespace App\Module\AdminModule\Security;

use App\Module\AdminModule\Entity\Role;
use App\Module\AdminModule\Entity\User;
use Arachne\Security\Authentication\IdentityValidatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Nette\Security\Identity;
use Nette\Security\IIdentity;

class AdminIdentityValidator implements IdentityValidatorInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateIdentity(IIdentity $identity): ?IIdentity
    {
        $entity = $this->entityManager->getRepository(User::class)->find($identity->getId());

        if (! $entity) {
             return null;
        }

        $roles = array_map(
            function (Role $role) {
                return $role->getName();
            },
            $entity->getRoles()
        );

        return new Identity(
            $entity->getId(),
            $roles,
            [
                'name' => $entity->getName(),
                'email' => $entity->getEmail(),
            ]
        );
    }
}
```


## Conclusion

The goal of Arachne/Security is only to provide a better API then Nette/Security. It just implements what Nette could not because of BC but still internally uses some of the classes and interfaces from Nette/Security.

This article was about authentication improvements. **In the next article I'll talk about how Arachne/Security can help you with authorization.** Later you can also expect an article about annotations-based security using [Arachne/Verifier](https://github.com/Arachne/Verifier).

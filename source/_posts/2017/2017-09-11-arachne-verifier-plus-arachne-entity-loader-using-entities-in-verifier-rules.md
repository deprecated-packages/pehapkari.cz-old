---
layout: post
title: "Arachne/Verifier + Arachne/EntityLoader - Using Entities in Verifier Rules"
perex: '''
    This article demonstrates that while Arachne components are mostly independent on each other, their potential raises dramatically when you use them together. Push both <a href="https://github.com/Arachne/ParameterValidation">Arachne/ParameterValidation</a> and <a href="https://github.com/Arachne/SecurityVerification">Arachne/SecurityVerification</a> to their limits with <a href="https://github.com/Arachne/EntityLoader">Arachne/EntityLoader</a>!
'''
author: 5
reviewed_by: [1]
lang: en
---

## Wait a Second

This article is about advanced features which are available when you use several Arachne components. Make sure to read all of the previous articles about Arachne before reading further:

- [Arachne/Security - Separate Authentication and Session Refresh](/blog/2017/08/14/arachne-security-separate-authentication-and-session-refresh)
- [Arachne/Security - Simplified Authorizator and Fixed ACL Callbacks](/blog/2017/08/21/arachne-security-simplified-authorizator-and-fixed-acl-callbacks)
- [Arachne/Verifier - Request Validator for Nette/Application](/blog/2017/08/28/arachne-verifier-request-validator-for-nette-application)
- [Arachne/EntityLoader - Object Parameters for Nette/Application](/blog/2017/09/04/arachne-entity-loader-object-parameters-for-nette-application)

Now how exactly can EntityLoader make Verifier rules better? It's simple. **All Verifier rules work with `Nette\Application\Request`. And since EntityLoader loads entities into it, Verifier rules can now use these entities to do more powerful validations.**


## Improving `@Validate` rule from ParameterValidation

With EntityLoader the capabilities of this rule increase dramatically - you can easily verify that the entity is in a state required for that action.

```php
use Arachne\SecurityVerification\Rules\Privilege;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * Product page is only visible if the product is marked as public.
 *
 * @Validate(parameter="product.public", constraints=@IsTrue()),
 */
public function actionShow(Product $product): void
{
    // ...
}
```

The `product.public` part in the annotation is a syntax from [Symfony/PropertyAccess](https://symfony.com/doc/current/components/property_access.html) which is used internally here.

With all the [Symfony/Validator constraints](https://symfony.com/doc/current/reference/constraints.html) you can make sure the entity meets all the requirements needed to perform the action. **This is especially useful for entities with complex live process when you want to make sure that the user does not skip any step by accident.**

On a side note if you're interested in some commonly unknown Symfony/Validator tricks read [my](/blog/2017/02/11/symfony-validator-comparison-constraints/) [previous](/blog/2017/02/18/symfony-validator-conditional-constraints/) [articles](/blog/2017/02/24/symfony-validator-dynamic-constraints/).


## Improving `@Privilege` rule from SecurityVerification

The magic here is that instead of `@Privilege(authorizator="admin", resource="Article", privilege="edit")` you can use `@Privilege(authorizator="admin", resource="$article", privilege="edit")` (note the little change in resource) to reference a parameter from request. If this parameter is an entity implementing the `Nette\Security\IResource` interface, you can use it as the resource to **solve complex cases like ownership-based rules** using the improved ACL callbacks from [Arachne/Security](https://github.com/Arachne/Security).

First an entity implementing IResource:

```php
namespace App\Entity;

use Nette\Security\IResource;

class Article implements IResource
{
    // ...

    /**
     * @var User
     */
    private $author;

    public function getAuthor(): User
    {
        return $this->author;
    }
}
```

Next ACL-based authorizator with an ownership rule.

```php
namespace App\Module\AdminModule\Security;

use App\Module\AdminModule\Security\AdminFirewall;
use Arachne\Security\Authentication\FirewallInterface;
use Arachne\Security\Authorization\AuthorizatorInterface;
use Arachne\Security\Authorization\Permission;
use Arachne\Security\Authorization\PermissionAuthorizator;

class AuthorizatorFactory
{
    /**
     * @var AdminFirewall
     */
    private $firewall;

    public function __construct(AdminFirewall $firewall)
    {
        $this->firewall = $firewall;
    }

    public function create(): AuthorizatorInterface
    {
        $permission = new Permission();

        // Setup $permission using addRole, addResource and allow methods.

        // Redactor can only edit his own articles.
        $permission->allow(
            'Redactor',
            Article::class,
            'edit',
            function (IIdentity $identity, IResource $article) {
                return $identity->getId() === $article->getAuthor()->getId();
            }
        );

        return new PermissionAuthorizator($this->firewall, $permission);
    }
}
```

And finally the rule declaration in the presenter.

```php
use App\Entity\Article;
use Arachne\SecurityVerification\Rules\Privilege;
use Nette\Application\UI\Presenter;

class ArticlePresenter extends Presenter
{
    /**
     * @Privilege(authorizator="admin", resource="$article", privilege="edit")
     */
    public function actionEdit(Article $article): void
    {
        // ...
    }
}
```

**This will also affect links with the `n:ifLinkVerified` macro. You can just iterate over the articles normally in your grid and the edit button will only appear for the articles owned by the user.**


## Final Words

Thanks for reading though all five articles to get down here. I'll be happy to read your feedback here in the comments or on GitHub.

This is the end of the series for now but I want to write an article about [Arachne/ServiceCollections](https://github.com/Arachne/ServiceCollections) later which is sort of Arachne internals but could find some usage in your libraries as well.

---
layout: post
title: "Arachne/Verifier - Request Validator for Nette/Application"
perex: '''
    The concept behind <a href="https://github.com/Arachne/Verifier">Arachne/Verifier</a> was originally meant to solve annotations-based authorization for <a href="https://github.com/nette/application">Nette/Application</a>.
    Now after years of development it is no longer limited to neither annotations nor authorization making it a very powerful tool for your security layer.
'''
author: 5
reviewed_by: [1]
lang: en
---

## The Basics

With [Arachne/Verifier](https://github.com/Arachne/Verifier) you can add rules to your presenters, actions, signals and components. They will be available only if all the rules are met, otherwise the request is denied.

```php
use App\Entity\Product;
use Arachne\SecurityVerification\Rules\Privilege;
use Nette\Application\UI\Presenter;
use Symfony\Component\Validator\Constraints\IsTrue;

class ArticlePresenter extends Presenter
{
    /**
     * @Privilege(authorizator="admin", resource="Article", privilege="edit")
     */
    public function actionEdit(int $id): void
    {
        // ...
    }
}
```

The point is that you don't need to repeat these rules in your Latte templates to determine when to show which link. There is a macro to simplify this and keep your code [DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself).

```
{* Standard Nette way *}
{if $user->isAllowed(Article, edit)}
    <a n:href="Article:edit $article->getId()">Edit</a>
{/if}

{* Simplified using Arachne/Verfier *}
<a n:ifLinkVerified="Article:edit $article->getId()" n:href>Edit</a>
```


### Available rules

First install Arachne/Verifier according to [documentation](https://github.com/Arachne/Verifier/blob/master/docs/index.md).

Next you'll need some rules for Verifier. **Arachne has 3 packages providing some rules.**


### 1. [Arachne/SecurityVerification](https://github.com/Arachne/SecurityVerification)

This package provides rules for [Arachne/Security](https://github.com/Arachne/Security). Read my [previous](/blog/2017/08/14/arachne-security-separate-authentication-and-session-refresh) [articles](/blog/2017/08/21/arachne-security-simplified-authorizator-and-fixed-acl-callbacks) for details. There are 4 rules in total:

#### `@Identity(firewall="admin")`

The most basic rule that simply requires the user to be authenticated via the specified Arachne/Security firewall.

#### `@NoItentity(firewall="admin")`

The opposite of the previous rule, allows only non-authenticated users. This can be used for example to show ads only to unregistered users.

#### `@Role(firewall="admin", role="Redactor")`

Restricts access to just users with the specified role.

#### `@Privilege(authorizator="admin", resource="Article", privilege="edit")`

[ACL](https://doc.nette.org/en/2.4/access-control#toc-permission-acl) based authorization rules can solve advanced cases with configurable privileges.


### 2. [Arachne/ParameterValidation](https://github.com/Arachne/ParameterValidation)

This package provides only one rule - `@Validate`. It can be used to validate the request parameters using [Symfony/Validator constraints](http://symfony.com/doc/current/validation.html#constraints). This addon can validate the request parameters like this.

```php
use Symfony\Component\Validator\Constraints\GreaterThan;

/**
 * @Validate(parameter="price", constraints=@GreaterThan(0)),
 */
public function actionChangeProductPrice(int $id, int $price): void
{
    // ...
}
```


### 3. [Arachne/ComponentsProtection](https://github.com/Arachne/ComponentsProtection)

This package can be used to restrict components to certain actions to avoid [vulnerability](http://www.youtube.com/watch?v=ivDl8g0NEwg&t=57m4s) (video in Czech) common to many Nette-based applications. The problem is that you can for example submit an edit form using a different action than `actionEdit()` - bypassing any security conditions you check in that action.

Using this addon you can restrict your components to only certain actions using the `@Actions` annotation. To avoid omitting this annotation by accident it is strictly required for all components.

```php
use Arachne\ComponentsProtection\Rules\Actions;

class ArticlePresenter extends BasePresenter
{
    public function actionDefault()
    {
        // Using $this->getComponent('editForm') would cause an exception.
        // The point is to protect the form from using this action with an editForm-submit
        // signal which would bypass any privilege checks you might have in actionEdit.
    }

    public function actionEdit($id)
    {
        // Using $this->getComponent('editForm') will work normally.
    }

    /**
     * @Actions("edit")
     */
    public function createComponentEditForm()
    {
        // This component will be available only for edit action.
    }
}
```


## Custom Rule Provider

If your rules follow some pattern across your application you can implement `Arachne\Verifier\RuleProviderInterface` to generate the rules automatically according to your conventions instead of repeating similar rule for every action. Alternatively you can write a provider to pull rules from a neon/yaml/whatever file in case you don't like annotations.


## More information

For more information how to use Arachne/Verifier read the [documentation](https://github.com/Arachne/Verifier/blob/master/docs/index.md#usage). It covers some advanced cases such as the `@Either` and `@All` rules or the verified properties feature which is very useful for complex Nette components.

**Capabilities of both SecurityVerification and ParameterValidation can be greatly improved** by using [Arachne/EntityLoader](https://github.com/Arachne/EntityLoader) as well. I'll go into details in another article.

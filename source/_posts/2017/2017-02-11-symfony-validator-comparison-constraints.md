---
id: 20
layout: post
title: "How to use Comparison Constraints with Symfony/Validator"
perex: With Symfony/Validator there is no obvious way to implement validations like comparing a value to another property on the same object. There are several articles about this topic already but literally all of them are completely outdated. In this article I'll cover the correct way to solve this.
author: 5
lang: en
tested: true
test_slug: SymfonyValidatorComparisonConstraints
related_items: [21, 22]
tweet: "Post from Community Blog: How to use Comparison Constraints with #Symfony/#Validator"
---

**Update 2017-12-02: In Symfony 3.4+ comparison constraints have a `propertyPath` option. In this case you could use `@Assert\GreaterThanOrEqual(propertyPath="startDate")` instead of the `Expression` constraint.**

### Example use case

For date interval ensure that starting date is lower than ending date.

```php
use Symfony\Component\Validator\Constraints as Assert;

class Event
{
    /**
     * @var \DateTime
     * @Assert\Type("DateTime")
     */
    protected $startDate;

    /**
     * @todo Add validator constraint that end date cannot be lower than start date.
     * @var \DateTime
     * @Assert\Type("DateTime")
     */
    protected $endDate;
}
```


### The outdated solution

The obvious solution is to create a custom constraint and validator. It's explained in [several](https://creativcoders.wordpress.com/2014/07/19/symfony2-two-fields-comparison-with-custom-validation-constraints/) [articles](http://www.yewchube.com/2011/08/symfony-2-field-comparison-validator/) and [StackOverflow](http://stackoverflow.com/questions/15972404/symfony2-validation-datetime-1-should-be-before-datetime-2) [questions](http://stackoverflow.com/questions/8170301/symfony2-form-validation-based-on-two-fields). Note that all of these are several years old. With the solution they recommend you might easily end up with a lot of custom constraints. Other answers suggest using the Callback constraint instead but that's still more complicated than necessary.


### The correct solution

The point of this article is that Symfony actually does have an [easy mechanism](http://symfony.com/doc/current/reference/constraints/Expression.html) to do these validations. **The Expression constraint** is not something new either. It's just not covered in the documentation very well and less experienced developers can easily miss it.

The Expression constraint takes advantage of the [ExpressionLanguage](http://symfony.com/doc/current/components/expression_language.html) component. **In the expression you can use the `value` and `this` placeholders** to access the value itself and the underlying object respectively.


### Example

```php
use Symfony\Component\Validator\Constraints as Assert;

class Event
{
    /**
     * @var \DateTime
     * @Assert\Type("DateTime")
     */
    protected $startDate;

    /**
     * @var \DateTime
     * @Assert\Type("DateTime")
     * @Assert\Expression("value >= this.startDate")
     */
    protected $endDate;
}
```

Indeed it's this easy! More importantly the Expression constraint can help you solve many other situations. **Plus the ExpressionLanguage can be [extended](http://symfony.com/doc/current/components/expression_language/extending.html) with your own functions.**


### Usage with Nette Framework

If you want to use the Symfony/Validator component and the Expression constraint in your Nette application you will need these libraries:

- [Kdyby/Validator](https://github.com/Kdyby/Validator)
- [Arachne/ExpressionLanguage](https://github.com/Arachne/ExpressionLanguage)
- [Arachne/Doctrine](https://github.com/Arachne/Doctrine) (optional, enables caching for the ExpressionLanguage parser)

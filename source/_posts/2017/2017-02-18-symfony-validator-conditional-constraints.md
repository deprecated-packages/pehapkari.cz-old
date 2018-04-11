---
id: 21
layout: post
title: "How to use Conditional Constraints with Symfony/Validator"
perex: In some more complicated cases you need to do some validations only if some condition is met. This article covers the tricks you should use including a new feature in Symfony 3.2.
author: 5
lang: en
tested: true
test_slug: SymfonyValidatorConditionalConstraints
related_items: [20, 22]
tweet: "Post from Community Blog: How to use Conditional Constraints with #Symfony/#Validator"
---


### Example use case

Client can be a person or a company. In a form you need to either fill the comany name or both firstname and lastname.

The use case is a bit articifial and you might solve this particular situation otherwise. **Conditional validators are often needed in more complex use cases** that I don't want to go into here for simplicity.

```php
use Symfony\Component\Validator\Constraints as Assert;

class Client
{
    const TYPE_COMPANY = 1;
    const TYPE_PERSON = 2;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Choice({ Client::TYPE_COMPANY, CLIENT::TYPE_PERSON })
     */
    protected $type;

    /**
     * @todo Make this nonblank only if $type is TYPE_COMPANY.
     * @var string
     * @Assert\NotBlank()
     */
    protected $company;

    /**
     * @todo Make this nonblank if $type is TYPE_PERSON.
     * @var string
     */
    protected $firstname;

    /**
     * @todo Make this nonblank if $type is TYPE_PERSON.
     * @var string
     */
    protected $firstname;

    // getters and setters
}
```


### Validation groups

**The solution is to add a validation group to the constraints** and validate only one of them based on the user input.

Note the groups attribute in each of the NotBlank constraints:

```php
use Symfony\Component\Validator\Constraints as Assert;

class Client
{
    const TYPE_COMPANY = 1;
    const TYPE_PERSON = 2;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Choice({ Client::TYPE_COMPANY, CLIENT::TYPE_PERSON })
     */
    protected $type;

    /**
     * @var string
     * @Assert\NotBlank(groups = {"company"})
     */
    protected $company;

    /**
     * @var string
     * @Assert\NotBlank(groups = {"person"})
     */
    protected $firstname;

    /**
     * @var string
     * @Assert\NotBlank(groups = {"person"})
     */
    protected $lastname;
}
```


### Determining the groups to be validated

Next you need to determine which group to validate. You can do that manually when calling the validation or you can **use one of the advanced features of Symfony/Validator called `GroupSequenceProviderInterface`**.

To make the validation truly conditional it's better to let the Client entity determine which groups to validate on it's own. This is done by implementing the `getGroupSequence` method from the `GroupSequenceProviderInterface`.

```php
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @Assert\GroupSequenceProvider()
 */
class Client implements GroupSequenceProviderInterface
{
    // same as before

    public function getGroupSequence()
    {
        return [
            // Include the "Client" group to validate the $type property as well.
            // Note that using the "Default" group here won't work!
            'Client',
            // Use either the person or company group based on whether company is filled or not.
            $this->type === self::TYPE_PERSON ? 'person' : 'company',
        ];
    }
}
```

Now you can validate the client entity without specifying any groups. Symfony/Validator will recognize that it implements the GroupSequenceProviderInterface and will call the getGroupSequence() method to determine the validation groups. **The `@Assert\GroupSequenceProvider()` annotation is necessary as well!**


### Improving the validation result

Prior to Symfony 3.2 there was a drawback to this solution. Symfony/Validator runs the groups from a GroupSequence one by one and if one and skips the rest if one fails. It was not possible to get all of the violations at once, just the first group with any failure. With my pull request that was accepted into Symfony 3.2 it is now possible to validate multiple validation groups in each step. If you only use one step you can get all violations at once.

```php
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @Assert\GroupSequenceProvider()
 */
class Client implements GroupSequenceProviderInterface
{
    // same as before

    public function getGroupSequence()
    {
        // Return array of the validation steps.
        return [
            // If we want to get all violations at once we will return just
            // one validation step containing an array of the groups to validate.
            [
                'Client',
                $this->type === self::TYPE_PERSON ? 'person' : 'company',
            ]
        ];
    }
}
```

And that's it! Now Validator will validate the client differently based on the `$type` property and give you all violations.

**In the end conditional validation is surprisingly easy with Symfony/Validator.** The solution is just a bit hidden and not widely known.


### Usage with Nette Framework

If you want to use the Symfony/Validator component in your Nette application you will need [Kdyby/Validator](https://github.com/Kdyby/Validator).

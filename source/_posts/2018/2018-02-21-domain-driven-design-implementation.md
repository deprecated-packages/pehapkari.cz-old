---
id: 61
title: "Domain-Driven Design, part 4 - Implementation"
perex: |
 It is great to model something and now we have reached the point where we turn the model into the code.
 We will implement the model, no persistence, no input, only the most important part - the domain model.
 The implementation will be supported by tests and we will see how easy it is to test domain objects.
 We will also discuss the connection to the ubiquitous language and model and practical aspect of object encapsulation.
author: 29
lang: en
tested: true
test_slug: Cart/Domain
related_items: [49, 52, 54, 62, 63, 65, 66]
tweet: "Post from Community Blog: Domain-Driven Design, part 4 - Implementation #ddd #domain #php"
---

## Model

It doesn't make sense to explain implementation on *Foo, Bar, etc...*, the Domain-driven design is about the domain.
Let's use the e-shop cart model from [previous](/blog/2017/12/16/domain-driven-design-model/) [articles](/blog/2018/01/06/domain-driven-design-simplify-object-model/).

![cart aggregate](/assets/images/posts/2018/ddd-implementation/cart_aggregate.png)

## Implementing the Domain Model

Now we have the domain model and all terms come from the ubiquitous language.
We have to use the ubiquitous language in the code too, so the whole project uses the same language.
When we find something we cannot name, we have to deepen the domain knowledge so the term comes from domain again.

During the implementation, we might think of generalizing or coding for the future.
We should resist these ideas, these thoughts, and implement only the model which we know.
Implementing only the known behavior also makes the development quicker and the testing easier.

We should implement the model as accurate as possible.
But sometimes we can find a problem which is difficult or impossible to implement in the programming language we use.
Then we have to do decisions that also changes the model and these decisions should be spread over the team so everyone understands the changed model.

## Read Information from Object

A common requirement is to read an information from an object.
It doesn't have to be necessary reading the internal state, we just need to get some information.

We have two reading use cases in the cart story.
It is *showing the cart detail* and *calculating the cart total price*.
When we show the cart to the user, we also show the total price and when we show the total price, we usually show the detail or information extracted from the detail.
So we can join these two use cases into one - *calculate the cart detail*.
This decision may be changed in the future as we consider what is more practical.

### Getters Intermezzo

I think that the object-oriented programming is about encapsulating objects by behavior.
I think this is the reason for the whole OOP.
Getters are not a behavior for me, getters just break encapsulation according to my opinion.
So I try to do my best to avoid getters.
But as we can see in the code, I'm still not able to avoid all ot them.

Instead of tones of getters, I try to use immutable structures to pass information through the system.
These structures are based on use cases needs.

### Data Transfer Object over Array

Since we are programming in the PHP, arrays are the first choice how to pass multiple information.
But there are issues with arrays.
The structure cannot be thrust, we can read a certain key but it doesn't have to be there.
The data types are not thrust, is it an integer, a string or another array? IDE can not help us by suggesting keys and types.
Arrays are mutable so we don't know if the information wasn't unintentionally changed.

Data transfer objects avoid these problems.
It takes some time to write them but once we have them, it is comfortable to use them.
Data transfer objects are not classic objects, we do not expect behavior, they supplement immutable structures that are missing in the PHP.

## The Implementation

The article would be enormous if it contained all the code and the code would be difficult to navigate through.
So please find the code on the GitHub:
[https://github.com/pehapkari/pehapkari.cz/tree/master/src/Posts/Year2018/Cart/Domain](https://github.com/pehapkari/pehapkari.cz/tree/master/src/Posts/Year2018/Cart/Domain)

But comment it here, the code is a part of this article.

## Testing

Domain has nothing to do with an infrastructure like a database or a data source like a UI form or an API.
Domain objects are absolutely independent, so they are quite easy to test.
Tests should come from use cases and they should also test edge cases.

We should focus on testing aggregates.
Internal structure of aggregates may change, but their behavior is less likely to change.
We can consider aggregate tests as unit tests because an aggregate is definitely a unit.
But we can test whatever we want to, if there is an interesting internal class, it is a great idea to test it too.

Please find tests on the GitHub:
[https://github.com/pehapkari/pehapkari.cz/tree/master/tests/Posts/Year2018/Cart/Domain](https://github.com/pehapkari/pehapkari.cz/tree/master/tests/Posts/Year2018/Cart/Domain)

## Test-Driven Development

Test-driven development is popular and this approach is pretty fine.
But how can be development driven by two different paradigms? Test-driven development tells us to write tests first.
Domain-driven design tells us to focus on the domain.
How do these styles interfere with each other? Well, they actually don't.

Write tests first is great because we write tests by use cases so we know what do we expect.
It may happen that we don't know the answer to the test question.
This situation leads us to deepen the domain knowledge before writing a single line of code.

And by the way, DDD is about the design while TDD is about the development process.

The cart implementation was written using TDD.
Test first, then implementation and some refactoring:
[https://github.com/simara-svatopluk/cart/commits/TDD](https://github.com/simara-svatopluk/cart/commits/TDD)

## TL;DR

Keep ubiquitous language terms in the code, avoid getters and use data transfer objects.
Testing pure domain object is easy, so test at least aggregates.

## Complete Code

* https://github.com/simara-svatopluk/cart/tree/domain-logic **domain-logic** tag only
* https://github.com/simara-svatopluk/cart/commits/TDD

## Contact

Are you designing architecture and like DDD approach? Hire me, I can help you - [svatasimara.cz](http://svatasimara.cz/)

---
id: 62
layout: post
title: "Domain-Driven Design - Repository"
perex: '''
 We will discuss how to store and read domain objects while pretending we have an in-memory system.
 Simply, we will show how to implement and test repository.
'''
author: 29
lang: en
tested: true
test_slug: Cart/Infrastructure
related_posts: [49, 52, 54]
---

## Collections and Reality

Let´s imagine that we have a system which runs continually, has enough memory and which is for a single user only.
With this kind of system, we can have all objects in memory collections and everything is shiny.
Memory collections are enough - they allow us to store, receive and remove objects.

But real world is different.
We usually build web applications with request-process-response-die life.

![server receive request, create process, send request and kill process](/assets/images/posts/2018/ddd-repository/server-life.png)

We have to load objects out of persistent memory like a database, work with them and persist them again while the process is done.
This does not reflect the reality where everything run continually, this approach is not domain friendly.

## Repository

Whole idea is about that we keep pretending that we have collections.
Clever and persistent collections, so-called repositories.
The domain layer would seem to live continually.

Repository, same as a collection, have responsibility to add an object, get objects by identifier or complex criteria and eventually to remove an object.
There are also use cases that require aggregations like *How many objects are in the system*, *Total amount of all products in the warehouse*.
For these use cases, the repository can provide direct aggregation methods so we don't have to inefficiently fetch loads of objects.

Repositories are created for aggregates only because aggregates are our building blocks, our units.
They also always work with the whole aggregate, not with an internal part alone, not with a partial aggregate, always with the whole aggregate.

The repository is implemented in the domain layer, because it works with domain objects.
But in the domain layer we should have no idea about any database nor any storage, so the repository is just an interface.

```php
// CartRepository.php

namespace Simara\Cart\Domain;

interface CartRepository
{
    public function add(Cart $cart): void;

    /**
     * @throws CartNotFoundException
     */
    public function get(string $id): Cart;

    /**
     * @throws CartNotFoundException
     */
    public function remove(string $id): void;
}
```

### Persistence Responsibility

The repository can be responsible for persisting objects.
It would make some sense to have a saving method that instantly persists an object.

But there is no such use case with memory collections so we would have to bring infrastructure requirements into the domain.
If there was a persist operation, we'd have problems with transactions - one object is persisted and second causes an exception while persisting, so what now?

The solution is simple.
Object persistence is not the repository's responsibility.
Someone else is responsible for the persistence.
We can wrap domain use cases into a layer which is responsible for the persistence.

When we use an advanced persistence tool, it usually deals with persistence by flushing an object manager.
But it's still possible to keep references to objects we used and flush them into a storage after the domain use case is done.
This system also allows us to use transactions if the persistence system supports them.

![command is processed by wrapping layer that calls handler and flushes object manager](/assets/images/posts/2018/ddd-repository/persistence.png)

## Concrete Implementation

We can store domain objects in a relational database, in a document database, in an external system connected by API or in anything else we can imagine.
All these systems are infrastructure for the domain so the repository implementation is in the infrastructure layer.

### Inversion of Dependency

In an active record persistence pattern and similar patterns, the domain depends on the infrastructure.
But we have created a domain repository interface and the implementation is the infrastructure.
The domain layer is still independent.
For those who like SOLID, everything smoothly fit together.

### Memory Implementation

The easiest and the most instructive is a memory implementation.
An implementation which just keeps objects during a life and does not persist them at all.

The memory implementation is useful for complex component or module tests.
We integrate more system parts together but we do not use the real repository implementation.
Since everything is in the memory, tests are quick and we still test the whole component or module.

```php
// MemoryCartRepository.php

namespace Simara\Cart\Infrastructure;

use Simara\Cart\Domain\Cart;
use Simara\Cart\Domain\CartNotFoundException;
use Simara\Cart\Domain\CartRepository;

class MemoryCartRepository implements CartRepository
{
    /**
     * @var Cart[]
     */
    private $carts = [];

    public function add(Cart $cart): void
    {
        $this->carts[$cart->getId()] = $cart;
    }

    public function get(string $id): Cart
    {
        $this->checkExistence($id);
        return $this->carts[$id];
    }

    private function checkExistence(string $id): void
    {
        if (!isset($this->carts[$id])) {
            throw new CartNotFoundException();
        }
    }

    public function remove(string $id): void
    {
        $this->checkExistence($id);
        unset($this->carts[$id]);
    }
}
```

## Interface Test

We can write tests for the memory implementation and when we integrate a database storage, we can write tests for the database integration.
But these tests can be pretty similar, so we can think of testing the repository interface only.

Basic idea is that the test expects interface implementation.
The test responsibility is not to create the tested object.
Because the test is not responsible for the object creation, it can become simpler.

The furthest I get is that the test is an abstract class and the implementation test extends the abstract test.
I would prefer to have one test with several implementation providers but I didn't find a way to implement this style.
If you have a better idea, please share a comment.

```php
// CartRepositoryTest

namespace Simara\Cart\Infrastructure;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Simara\Cart\Domain\Cart;
use Simara\Cart\Domain\CartDetail;
use Simara\Cart\Domain\CartRepository;
use Simara\Cart\Domain\Price;

abstract class CartRepositoryTest extends TestCase
{
    /**
     * @var CartRepository
     */
    private $repository;

    abstract protected function createRepository(): CartRepository;

    protected function setUp()
    {
        $this->repository = $this->createRepository();
    }

    protected function flush()
    {
    }

    public function testAddAndGetSuccessfully()
    {
        $cart = new Cart($id);
        $this->repository->add($cart);
        $this->flush();

        $foundCart = $this->repository->get('1');
        $expected = new CartDetail([], new Price(0));
        Assert::assertEquals($expected, $foundCart->calculate());
    }

    // more tests
}
```

The flush method supplements the persistence and start of a new process life.
The memory implementation doesn't use it and it is prepared for a real persistent repository.

```php
// MemoryCartRepositoryTest

namespace Simara\Cart\Infrastructure;

use Simara\Cart\Domain\CartRepository;

class MemoryCartRepositoryTest extends CartRepositoryTest
{
    protected function createRepository(): CartRepository
    {
        return new MemoryCartRepository();
    }
}
```

## TL;DR

Repositories are persistent collections and allow us to pretend that the system is in-memory.
The repository works with complete aggregate and is an interface in the domain layer.
The concrete implementation is done by infrastructure which we use.
Tests can be written for the repository interface.

Next time we'll implement a database repository using the Doctrine.

## References

CLEMSON, Toby.
*Testing Strategies in a Microservice Architecture* [online].
2014 [2018-01-11].
Available: [https://martinfowler.com/articles/microservice-testing/](https://martinfowler.com/articles/microservice-testing/)

EVANS, Eric.
*Domain-driven design: tackling complexity in the heart of software*.
Boston: Addison-Wesley, 2004.
ISBN 0-321-12521-5.

VERNON, Vaughn.
*Implementing domain-driven design.* Upper Saddle River, NJ: Addision-Wesley, 2013.
ISBN 978-0-321-83457-7.

## Complete code

* https://github.com/simara-svatopluk/cart/tree/memory-repository **memory-repository** tag only

## Contact

Are you designing architecture and like DDD approach? Hire me, I can help you - [svatasimara.cz](http://svatasimara.cz/)

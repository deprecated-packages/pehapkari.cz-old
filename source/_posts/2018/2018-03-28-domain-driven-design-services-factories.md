---
id: 66
layout: post
title: "Domain-Driven Design, part 8 - Services and Factories"
perex: |
 This article is a reaction to readers’ confusion about services.
 We'll cover a domain service and domain factory in this article and when to use them and when not to.
author: 29
lang: en
related_items: [49, 52, 54, 61, 62, 63, 65]
---

Domain-driven design is about the domain.
Domain services and domain factories do not exist in the domain.
In general we shouldn't use them.

They are artificial constructions and this causes a lot of problems with code understanding, maintainability and also divergence between the domain, the model and the code.

## Domain Service

We encapsulate domain use cases into domain aggregates, entities and value objects.
This works well for many use cases.
But when a single use case involves multiple aggregates, we have a problem.

It may happen that use case doesn't naturally belong to any aggregate.
And it is awkward when one aggregate contains the domain logic of another aggregate.

A domain service is a stateless service object.
It encapsulates a single use case that involves more aggregates.

### Cart and Order Example

We have an e-shop that contains shopping carts and orders.
The order is a pretty complicated aggregate and so is the cart.

The complicated use case is *to order the cart*.
This use case involves at least two aggregates - cart and order.
It doesn't belong to the cart - the cart is a shopping box not responsible for the order.
It doesn't belong to the order - order doesn't care about source of its items.
But conversion is still a single use case.

The solution is a domain service that does exactly this complicated use case.

![service has cart as an input and order as an outpu](/assets/images/posts/2018/ddd-services-factories/service.png)

### Don't use Domain Services

Do not use domain services too much.
We usually end up with an anemic model with behavior in services.
And that is not the object-oriented programming.

### Application Service

Do not mix domain services with application services you may know.
Application services, as far as I know, are something from the application architecture.
But application services are in a different layer - application.

I think that common confusion comes from the fact that both types work with domain objects.

Remember that domain service has no state; it doesn't contain any private property.
Domain services are rare and contain complicated cross-aggregate domain logic.

## Domain Factory

Have you ever seen how the ship engine is made?
You need a whole factory for it.

Factory is an object that produces complicated aggregates or sometimes also entities and value objects.
It can be used in several scenarios.

If the object construction is complicated, it is fine to move the construction process into a separated factory instead of using complicated constructor.

![data goes into a factory and it creates an order](/assets/images/posts/2018/ddd-services-factories/factory-constructor.png)

If we have a custom repository, we can delegate the object reconstruction responsibility into the factory.
We get JSON data from an API for example, we pass them into a factory and it returns a domain object.

![api order repository gets data from api adapter, passes them into api order factory and factory returns order](/assets/images/posts/2018/ddd-services-factories/factory-json.png)

Sometimes the object construction needs some information that we do not have, like the information from an external system.
The factory can be responsible for getting the needed information and constructing the object.
In the following example, we get the order number from an external sequence.

![data goes into a factory and it creates an order](/assets/images/posts/2018/ddd-services-factories/factory-sequence.png)

### Don't use Domain Factories

Factories are heavily overused.
What we usually need is a constructor.

### Factory Pattern

An argument that new objects should be created only in factories is odd.
A typical use of the factory or the abstract factory pattern is to inject them so we are able to change the implementation.
This scenario is great for UI where we have no idea whether the operating system is linux, windows or any other.

But this is not true in the domain layer.
In the domain layer we know exactly what implementation we are using, it is the domain object.
Not any abstract object, the exact domain object.
And the only reason for changing the domain object is a domain change.
But if the domain changes, no factory can help us, we have to refactor the code.

## TL;DR

Do not use them.

## References

EVANS, Eric.
*Domain-driven design: tackling complexity in the heart of software*.
Boston: Addison-Wesley, 2004.
ISBN 0-321-12521-5.

## Contact

Are you designing architecture and like DDD approach? Hire me, I can help you - [svatasimara.cz](http://svatasimara.cz/)

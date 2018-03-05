---
id: 52
layout: post
title: "Domain-Driven Design - Model"
perex: '''
 All of us model every day.
 A friend tells us a joke, we imagine the situation and if we model it as is intended, we find the situation funny.
 A customer wants to have a new functionality and while he speaks, we try to imagine what does the customer wants - we model.

 We are going to take a look at what is software modeling, how can we express the model and how can we capture key concepts.
'''
author: 29
lang: en
related_posts: [49, 54, 61, 63]
---

## Modeling

Modeling is a process that aims to capture key concepts of reality and ignores irrelevant details.

![unshaped reality converted to shaped abstract model converted to concrete implementation model](/assets/images/posts/2017/ddd-model/modelling.png)

The reality contains everything important, all details, everything we know and much more that is hidden.
We are not able to describe the whole reality and describing it is not really our goal.

We can tell stories that cover important concepts of reality.
We can convert the stories to use cases.
We can express abstract models with diagrams and images - they are great to explain our abstract model.

### Validation

Once we have the abstract model in our heads and it is also described by documentation and in the diagrams, we should validate it.
Validation is done by domain experts who have to confirm or refute the abstract model.

![reality is modeled by abstract model and abstract model is validated by reality](/assets/images/posts/2017/ddd-model/validation.png)

Validation is usually a continuous process.
As we deepen our domain knowledge, as we model and sketch the diagrams, experts are validating and correcting the abstract model immediately.

## How to Get The Right Information

**Why?** is the most important question.
We have to ask even the simplest questions.
The question aims to clear terms, requirements, needs and helps us to avoid concealed assumptions.

What is it? Whom is it for? We want to know our target group.

What are performance requirements? It's a pretty big difference if a part of the software is used once in an hour or thousands of times per second.
High-performance requirements can lead to a model with many trade-offs.

Asking the right questions is important in order to get the right information so we are able to model correctly.
Asking the right questions is also the most difficult part of modeling.

## Shopping Cart Story

It doesn't make sense to explain modeling on *Foo, Bar, etc...*, the Domain-driven design is about the domain.
So let's tell a story about an e-shop cart.

> *In our e-shop we need a shopping cart.  You know, a normal cart that is everywhere.*

Ok, that is a good beginning, but can we explain what a cart is to a five old year child?

> *Cart... it is a box where you put products, like a barbie doll you want to buy.
Sometimes you can also remove any item you don't want to buy anymore because you found something better.
The cart is not closed, you can always look at items you have inside.
But unlike in the real world, the e-shop cart can always tell you how much everything costs together.*

We keep a conversation in the real world, we ask questions and build mental, abstract model.
The conversation would look really awkward in an article, so we just pretend it happened.

## Use Cases and Constraints

* Add the **product** to the **cart**.
* Remove the **product** from the **cart**.
* Show the **cart** detail.
* Calculate the **cart** total **price**.
* Change the **product** amount.
* Once the **item** is in the **cart**, its **price** is fixed.

## Key Concepts

Let's identify concepts, responsibilities and relations.

The whole story is about a cart, the cart is definitely the central point. The cart is a box that holds items.

We can see the term product in all use cases, it is something that customers care about, something that they add to the cart.
But in this story, we do not investigate what the product is, we assume that the product already exists in the system.
We keep an eye on the cart.

Once we add a product into the cart, more interesting things happen.
We start to call the product the item; the item is the product in the cart.
The product has its price in the cart, and the price is fixed once the product is added to the cart.
We need to hold price information.
Since an item is a product in a cart, the fixed price is related to an item.

This is our abstract model.

![cart is related to item, item is related to product and price](/assets/images/posts/2017/ddd-model/cart_model.png)

## TL;DR

We always start with an unknown reality and with an unstructured story.
We have to discover the key concepts, their relations and ignore irrelevant information and details.
Once we have the abstract model in the head, we can specify it by use cases, diagrams and stories that use ubiquitous language.
The model should be continuously validated by a domain expert so that we keep the right track.

Next time we are going to shape the abstract model into the implementation model.

## References

EVANS, Eric. *Domain-driven design: tackling complexity in the heart of software*. Boston: Addison-Wesley, 2004. ISBN 0-321-12521-5.

## Contact

Are you designing architecture and like DDD approach? Hire me, I can help you - [svatasimara.cz](http://svatasimara.cz/)

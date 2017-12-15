---
id: 49
layout: post
title: "Domain-Driven Design - Language"
perex: '''
 Domain-driven design is a software design that focuses on understanding underlying business.
 It is useful for long-term projects because it leads to high-quality software that serves users.
 It helps when dealing with difficult problems, keeps track of core problems and prevents us from getting lost in the code.
'''
author: 29
lang: en
related_posts: [52]
---

### Personal Motivation

Before I started to apply domain design ideas, I struggled with software development.
I knew how to make user interface, how to deal with API, how to work with templates, how to use design patterns.
But the most important code was hidden in services that were connected to a database.
Although I had useful tools, I had a really big problem with testing these services, the tests were slow and not maintainable.

Thanks to the domain design I started to learn how to think in object oriented programming.
I know there is still much to learn, but at least I know in which way to progress.
DDD has a huge positive impact on my architectural, modeling and testing skills.

This is just a side effect because domain-driven design is not about objects, domain-driven design is about the domain.

## Domain

Domain is an area that project covers, it has its own terminology, requirements, problems to solve.
A concrete domain is its own small world. It can be e-shop, hockey statistics, content management, our new project.
Domain has its natural boundaries too, it does not cover everything.

## Domain Language

Every domain has its own terminology, its own language.
A term in a domain means something completely different in another domain.

### Account

A bank account allows us to send and receive money and has its unique number.
Anytime we tell about an account in a bank, an account is always a bank account.
In the other hand, an account in an information system is used to authorize a user.
We have the term "account" meaning something absolutely different in two different domains.
Domain has an impact on what we imagine when someone says a concrete term.
So we have to learn and specify domain terms first.

### Price

Let's speak about e-shop domain. What is a price?
For us, as customers, it is how much we pay.
A manager can think about price as an amount that his company pays to the supplier.
For an accountant, a price is just a number.
And e-shop programmer is now confused.

Language is crucial because customers and experts are telling their stories in their language.
But it is also natural language, inaccurate, ambiguous, context-aware.
And as we can see, language can be tricky even within one domain.

## Ubiquitous Language

To reduce natural language ambiguity, we can introduce accurate language.

Is price that we are selling for always a selling price?
Do users use often term selling price instead of price?
Great, maybe experts agree that we use only the term selling price because it is definitive.
When we discuss with users, we always use term selling price, the user interface communicate selling price, we find selling price in the documentation.
What is written in code - variables, classes, methods? The selling price. Always.

The strict term became part of everyday users' life, communication between experts and programmers, programmers' work.
The term is everywhere, it became ubiquitous.

Ubiquitous language connects users, domain experts, programmers, so it creates project backbone.
It may not be a bad idea to document key terms, especially if terms have conditions of use.
The documentation may help new programmers, as well as users, to get into the project.

Be careful, ubiquitous language, this definitive, specific language must always come from domain experts or users, their stories and their conversations.
We must not invent our terms, our language. Never.

## Impact on Model

### VIP Price

Let's talk about the e-shop for a while.
Our client wants to have better prices for regular customers, so they keep buying products.
Client starts calling these prices as VIP prices since they are only for VIP customers.
This is a reasonable story.

We can think about clients use-case - we need another price.
Well, what will happen when he asks for yet another price?
We will have to rewrite our software again.
What if we generalize this task?
We can offer an unlimited amount of prices by introducing price lists.
A product can have a price in multiple price lists.
Sounds good, right? A cool feature - price lists.

### Mental Model

What client has in mind are two prices for the same product

![product with exactly two prices](/assets/images/posts/2017/ddd-language/user-product-prices.png)

We think about product, price lists, and prices

![one product, two price lists and two prices that connect product and price lists](/assets/images/posts/2017/ddd-language/programmer-product-prices.png)

When the client tells us his stories, we have to map his language to our language and our model.
When he is using software, he has to map his model to language in the user interface.
We ended with two different languages and with models that diverged.

And now imagine that a client wants to lower the VIP price.
For him it is straightforward - he wants to change a number that is called VIP price.
What is it for a programmer?
We have to find a VIP price list, find a price for a given product in the price list and finally change the value.
What would happen if the price list is not found?
If the price in price list is not found?
Now we have use cases that do not exist in client's domain.
The client can't explain what should happen because it does not make sense for him.

The model does not fit user use cases, the model language is messed up.
If we would invent our language, we would speak with users in terms they do not understand, and they would be using their language anyway because it is their domain language.
The user interface will not communicate with them in their language and using the software will cause frustration.

The domain way to solve diverged model and language is to refactor software, so user and programmer will use the same ubiquitous language.

## Domain Evolution

The domain may evolve, nothing is permanent.
Important thing is that the ubiquitous language is evolving with the domain and so is the software.
When the ubiquitous language is changed, the mental model is changed and the software is refactored by this new model.

## TL;DR

The building block of domain-driven design is the ubiquitous language.
The ubiquitous language connects people in the project, so everyone can understand each other.
We have to keep using the language that comes from the domain and never invent our own.

So simple, yet so powerful.

Next time we'll take a look at modeling.

## References

EVANS, Eric. *Domain-driven design: tackling complexity in the heart of software*. Boston: Addison-Wesley, 2004. ISBN 0-321-12521-5.

FOWLER, Martin. UbiquitousLanguage. *MartinFowler.com* [online]. 2006 [2017-11-28]. Available: [https://martinfowler.com/bliki/UbiquitousLanguage.html](https://martinfowler.com/bliki/UbiquitousLanguage.html)

## Contact

Are you designing architecture and like DDD approach? Hire me, I can help you - [svatasimara.cz](http://svatasimara.cz/)

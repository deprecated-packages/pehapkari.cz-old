---
id: 71
layout: post
title: "SyliusResourceBundle - how to develop your CRUD apps faster"
perex: |
 Brief introduction to Sylius ecosystem: SyliusResourceBundle and Rapid CRUD application development.
author: 35
lang: en
tweet: "Post from Community Blog: Introduction to @Sylius: SyliusResourceBundle - how to develop your CRUD apps faster."
---

Have you heard about Sylius? If not, you need to know that it is an e-commerce solution built on top of Symfony framework full stack. 
One of the main advantages of Sylius from the software engineer perspective is that it is developer oriented. 
High productivity and fast iteration loops are essential for us. 
That being said, SOLID and DRY are fundamental principles at our work.

## Ok, but what does it mean regarding an e-commerce framework? 

Let’s take a look at administrator panel of a typical e-commerce website. 
We can find there several usual CRUD-based resources. 
Tax rates or shipping categories are not complicated entities.
However, you need a whole stack of classes to handle each of them properly. 
In order to perform all required actions, you need to have a controller, repository, factory and some form type.

> **Disclaimer**: Not every problem should be resolved in a CRUDish way. More complex or crucial parts of your software should be solved by different structural and architectural patterns.

Our main aim was to provide a standard Symfony workflow without writing all of the classes manually or generating them.
The second goal was to provide a simple solution, which will bootstrap feature implementation at the beginning, but will be easy to customise in the further phase of development. 
And now the SyliusResourceBundle comes to play, all in white!

## What is a SyliusResourceBundle?

It is a generic, CRUD-based implementation of the most common services required for rapid application development.
Once you declare some entity as a resource, you will have access to several services such as factory, controller (with the show, index, create, update and delete actions), repository and form type.
And all of them have just a default implementation, which can be overridden when required.

## Let me see the code!

> **Note**: Example below has been crafted for Sylius v1.1 and Symfony v3.4. A [composer](https://getcomposer.org/download/) is required as well for project bootstrap.

That’s a lot of bragging, but how does it work? Let’s create a sample Sylius project so that we can save some time on a setup:

```bash
php composer.phar create-project sylius/sylius-standard Acme
```

![composer create project output](/assets/images/posts/2018/sylius-resource-bundle/create-project.png)

Go to the project directory:

```bash
cd Acme
```

And install Sylius project with default data. During this installation a new database will be created and some sample data will be loaded into it.

```bash
php bin/console sylius:install
```

![sylius install output](/assets/images/posts/2018/sylius-resource-bundle/sylius-install.png)

Once we created our project, it is high time to write a little bit of code.
We will start easy and create a new entity class, which will become our resource in the later stage of coding.
First of all, we need to create an `Entity` folder under `src/AppBundle`.
Inside of this folder, we need to create a new `ProductBundle` class and declare two simple properties inside:

```php
// src/AppBundle/Entity/ProductBundle.php
<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

class ProductBundle implements ResourceInterface
{
	/** @var int */
    private $id;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $code;

    public function getId() // Missing return type due to ResourceInterface restrictions
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
 
   public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }
}
```

The `ProductBundle` class is a simple data structure with the code, name and id properties and related getters and setter.
My recommendation is to use strict types declaration and scalar/interface type hints wherever possible, as can be spotted in the code above.
What is more, there is one more unusual concept declared in this class.
It has to implement `ResourceInterface` from the `Sylius\Component\Resource\Model\ResourceInterface` namespace.

> **Disclaimer**: Anemic models and mutable data structures can be considered as an antipattern. Not every problem should be resolved based on them.

Now it is time to define an ORM mapping for this class.
First of all, we need to create a new folder structure inside of `src/AppBundle`.
Let’s create `Resources/config/doctrine` folders and inside of a `doctrine` one, let’s create a `ProductBundle.orm.xml` file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!-- src/AppBundle/Resources/config/doctrine/ProductBundle.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping">

    <entity name="AppBundle\Entity\ProductBundle" table="app_product_bundle">
        <id name="id" column="id" type="integer">
            <generator strategy="AUTO" />
        </id>

        <field name="code" column="code" type="string" unique="true" />
        <field name="name" column="name" type="string" />
    </entity>

</doctrine-mapping>
```

The file and folder structure is predefined by Doctrine library and should be known for all whose are familiar with Doctrine project itself.
The file contains information about basic class mapping to SQL database.
We have declared that the class has three fields, where one is an auto-incremented integer, and there are two other string fields: code and name.
When the new entity is defined, we can generate a doctrine migration (which is a recommended way of handling database changes):

```bash
php bin/console doctrine:migrations:diff
```

![migration diff output](/assets/images/posts/2018/sylius-resource-bundle/migration-diff.png)

You can check a newly created migration file in `app/migrations/` folder.
The file will be prefixed with the `Version` word and suffixed with the current timestamp.
The migration can be executed with the following command:

```bash
php bin/console doctrine:migrations:migrate
```

![migration migrate output](/assets/images/posts/2018/sylius-resource-bundle/migration-migrate.png)

Of course, we need to confirm our intention of data migration.
We have created a new entity and mapped it to the database.
But can we do anything with it?
Not yet.
We need to interact with it somehow.
One of the possibilities is to code controllers and some routing for it.
On the other hand, we can use a SyliusResourceBundle.
But how? It is enough to add the following configuration in `app/config/config.yml` file:

```yml
# app/config/config.yml
sylius_resource:
    resources:
        app.product_bundle:
            classes:
                model: AppBundle\Entity\ProductBundle
```

This configuration will inform Sylius that a `ProductBundle` class should be considered as a resource.
Therefore, Sylius will provide a default implementation of basic services for you.
You don’t believe me?
Try this line:

```bash
php bin/console sylius:debug:resource app.product_bundle
```

As a result, you should see the following table:

```
|--------------------|------------------------------------------------------------|
| name               | product_bundle                                             |
| application        | app                                                        |
| driver             | doctrine/orm                                               |
| classes.model      | AppBundle\Entity\ProductBundle                             |
| classes.controller | Sylius\Bundle\ResourceBundle\Controller\ResourceController |
| classes.factory    | Sylius\Component\Resource\Factory\Factory                  |
| classes.form       | Sylius\Bundle\ResourceBundle\Form\Type\DefaultResourceType |
|--------------------|------------------------------------------------------------|
```

Another way to check what ResourceBundle generated is to call Symfony debug container command:

```bash
php bin/console debug:container --show-private | grep product_bundle
```

![debug container output](/assets/images/posts/2018/sylius-resource-bundle/debug-container.png)

All services with the `app` prefix are newly created.

## But why should I care?

There are two main reasons for that.

Firstly, it is just an entry point to the features of ResourceBundle. For example, if we add the following snippet to `app/config/routing.yml`:

```yml
# app/config/routing.yml
app_bundle:
    prefix: api/
    resource: |
        alias: app.product_bundle
    type: sylius.resource_api
```

...we will provide full API-based CRUD for the newly created resource. Check it out calling:

```bash
php bin/console debug:router | grep product_bundle
```

![debug router output](/assets/images/posts/2018/sylius-resource-bundle/debug-router.png)

> **Note**: You can now use your newly created API to manage your resource, but first you need to authenticate with OAuth2 according to Sylius documentation: http://docs.sylius.com/en/1.1/api/authorization.html and run the server app.

As you can imagine, HTML-based management panel is also straightforward to set up.

What is probably even more important, the whole logic is not coupled to Sylius at all.
As with every Symfony Bundle, you can reuse this logic to use in whatever Symfony app you want.

## Want to see more?

If you would like to read a little bit more about ResourceBundle itself you can visit following links:
 
 * http://docs.sylius.com/en/1.1/components_and_bundles/components/Resource/index.html
 * http://docs.sylius.com/en/1.1/book/architecture/resource_layer.html
 * http://docs.sylius.com/en/1.1/components_and_bundles/bundles/SyliusResourceBundle/index.html

Furthermore, you can join me on May 11th in Prague for 8-hour training.
I will show you how easy it is to add custom logic to Sylius based on a class we have just created.
If you are interested, you can read more about the training itself [here](https://pehapkari.cz/kurz/getting-started-with-sylius/).

## TL;DR

SyliusResourceBundle can boost you CRUD develepment, you should try it out.
You can also join the [Getting started with Sylius training](https://pehapkari.cz/kurz/getting-started-with-sylius/) in Prague and see it in action live.

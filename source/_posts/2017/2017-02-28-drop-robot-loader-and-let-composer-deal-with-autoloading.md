---
layout: post
title: "Drop RobotLoader and let Composer Deal with Autoloading"
perex: '''
    Using 2 tools for one thing, in this case 2 packages to autoload classes, are sign of architecture smell. Many application I see contain RobotLoader for historical reasons. I will borrow this from psychology: pathological behavioral patterns tear us down in the present, but were useful in past.
    <br><br>
    The best way to deal with them is acknowledge their purpose and then, let them go and enjoy the gift of present.
'''
author: 1
lang: en
---

*Martin Zlámal already [wrote about this](http://zlml.cz/psr-4-autoloader-aplikace) (Czech) a year ago and I think this needs to promote even more.*

## Where is RobotLoader Useful

[RobotLoader](https://doc.nette.org/en/auto-loading#toc-nette-loaders-robotloader) is Nette Component that is used to autoload classes. Its killer feature is: **in whatever file it is and whatever the class name is, I will load it**. You can have 20 classes in 1 file or classes located in various locations.

### Before composer, it was The Best

RobotLoader was very useful tool in times before composer, because **there were not many efficient tools to load classes**.

Also, when people could not agree upon where to put their classes, how to name them, whether use or don't use namespace and how many classes to put in one file, we can say **this tool saved a lot of argument-hours**.

After many discussion, followed by [first standard](http://www.php-fig.org/psr/psr-0/), people agreed upon [PSR-4](http://www.php-fig.org/psr/psr-4/).


### Why not Anymore

Have your heard about PSR-4? It is *PHP Standard Recommendation* about naming and location classes. This says you completely nothing, but in simple words is means:

**1 class** (/interface/trait) = **1 file**

**class name** = **file name**.php

**namespace\class name** = **directory\file name**.php

```bash
# class => file
MyClass => MyClass.php
App\MyClass => App\MyClass.php
App\Presenter\MyClass => App\Presenter\MyClass.php
```

I know I can really on this on 99% of places `composer.json` is used.

When I see `App\Presenter\MyClass` I know it's located in `App\Presenter\MyClass.php` file.

And this is the place where **RobotLoader** (or any custom ultimate loader) **fails**. I come to many applications, where classes are located at random. And I have to use my brain to find them. But I don't want focus my mental activity to think about location, **I want to develop my application**.


## How to move to composer in Nette application?

If you prefer reading commits, here is one [applying this](https://github.com/TomasVotruba/igloonet-se-skoli/pull/8/commits/10f389738ca1fef559ba9fd9509b36151cdaf400) on Nette sandbox.

And if not, there are 2 levels how to achieve this.

### Level 1: Change your Composer

First is more simple and requires only adding few lines to `composer.json`:


```json
{
    "require-dev": {
        "..."
    },
    "autoload": {
        "psr-4": {
            "App\\Forms\\": "app/forms",
            "App\\Model\\": "app/model",
            "App\\Presenters\\": "app/presenters",
            "App\\": "app/router"
        }
    }
}
```

This means, all classes starting with `App\\Forms\\` namespace have to be located in `app/forms` directory.

One important rule - **it is case sensitive**.

So this will work:

```bash
App\Presenters\HomepagePresenter => app\presenters\HomepagePresenter.php
```

But this won't:

```bash
App\Presenters\HomepagePresenter => app\presenters\homepagePresenter.php
```

Now you can clean up your `app/bootstrap.php`:

```php
// $configurator->createRobotLoader()
//      ->addDirectory(__DIR__)
//      ->register();
```

And tell composer, to regenerate its autoload:

```bash
composer dump-autoload
```

Note: This command is run by default after `composer update`, `composer require ...` etc. commands. Now we changed manually our `autoload` section, so we have to run it manually.

Now try our application and it should run.

**You are finished and all your classes are loaded by composer.** Congrats!

There is one level I do with my applications, so my `composer.json` is nice and clear. But this is optional! Do it only if you want to write better code that has lower WTF factor!


### Level 2: Rename Directories to capital case, to Respect PSR-4

Turn this:

```bash
/app
    /forms
    /model
    /presenters
    /routing
    /...
```

To this:

```bash
/app
    /Forms
    /Model
    /Presenters
    /Routing
    /...
```

Also [rename `App\RouterFactory` to `App\Routing\RoutingFactory`](https://github.com/nette/sandbox/pull/86), so the class respects the file name.

After these steps, you can simplify your `autoload` section to this:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app"
        }
    }
}
```

Don't forget to run:

```bash
composer dump-autoload
```

And you've unlocked Level 2.

That's it!

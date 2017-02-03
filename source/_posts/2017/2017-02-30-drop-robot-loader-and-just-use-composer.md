---
layout: post
title: "Drop RobotLoader and let Composer deal with it"
perex: '''
    Using 2 tools for one thing, in this case 2 packages to autoload classes, are sign of architecture smell. Many application I see contain RobotLoader for historical reasons. Some behavioral patterns we have now were useful in past, but keep as back in the present.
    <br><br>
    The best way to deal with them is acknowledge their purpose and then, let them go and enjoy the present.
'''
author: 1
lang: en
---

## Where is RobotLoader Useful

[RobotLoader](https://doc.nette.org/en/auto-loading#toc-nette-loaders-robotloader) is Nette Component that is used to autoload classes. His killer feature is: **in whatever file it is and whatever the class name is**. You can have 20 classes in 1 file or classes located in various locations.

### In times, it was good

This was very useful tool in times before composer, because there were not many efficient tools to do this. When people could not agree upon where to put their classes, how to name them, whether use or don't use namespace and how many classes to put in one file, we can say **this tool saved a lot of argument-hours**.

After many discussion, followed by [first standard](http://www.php-fig.org/psr/psr-0/), people agreed upon [PSR-4](http://www.php-fig.org/psr/psr-4/).


### Why not anymore

PSR-4 is *PHP Standard Recommendation* about naming and location classes. In simple words:

**1 class** (/interface/trait) = **1 file**

```bash
# class => file
MyClass => MyClass.php
App\MyClass => App\MyClass.php
App\Presenter\MyClass => App\Presenter\MyClass.php
```

I know I can really on this on 99% of places `composer.json` is used.

When I see `App\Presenter\MyClass` I know it's located in `App\Presenter\MyClass.php`.

And this is the place where RobotLoader (or any custom ultimate loader) fails. I come to many applications, where classes are located at random. And I have to use my brain to find them. But I don't want focus my mental activity to think about location, **I want to develop my application**.


## How to move to composer in Nette application?

If you prefer reading commits, here is one [applying this](https://github.com/TomasVotruba/igloonet-se-skoli/pull/8/commits/10f389738ca1fef559ba9fd9509b36151cdaf400) on Nette sandbox.

And if not:

```bash
# app/bootstrap.php

/*
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();
*/
```

Add to your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
    	    "App\\": "app"
        }
    }
}
```

This means, all classes starting with `App\\` namespace have to be located in `app` directory.

Important thing: **it is case sensitive**.

So this will work:

```bash
App\Presenters\HomepagePresenter => app\Presenters\HomepagePresenter.php
```

But this won't:

```bash
App\Presenters\HomepagePresenter => app\presenters\HomepagePresenter.php
```


### 2. Rename directories to capital case

Turn this:

```bash
/app
    /presenters
    /...
```

To this:

```bash
/app
    /Presenters
    /...
```


### 3. Dump autoload

@todo


That's it!




**Autoloading tests?**

```json
# composer.json
{
    "autoload-dev": {
        "psr-4": {
            "App\\Tests\\": "tests"
        }
    }
}
```

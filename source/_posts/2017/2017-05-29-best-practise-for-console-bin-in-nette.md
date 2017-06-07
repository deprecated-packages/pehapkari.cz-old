---
layout: post
title: "Best Practise for Console Bin in Nette"
perex: '''
    If you use console in Nette, you will be probably familiar with <code>php index.php command</code> approach.
    Well, there is a better one.
    <br><br>
    In this short post, I will show you <strong>how to make it cleaner, standard, more decoupled</strong> and just refactor to <code>bin/console</code>.        
'''
author: 1
---

`php index.php` is mostly known from [Kdyby\Console](https://github.com/Kdyby/Console/), that integrates [Symfony\Console](https://github.com/symfony/console)
to Nette DI. Great thanks to [Filip Procházka](https://filip-prochazka.com/) for this package, because it made using console command (something like Presenter, just in CLI)
very simple. 

## How it Works?

What `php index.php command` actually does?

When you run this command from your CLI, it will:
 
1. use Nette Router
2. find CliRoute
3. call Symfony Application (something like [Nette\Application\Application](https://github.com/nette/sandbox/blob/ae3556149309b8442553f6bc70527a923432a19d/www/index.php#L10))
4. pass it your command name and arguments

## Historical Reasons (Again)

CD players were the best tool to play music on the road... like 10 years ago? Every best practise we use in present has its expiration date. 

First 2 steps have almost no added value nowadays - they are present for historical reasons: As every framework has "his own way", in Nette there is [CliRoute](https://github.com/nette/application/blob/master/src/Application/Routers/CliRouter.php) class
which implies, it's "the Nette way" to use this class and routing.

## How other Frameworks do it

When I see some framework concept for the first time, I'll be curious and wonder, how other frameworks do it?
There is lot of reinvented wheels already, because of lack of information sharing.
 
In Symfony there is [`bin/console`](https://github.com/symfony/symfony-demo/blob/master/bin/console).

```bash
php bin/console
```

In Laravel there is [`artisan`](https://github.com/laravel/laravel/blob/master/artisan).

```bash
php artisan
```

It is only "a file to delegate CLI arguments to". Why not use the same approach in Nette? 


## What the Best Practise (so far)

I'm happy to see [this fresh commit](https://github.com/Kdyby/Console/commit/db9c3304f0998bc82724665d3b43d3b6e3eb40ce) by Filip Prochazka, that promotes the same
approach I'm building up to:

```bash
php bin/console
```

**bin/console**

```php
#!/usr/bin/env php
<?php declare(strict_types=1);

/** @var Nette\DI\Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

/** @var Symfony\Component\Console\Application $consoleApplication */
$consoleApplication = $container->getByType(Symfony\Component\Console\Application::class);
$consoleApplication->run();
```

You may notice it's the same as [`www/index.php`](https://github.com/nette/sandbox/blob/master/www/index.php):

```php
<?php declare(strict_types=1);

/** @var Nette\DI\Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

/** @var Nette\Application\Application $application */
$application = $container->getByType(Nette\Application\Application::class);
$application->run();
```

Only with different `Application` class.

### Protip: use "Application" naming for entry class to your application

As you can see, they are standard way to let user know, where to start. 


## Show me Real Refactoring!

If you'd like to see a real life code, [I made PR on Github](https://github.com/eventigo/eventigo-web/pull/19/files) to [Eventigo.cz](https://eventigo.cz/) recently.

We use lightweight and simple [Contributte\Console](https://github.com/contributte/console) by [Milan Šulc](https://jfx.cz/), that also
allowed use to drop [useless command tagging](https://www.tomasvotruba.cz/blog/2017/02/12/drop-all-service-tags-in-your-nette-and-symfony-applications/#get-rid-of-tagging-in-nette).

<div class="text-center">
    <img src="/assets/images/posts/2017/nette-console/commands-after.png">
</div>


## Now You Know

- **The best practise for running console command** in Nette in 2017.
- **How to refactor** to `bin/console`.
- There might be some awesome packages in [Contributte](https://github.com/Contributte) :).

Have a nice day!

Btw, how do you use console bin? With or without router? Or another way?
Share with us in comments. There always might be better way to approach this.

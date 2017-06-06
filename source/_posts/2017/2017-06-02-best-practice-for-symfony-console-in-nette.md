---
layout: post
title: "Best Practice for Symfony Console in Nette"
perex: '''
    If you use Symfony\Console in Nette, you will be probably familiar with <code>php index.php command</code> approach.
    It has been obsolete since Nette 2.3, and we should all migrate to its successor.
    This blog post will show you why and how.
'''
author: 23
lang: en
---


Running console through the `www/index.php` was introduced in [Kdyby\Console](https://github.com/Kdyby/Console/) by [me (Filip Procházka)](https://filip-prochazka.com/) and [the practice is now deprecated](https://github.com/Kdyby/Console/commit/db9c3304f0998bc82724665d3b43d3b6e3eb40ce). This article shows why it was introduced, why it is deprecated and how to use [Symfony\Console](https://github.com/symfony/console) more elegantly.

## Why was running through index.php introduced

For example, you might have a mailer service that handles rendering of a template for the email and then sending it. And a regular email probably has some links in it. That is what the `LinkGenerator` is for, [which was introduced in Nette 2.3](https://github.com/nette/application/commit/e0305285cebc65426073061b261e084daef4933e).

Before Nette 2.3 the simplest way to solve this was to fetch the current instance of `UI\presenter` from `Nette\Application\Application`, pass it to the template you're trying to render and only then the URL generating was working. And to have the `UI\presenter` in `cli`, you had to execute `Nette\Application\Application` that would create the presenter so you can generate the URL.

In short, every time you call `php www/index.php command`, Kdyby\Console is

1. taking over the Nette routing with `CliRoute`
2. `CliRoute` creates an application request for `KdybyModule\CliPresenter`
3. `CliPresenter` calls Symfony Application and passes your command name and arguments

This guarantees you'll always have an `UI\Presenter` in `Nette\Application\Application` that can be used for generating URLs.

## Missing Http\Url problem

Having the `LinkGenerator` (or `UI\Presenter` in the past) available is not enough for generating URLs. It requires an `Http\Url` instance that is by default fetched from the `Http\IRequest` service. This is solved by Kdyby too. You can just configure an URL and Kdyby will rewrite the `Http\IRequest` service and provide a fake instance with the URL configured.

```yaml
console:
    url: https://pehapkari.cz/
```

## Simplifying the execution

Executing the console through `Nette\Application\Application` is no longer necessary since we have the `LinkGenerator`. So how can we make this more elegant? Since we're integrating [Symfony\Console](https://github.com/symfony/console), a logical approach is to look what Symfony thinks is the best practice.

Symfony has a `bin/console` script with contents similar to the following snippet which is sufficient for Nette

```php
#!/usr/bin/env php
<?php declare(strict_types=1);

/** @var \Nette\DI\Container $container */
$container = require __DIR__ . '/../app/bootstrap.php';

/** @var \Symfony\Component\Console\Application $consoleApplication */
$console = $container->getByType(Symfony\Component\Console\Application::class);
exit($console->run());
```

that we can execute by typing


```bash
php bin/console help
```

And if we make it executable with

```bash
chmod +x bin/console
```

we can even drop the `php` when executing it

```bash
bin/console help
```

This removes the extra layers of abstractions that are no longer needed thanks to the `LinkGenerator`. But, having the `console.url` option is still necessary to correctly generate URLs.

## Utilizing decorator extension

[With Nette 2.3](https://github.com/nette/di/commit/28fdac304b967ae43a90936069d94316ee2daca4) a new `DecoratorExtension` was introduced that greatly simplifies command registration. We can use it, to find all services that have a given type and give them all a tag or some common setup calls.

With the extension this config

```yaml
# app/config/config.neon

services:
    -
        class: App\Console\FirstCommand
        tags: [kdyby.console.command]
    -
        class: App\Console\SecondCommand
        tags: [kdyby.console.command]
    -
        class: App\Console\ThirdCommand
        tags: [kdyby.console.command]
```

can be simplified to

```yaml
# app/config/config.neon

services:
    - App\Console\FirstCommand
    - App\Console\SecondCommand
    - App\Console\ThirdCommand

decorator:
    Symfony\Component\Console\Command\Command:
        tags: [kdyby.console.command]
```

Nette extensions allow to search for a given type in compile-time, and therefore Kdyby\Console (and any other extension) could just work without tags, but tags are a predictable solution to marking services for special processing. If you want to just tag everything for processing, it's better if you do it explicitly yourself using the `DecoratorExtension`.

## Auto-complete support

Another added benefit of switching to `bin/console` is automatic support of Symfony Console by `zsh`. The [default zsh Symfony Console integration](https://github.com/robbyrussell/oh-my-zsh/blob/291e96dcd034750fbe7473482508c08833b168e3/plugins/symfony2/symfony2.plugin.zsh) is invoked when you execute `bin/console` in your terminal and auto-completes the commands and options. Thanks [Klára](https://twitter.com/kerlebac), for pointing this out!

## Example of the refactoring

If you'd like to see a real-life code, [Tomáš Votruba made PR on Github](https://github.com/eventigo/eventigo-web/pull/19/files) to [Eventigo.cz](https://eventigo.cz/) recently.

## Conclusion

This article was mainly about [Kdyby\Console](https://github.com/Kdyby/Console/) and its history, but to be fair, I have to mention [Contributte\Console](https://github.com/contributte/console) by [Milan Felix Šulc](https://f3l1x.io) that actually prompted the update in Kdyby\Console documentation.

Also, a side-note to whoever is using [Kdyby\Doctrine](https://github.com/Kdyby/Doctrine) - it is planned to [make Kdyby\Console optional](https://github.com/Kdyby/Doctrine/issues/190) which will remove the vendor lock-in for Kdyby\Console, so you can choose what integration to use. This will allow you not to use Symfony\Console at all, or replace Kdyby\Console with [Contributte\Console](https://github.com/contributte/console) if you decide to do so.

There is always a better way to approach things. Please, share with us how you're using Symfony\Console in the comments.

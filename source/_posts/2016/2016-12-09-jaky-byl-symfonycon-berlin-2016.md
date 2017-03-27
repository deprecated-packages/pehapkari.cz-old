---
layout: post
title: "Jaký byl SymfonyCon Berlín 2016"
perex: "Největší událost v Symfony světě je za námi. Představeny byly služby <strong>SensioCloud</strong>, nový balíčkovací systém <strong>Symfony Flex</strong> a mnoho dalšího."
author: 2
---

[SymfonyCon](http://berlincon2016.symfony.com/) největší Symfony konference, která se koná každoročně na začátku prosince. Do Berlína dorazilo **přes 1200 účastníků** na 2 dny konference a jeden hackday.

<div class="text-center">
    <img src="/assets/images/posts/2016/symfonycon/our-group.jpg">
    <br>
    <em>
        <a href="https://twitter.com/MichalOktabec/status/804690615023890432">Z Čech a Slovenska nás dorazilo rekordních 16</a>
    </em>
</div>

<br>

## SensioCloud

V úvodní keynote [Fabien Potencier](https://github.com/fabpot) představil službu [SensioCloud](https://sensio.cloud/) - [PaaS](https://en.wikipedia.org/wiki/Platform_as_a_service) optimalizovaný pro Symfony.

Jde o nadstavbu nad [Heroku](https://www.heroku.com). Celá platforma má být velmi jednodušše použitelná, s dobrou podporou škálování a optimalizací na vývoj: možnost branchování (testovací prostředí, staging...) nebo synchronizace DB mezi nimi.

*Termín spuštění?*
Začátek roku 2017.

### Readonly FileSystem je ready

Důležitou součástí bylo **dokončení podpory readonly filesystému** (dostupné od verze [Symfony 3.2](https://github.com/symfony/symfony/blob/master/CHANGELOG-3.2.md)). Právě to umožní **deploying aplikací pomocí artefaktů**.

Problém byl v komponěntě Cache, která kvůli absolutním cestám znemožňovala deploy Symfony aplikace na úložiště bez možnosti zápisu. **Cache lze od verze 3.2 zahřát na build serveru a vytvořit tak funkční artefakt, který již žádný zápis nevyžaduje**.


## Symfony Flex

V další přednášce Fabien popsal nedostatky v Symfony distribučním systému. Jako řešení představil **Symfony Flex**. Tím chce nahradit dosavadní balíčky jako [Symfony Demo Application](https://github.com/symfony/symfony-demo), [Symfony Standard Edition](https://github.com/symfony/symfony-standard) nebo  [Symfony CMF](http://cmf.symfony.com/).

Řešením je použití kompozice místo dědičnosti a **rozpadení konfiguračních souborů/úkonů do samostatných souborů**, aby bylo možné "balíčky" jednoduše odebírat či přidávat.

*Termín spuštění?*
Konec ledna 2017.


## Don't kill the chef - Keep PHP Alive Between Requests

[Andrew Carter](https://twitter.com/AndrewCarterUK) nám představil možnosti, jak je možné udžet Symfony aplikaci "živou" napříč více requestů a tak [zvýšit výkon výsledné aplikace](http://andrewcarteruk.github.io/slides/soup-up-symfony/#/49).

Upozornil také na nedostatky PHP při běhu v tomto režimu - memory leaky či MySQL timeouty.

**[Mrkni na slajdy](http://andrewcarteruk.github.io/slides/soup-up-symfony/)**

<div class="text-center">
    <img src="/assets/images/posts/2016/symfonycon/dont-kill-chef.jpg">
    <br>
    <em>
        Restaurace jako webový server
    </em>
</div>

<br>

## Cache komponenta (od Symfony 3.1)

[Nicolas Grekas](https://github.com/nicolas-grekas) povídal o Cache komponentně, která odpovídá [PSR-6: Caching Interface](http://www.php-fig.org/psr/psr-6/) standardu.

Krom funkcionality se zaměřil na perfomance testy, kde si komponenta vede obstojně. **Nejlépe v bulk operacích při použití Redis Adaptéru**. Nejlepším konkurentem byla [Doctrine\Cache](https://github.com/doctrine/cache).

**[Mrkni na slajdy](https://speakerdeck.com/nicolasgrekas/psr-6-and-symfony-cache-fast-by-standards-1)**


## Knowing your state machines

[Tobias Nyholm](https://github.com/Nyholm) nás uvedl do nové [Workflow komponenty](https://github.com/symfony/workflow), představil principy a ukázky použití. Workflow komponenta by mohla být velmi užitečná v nejedné business aplikaci - **pro stavy uživatelů nebo řízení stavů objednávky**.

**[Mrkni na slajdy](http://www.slideshare.net/TobiasNyholm/knowing-your-state-machines)**


## A Year of Symfony

[Sarah Khalil](https://github.com/saro0h) nám **stručně představila významé novinky posledního roku**.

Co mě zaujalo?

- [zjednodušený přístup k proměnným prostředí (ENV)](https://speakerdeck.com/saro0h/symfonycon-berlin-a-year-of-symfony?slide=60)
- [zjednodušení práce s Compiler Pass](https://speakerdeck.com/saro0h/symfonycon-berlin-a-year-of-symfony?slide=54)
- nebo  [Tagged Cache](https://speakerdeck.com/saro0h/symfonycon-berlin-a-year-of-symfony?slide=57)


**[Mrkni na slajdy](https://speakerdeck.com/saro0h/symfonycon-berlin-a-year-of-symfony)**


### A co další slajdy?

**Všechny zveřejněné slajdy najdeš na [joind.in](https://joind.in/event/symfonycon-berlin-2016).** Z nich bych
ještě rád vypíchnnul:


- [When To Abstract](https://qafoo.com/resources/presentations/symfonycon_berlin_2016/when_to_abstract.html)
- [Profiling PHP](https://speakerdeck.com/sgrodzicki/profiling-php-at-symfonycon-berlin-2016)
- [Modernizing with Symfony](https://slidr.io/derrabus/modernizing-with-symfony#1)


<div class="text-center">
    <img src="/assets/images/posts/2016/symfonycon/elaphants.jpg">
    <br>
    <em>
        Tak zase za rok
    </em>
</div>

<br>

### Pro tip na konec

Early-bird lístky na příští SymfonyCon se vyplatí sledovat - prvních **200 bývá za míň jak poloviční cenu**!

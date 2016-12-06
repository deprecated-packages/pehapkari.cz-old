---
layout: post
title: "SymfonyCon Berlin 2016"
perex: "Největší událost v Symfony světě je za námi, tak se pojďme podívat jak to probíhalo. Představeny byly služby <strong>SensioCloud</strong>, nový balíčkovací systém <strong>Symfony Flex</strong> a mnoho dalšího ..."
author: 2
---


SymfonyCon patří každoročně k nejvýznamějším symfony-related událostem roku, která se koná každoročně na začátku prosince v nějakém evropském městě.
[Letošní v pořadí již čtvrtá konference](http://berlincon2016.symfony.com/) se pořádala u našich sousedů - v Berlíně. Sešli jsme se tam v krásném kulatém počtu 16-ti účastníků.
Konference je oficiálně třídenní, kde první dva dny jsou věnovany přednáškám a poslední den vždy čistě jako hackday. Celková účast byla kolem 1200+ účastníků.

<blockquote class="twitter-tweet" data-lang="en"><p lang="und" dir="ltr"><a href="https://twitter.com/hashtag/czech?src=hash">#czech</a> <a href="https://twitter.com/hashtag/symfonycon?src=hash">#symfonycon</a> <a href="https://t.co/u0x3Pi4G8t">pic.twitter.com/u0x3Pi4G8t</a></p>&mdash; Michal Oktábec (@MichalOktabec) <a href="https://twitter.com/MichalOktabec/status/804690615023890432">December 2, 2016</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>


## SensioCloud
Úvodní keynotes obou dnů si vzal sám [Fabien](https://github.com/fabpot) a představil nám nejdříve službu [SensioCloud](https://sensio.cloud/),
což je PaaS optimalizovany přímo pro Symfony. Vyšlo najevo že se bude jednat od nadstavbu nad [Heroku](https://www.heroku.com). 
Celá platforma by měla být velmi jendoduchá na použití s dobrou podporou škálování a optimalizací na vývoj - možnost 
branchování (=testovací prostředí, staging atd.) s podporovou synchronizace DB mezi nimi atd. Služba ještě není kompletně představena a měla
by být zveřejně na začátku příštího roku.

Nedílnou součástí bylo dokončení podpory readonly filesystému (dostupné od verze [Symfony 3.2](https://github.com/symfony/symfony/blob/master/CHANGELOG-3.2.md)) a tím plnou podporu pro deploying aplikací pomocí
artefaktů. Problém byl v cache, která po dlouhá léta obsahovala (více či méně) absolutní cesty a tak znemožňovala deploy symfony aplikace 
na úložiště, bez možnosti zápisu. Cache lze od verze 3.2 zahřát na build serveru, vytvořit funkční artefakt s předehřátou cachí. 


## Symfony Flex
V přednášce nám fabpot odkryl nejdříve (z jeho pohledu) nedostatky v symfony distribučním systému, aby v zápětí představil, dle jeho slov,
ultimátní řešení Symfony Flex čímž by chtěl nahradit dosavadní balíčky - [Symfony Demo Application](https://github.com/symfony/symfony-demo), [Symfony Standard Edition](https://github.com/symfony/symfony-standard), [Symfony CMF](http://cmf.symfony.com/), ... 
Řešením je použití kompozice místo dědičnost a rozpadení konfiguračních souborů/úkonů do samostatných souborů, aby bylo možné "balíčky" jednoduše
odebírat či přidávat. 

Zatím jsem nenašel žádný zdroj, kde by se Symfony Flex popísoval, ale dle Fabienových slov by chtěl mít Flex hotový do konce ledna 2017. 
Tak se můžeme prozatím pouze těšit, protože by to mělo znamenat výrazné zlepšení DX (developer experience).


## Don't kill the chef - Keep PHP Alive Between Requests  
[Andrew Carter](https://twitter.com/AndrewCarterUK) nám představil možnosti jak je možné udžet symfony aplikaci "živou" napříč více requestů a tak [zvýšit výkon výsledné aplikace](http://andrewcarteruk.github.io/slides/soup-up-symfony/#/49).
V druhé polovině upozornil na nedostatky PHP při běhu v tomto režimu (memory leaky, timeout - mysql atd.).

![Restart as a web server](/assets/images/conferences/synfonycon-2016/dont-kill-chef.jpg)

[Soup up Symfony - Keep PHP Alive Between Requests - slides](http://andrewcarteruk.github.io/slides/soup-up-symfony/)


## Cache komponenta (od Symfony 3.1)
[Nicolas Grekas](https://github.com/nicolas-grekas) povídal o "nové" Cache komponentně, která odpovídá [PSR-6: Caching Interface](http://www.php-fig.org/psr/psr-6/) standardu.
Krom samotné funkcionality se zaměřil také na perfomance testy, kde si komponenta vede docela obstojně - hlavním konkurentem byla Doctrine Cache,
která byla v některých ohledech dokonce pomalejší než představený driver. Lepší performence měla převážně, v bulk operacích při použití Redis Adaptéru.

<script async class="speakerdeck-embed" data-id="1962ed627f414eb28a70f2fbfd714f45" data-ratio="1.77777777777778" src="//speakerdeck.com/assets/embed.js"></script>

## Knowing your state machines
[Tobias Nyholm](https://github.com/Nyholm) nás uvedl do nové [Workflow komponenty](https://github.com/symfony/workflow), představil principy a 
ukázky použití. Workflow komponenta by mohla být velmi užitečná v nejedné business aplikaci. Stavy uživatelů, řízení stavů objednávky, atd. vypadají
jako typické příklady použití... 

<iframe src="//www.slideshare.net/slideshow/embed_code/key/ytwbbZ1zW9nWGy" width="425" height="355" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC; border-width:1px; margin-bottom:5px; max-width: 100%;" allowfullscreen> </iframe> <div style="margin-bottom:5px"> <strong> <a href="//www.slideshare.net/TobiasNyholm/knowing-your-state-machines" title="Knowing your State machines" target="_blank">Knowing your State machines</a> </strong> from <strong><a target="_blank" href="//www.slideshare.net/TobiasNyholm">Tobias Nyholm</a></strong> </div>


## A year of symfony
[Sarah Khalil](https://github.com/saro0h) nám představili stručnou formou všechny významější novinky posledního roku.
<script async class="speakerdeck-embed" data-id="587cfae6989d452eae71e7d61dcf2629" data-ratio="1.77777777777778" src="//speakerdeck.com/assets/embed.js"></script>


## A mnoho dalšího ...
Zajímavé byly všechny přednášky, ale nebudu se zde (kvůli rozsahu) rozepisovat o všem - pouze z těch nejzajímavějších vypíchnu jen - [When To Abstract](https://qafoo.com/resources/presentations/symfonycon_berlin_2016/when_to_abstract.html),
[Profiling PHP](https://speakerdeck.com/sgrodzicki/profiling-php-at-symfonycon-berlin-2016), [Modernizing with Symfony](https://slidr.io/derrabus/modernizing-with-symfony#1),
[HTTP Security: headers as a shield over your application](https://speakerdeck.com/romain/http-security-headers-as-a-shield-over-your-application), 
[Adventures in Symfony - Building an MMO-RPG](http://slides.com/margaretstaples/gamedev#/), ...

Prozatím nejsou [zveřejněna videa](https://www.youtube.com/user/SensioLabs/videos), ale dle jejich slov projdou editací a obratem budou. Můžete je tedy očekávat
v nejbližších týdnech. 


## Conference Social Event
První večer byl za hojné podpory SensioLabs věnována socializingu a až zde jsme se všichni potkali a s některými seznámili. S nastupující hladinou se 
diskuze uvolňovala a postupně padaly hlášky typu:
 
<blockquote class="twitter-tweet" data-lang="en"><p lang="cs" dir="ltr">Po sedmým pivu: &quot;už ani ten integer overflow není co to bejvalo&quot; <a href="https://twitter.com/hashtag/nostalgie?src=hash">#nostalgie</a> <a href="https://twitter.com/hashtag/symfonycon?src=hash">#symfonycon</a></p>&mdash; Filip Procházka (@ProchazkaFilip) <a href="https://twitter.com/ProchazkaFilip/status/804426014336122880">December 1, 2016</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

Jako dostatečně socializovaní se cítíme až v okamžiku, kdy se dopíjí poslední láhev a kolem 23h se rozcházíme do svých dočasných domovů.


## Hackday
Na sobotu je plánovaný hackday, což je výborná přiležitost k libovolnému pull-requestu. Lidi ze SesnsioLabs poskytnou libovolnou podporu a ochotně pomůžou.
Celé dopoledne působí velmi nenásilně a každý můžeme čas využít jak uzná za vhodné.

Kolem 14h se praktický celá česká komunita zvedá a odchází na vlak v 15h. Ve vlaku zkoumáme čerstvý update [PHP 7.1](http://php.net/index.php#id2016-12-01-3) a v Praze se definitivně rozcházíme.

Díky všem co se konference zůčastnili za skvělou náladu po celou dobu akce.


### Rozcestník

- web: [http://berlincon2016.symfony.com](http://berlincon2016.symfony.com)
- prezentace: [https://joind.in/event/symfonycon-berlin-2016](https://joind.in/event/symfonycon-berlin-2016)

<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">Let&#39;s start a circus! <a href="https://twitter.com/hashtag/SymfonyCon?src=hash">#SymfonyCon</a> <a href="https://t.co/fxYWQVcHnX">pic.twitter.com/fxYWQVcHnX</a></p>&mdash; Filip Procházka (@ProchazkaFilip) <a href="https://twitter.com/ProchazkaFilip/status/804600972941131776">December 2, 2016</a></blockquote>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

<style type="text/css">
img {
    display: block;
    width: 50%;
    margin: 0 auto 0 auto;
}

.twitter-tweet {
    display: block;
    margin: 0 auto 0 auto;
}
</style>

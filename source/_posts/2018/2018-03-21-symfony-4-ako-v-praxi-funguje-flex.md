---
id: 67
title: "Symfony 4 - Ako v praxi funguje Flex"
perex: |
    Ako titulka napovedá, pozrieme sa spoločne na jednu z mnohých skvelých noviniek, ktorú priniesol framework Symfony vo svojej štvrtej verzii. Pokúsim sa Vám priblížiť, čo Symfony Flex vlastne je a ako pracuje.
author: 33
lang: sk
tweet: "Urodilo se na blogu: #Symfony 4 - Ako v praxi funguje #Flex"
---

**Symfony Flex** je nástroj umožňujúci vývojárom automatizovať správu balíčkov pre ich Symfony projekt. Inštalácia balíčku sa stáva veľmi jednoduchou a intuitívnou, kedy ako plugin do obľúbeného Composera vykoná všetky potrebné akcie k registrácií a konfigurácií balíčka do projektu.

## Novinky na úvod

Vydanie Symfony verzie 4 prinieslo so sebou viacero noviniek, medzi nimi aj **Symfony Flex**. Kým sa vrhneme na jeho použitie, pozrieme sa spoločne na pár noviniek, ktoré môžu byť nápomocné pre rýchlejšie pochopenie ako Symfony Flex reálne pracuje.

### Framework

**Symfony 4 je miroframework**. Predchádzajúce verzie si spravidla niesli zo sebou množstvo komponentov, ktoré nie vždy boli reálne potrebné. Framework prešiel výraznou odtučňovacou kúrou a teraz má v základe iba 5 závislosti, čo je v porovnaní s verziou 3.4 o polovicu menej.

Náhľad do ``composer.json`` Symfony verzie 4:

````bash
"symfony/console": "^4.0",
"symfony/flex": "^1.0",
"symfony/framework-bundle": "^4.0",
"symfony/lts": "^4@dev",
"symfony/yaml": "^4.0"
````

Po inštalácií obsahuje vendor zložka 21 knižníc, ktoré sú nutné pre beh frameworku. Neobsahuje žiadne závislosti na šablónovací systém, ORM, debugovacie nástroje, logovacie nástroje, ktoré je nutné v prípade potreby doinštalovať.

Pre porovnanie náhľad do ``composer.json`` Symfony verzie 3.4:

````bash
"doctrine/doctrine-bundle": "^1.6",
"doctrine/orm": "^2.5",
"incenteev/composer-parameter-handler": "^2.0",
"sensio/distribution-bundle": "^5.0.19",
"sensio/framework-extra-bundle": "^5.0.0",
"symfony/monolog-bundle": "^3.1.0",
"symfony/polyfill-apcu": "^1.0",
"symfony/swiftmailer-bundle": "^2.6.4",
"symfony/symfony": "3.4.*",
"twig/twig": "^1.0||^2.0"
````

Veľkosť samotného frameworku sa zmenšila o neuveriteľných 70%. To je skvelá správa z pohľadu výkonu, ktorý sa pri jednoduchej "Hello World" aplikácií pod PHP 7.2 [zdvojnásobil](http://fabien.potencier.org/symfony4-performance.html) v porovnaní s verziou 3.4.

### Konfigurácia

V rámci **konfigurácie** bol odstránený súbor ``parameters.yml`` a nahrádzajú ho tzv. premenné prostedia, ktoré sa definujú v súbore ``.env`` umiestnenom v koreňovom adresári. Tento súbor sa neverzuje a je vždy viazaný na konkrétne behové prostredie. Ukážka jednoduchého ``.env`` súboru s konfiguráciou frameworku a doctríny: 

````bash
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=29f90564f9e472955211be8c5e05ee0a
#TRUSTED_PROXIES=127.0.0.1,127.0.0.2
#TRUSTED_HOSTS=localhost,example.com
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
###< doctrine/doctrine-bundle ###
````

Použitie premenných prostredia má viacero výhod:
* sú štandardným spôsobom správy konfigurácie pre rôzne prostredia,
* môžu byť čítané samostatne inými aplikáciami,
* sú oddelené od zdrojového kódu,
* hodnoty môžu byť zmenené bez nutnosti znovu deployovať,
* sú podporované existujúcimi vývojovými nástrojmi.

### Autowiring a Dependency Injection

Viacerých vylepšení sa dočkal **DI kontajner** už vo verzií [Symfony 3.3](https://symfony.com/doc/current/service_container/3.3-di-changes.html), ktorých cieľom bolo zjednodušiť registráciu a konfiguráciu služieb. Symfony 4 zachováva všetky zlepšenia s tým rozdielom, že ich definuje ako predvolené.

````json
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
````

### Adresárová štruktúra

Zmenami prešla aj adresárová štruktúra z dôvodu podpory Flexu a tiež pre zosúladenie s [unixovým štandardom](http://fabien.potencier.org/symfony4-directory-structure.html):

````bash
symfony4-project/
├── bin/
│   └── console
├── config/
│   ├── bundles.php
│   ├── packages/
│   ├── routes.yaml
│   └── services.yaml
├── public/
│   └── index.php
├── src/
│   ├── ...
│   └── Kernel.php
├── var/
└── vendor/
````

Adresár ``app``, ako ho možno poznáte napríklad z Nette, bol rozdelený medzi adresáre ``config`` a ``src`` nachádzajúce sa priamo v koreni.

Adresár ``config`` zahŕňa konfiguračné súbory jak frameworku, tak jednotlivých knižníc, zanorených v adresári ``packages``. Pre rôzne prostredia môžu byť definované rôzne konfiguračné súbory a ich správne použitie zabezpečí zatriedenie do príslušného adresára (dev, test, prod).

Okrem konfiguračných súborov zahŕňa adresár ``config`` aj súbor ``bundles.php``, ktorého úlohou je definovať všetky aktuálne registrované balíčky v rámci Symfony projektu. Obsahom súboru je pole tvorené z cesty k tzv. **bundle súboru** ako kľúča a hodnota je opäť pole, ktoré určuje jedno alebo viac prostredí, pre ktoré bude balíček dostupný. Triedenie balíčkov podľa potreby vývojového, testovacieho, či produkčného prostredia nebolo nikdy jednoduchšie. Príklad súboru ``bundles.php``:

````php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['dev' => true],
    ...
];
````

Pre všetkú biznis logiku je pripravený adresár ``src`` , v ktorom je taktiež  aj bootstrap súbor frameworku ``Kernel.php``. PHP súbory z adresára sú načítavané autoloadingom štandardu [PSR-4](https://www.php-fig.org/psr/psr-4/), kde namespace začína priamo na ``App\``. Začiatok namespacu určuje následujúca direktíva v ``composer.json``:

````json
"autoload": {
    "psr-4": {
        "App\\": "src/"
    }
},
````

Pôvodne adresár ``web`` (alebo ``www`` v Nette) bol premenovaný na public a obsahuje ``index.php``. Vznikol mergnutím dvoch php súborov ``app.php`` a ``app_dev.php`` z minulých verzií, kde každý z týchto súborov slúžil pre iné prostredie. Tento dvojaký prístup k aplikácií už nie je potrebný, keďže typ prostredia určuje premenná definovaná v konfiguračnom súbore ``.env``:

````bash
###> symfony/framework-bundle ###
APP_ENV=dev
###< symfony/framework-bundle ###
````

Adresár ``public`` je vhodným miestom pre assety (css-ka, javascriptové knižnice), obrázky, dokumenty a podobne.

Adresár ``var`` obsahuje cache a logy.

Posledný ``bin`` klasicky obsahuje binárky.

## Vytvárame prvú Symfony aplikáciu

Symfony 4 je možné nainštalovať výhradne prostredníctvom nástroja [Composer](https://getcomposer.org/), následujúcou direktívou:

````bash
$ composer create-project symfony/skeleton symfony4-project
````

Na základe príkazu Composer stiahne repozitár [symfony/skeleton](https://github.com/symfony/skeleton), ktorý obsahuje iba súbor ``composer.json``, definujúci závislosti frameworku, ktoré nainštaluje. 

Všimnite si, že v novovzniknutom projekte máte okrem adresárovej štruktúry aj inicializovaný git repozitár, čo je ďalšia z mnohých vecí, ktorú framework vyrieši za Vás.

### Ako pridať Twig?
    
Predpokladajme, že si na začiatok chceme navrhnúť jednoduchú webstránku. Na prípravu šablón môžeme samozrejme použiť čisté PHP, no my sa chceme niečo naučiť a preto si zvolíme šablónovací systém [Twig](https://twig.symfony.com/).
 
Samozrejme použijeme Symfony Flex a zadefinujeme novú závislosť: 

````bash
$ composer require twig
````

Po ukončení inštalácie si všimnime zmeny, ktoré nastali v projekte. Ako prvé nás zaujme nový adresár ``templates`` v koreni projetku, kde tento adresár bude slúžiť ako úložisko pre všetky šablóny v rámci projektu.

Ak nazrieme do súboru ``bundles.php`` zistíme, že Flex zaregistroval balíček do zoznamu pre všetky prostredia. Vznikli taktiež potrebné konfiguračné súbory v zložke ``packages`` s predvolenými nastaveniami.

````php
return [
    ...
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    ...
];
````
Od tejto chvíle môžeme balíček okamžite používať bez potreby ďalšej konfigurácie.

### Čo sa vlastne stalo?

Symfony Flex sa pokúsi vyhľadať balíček podľa názvu alebo aliasu v oficiálnom repozitári. Pokiaľ ho nenájde, Composer pokračuje štandardným spôsobom a nainštaluje závislosť.

Situácia bude iná, pokiaľ sa k požadovanej závislosti nájde recept pre Symfony Flex. Inštalácia závislosti prebehne štandardným spôsobom, no v jej závere zapracuje Symfony Flex a na základe [receptu](https://github.com/symfony/recipes/blob/master/symfony/twig-bundle/3.3/manifest.json) zaregistruje a nakonfiguruje balíček v rámci projektu.

Ako teda taký recept môže vyzerať sa pozrieme do súboru [manifest.json](https://github.com/symfony/recipes/blob/master/symfony/twig-bundle/3.3/manifest.json):

````json
{
    "bundles": {
        "Symfony\\Bundle\\TwigBundle\\TwigBundle": ["all"]
    },
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/",
        "templates/": "templates/"
    },
    "aliases": ["twig", "template", "templates"]
}
````

O spracovaných receptoch v rámci procesu informuje výpis v konzole:

````bash
Package operations: 3 installs, 0 updates, 0 removals
  - Installing twig/twig (v2.4.7): Loading from cache
  - Installing symfony/twig-bridge (v4.0.6): Loading from cache
  - Installing symfony/twig-bundle (v4.0.6): Loading from cache
  
Symfony operations: 1 recipe (66e53f29c335c61bdbf961f6f963b888)
  - Configuring symfony/twig-bundle (>=3.3): From github.com/symfony/recipes:master
````

Tento [recept](https://github.com/symfony/recipes/tree/master/symfony/twig-bundle) je skutočne jednoduchý, kde celá registrácia a konfigurácia twigu do projektu pozostáva z dvoch krokov:
    1. mergnutie poľa bundles s aktuálnym poľom vo Vašom súbore ``config/bundles.php``,
    2. nakopírovanie nových zložiek aj s obsahom do Vášho projektu.
    
Pole ``aliases`` definuje skrátené formy oficiálneho názvu balíčka, čím značne zvyšuje efektivitu pri práci s týmto nástrojom.

Symfony Flex si vytvára alebo aktualizuje súbor ``symfony.lock``, v ktorom zachytáva vykonané zmeny. Tento súbor sa odporúča verzovať s projektom.

### Balíček sem, balíček tam

Väčšina všeobecne obľúbených oficiálnych alebo komunitných balíčkov publikuje svoj recept na [oficiálnom portáli](https://symfony.sh/).

Skús niektoré zo svojich obľúbených vyhľadať a hneď nainštalovať:
    * symfony/orm-pack alias doctrine
    * symfony/security-bundle alias security
    * symfony/form alias form
    * symfony/monolog-bundle alias logs
    * symfony/phpunit-bridge alias phpunit
    * symfony/profiler-pack alias profiler
    * ...
    
## Záver

Symfony vo svojej už štvrtej verzii vyrástlo a práca s ním je zo dňa na deň efektívnejšia, príjemnejšia a v neposlednom rade zaujímavejšia. Myslím, že sa Symfony uberá správnym smerom a my sa v budúcnosti máme na čo tešiť.

O novinkách v pripravovanej verzii 4.1 si dáme ďalší článok :) a nedočkavcom, ktorí sa chcú dozvedieť viac už teraz, odporúčam navštíviť [naplánované školenie o Symfony 4](https://pehapkari.cz/kurz/zacni-so-symfony-4/) koncom marca 2018. :)

Ďakujem za konštruktívne komentáre, ktoré mňa a moju budúcu publikačnú činnosť posunú tým správnym smerom.

## Zdroje

* https://symfony.com/blog/hello-symfony-4
* https://symfony.com/doc/current/service_container/3.3-di-changes.html
* http://fabien.potencier.org/symfony4-performance.html 
* http://fabien.potencier.org/symfony4-best-practices.html
* https://www.etondigital.com/need-know-symfony-4/
* https://medium.com/@zawadzki.jerzy/symfony-4-new-hope-dbf99dde91d8

## Kontakt

Zvažujete použitie Symfony 4 na svojich projektoch? Navštívte moje [najbližšie školenie](https://pehapkari.cz/kurz/zacni-so-symfony-4/) alebo ma oslovte - [neoweb.sk](https://www.neoweb.sk)

---
id: 74
title: "Novinky v Symfony 4.1 - Routovanie"
perex: |
    Symfony framework pokračuje v začatom trende inovácií vo svojej verzií 4.1 a opäť prináša množstvo zaujímavých noviniek. Poďme sa teda spolu pozrieť na novinky v súvislosti s routovaním.
author: 33
lang: sk
---

Predtým ako sa vrhneme na novinky, upresníme si termín vydania verzie 4.1. Podľa roadmapy uverejenej na [oficiálnej stránke](https://symfony.com/roadmap?version=4.1#checker) je release stable verzie naplánovaný na **máj 2018**. Hurá, už tento mesiac. :)

## Nový rýchlejší router

[Router](https://symfony.com/components/Routing) vo webovej aplikácií plní dve základné úlohy, a to **generovanie URL** adries z parametrov a **mapovanie URL** adries na interné zdroje aplikácie.

### Ako je to teraz?

Aktuálny router z dôvodu optimalizácie výkonu generuje v čase kompilácie našej aplikácie novú triedu, ktorá obsahuje všetky optimalizované definície rout. Pre každú URL adresu teda existuje v tejto triede definícia regulérneho výrazu.

Po zavolaní metódy ``match()`` s príslušným reťacom, hľadá router vyhovujúcu definíciu skúšaním, čiže dochádza k opakovanému volaniu metódy ``preg_match()``.

### Ako to bude po novom?

Základnou myšlienkou refaktoringu routovacej komponenty bolo obísť opakované volanie metódy ``preg_match()`` a to spojením viacerých regulárnych výrazov v jeden.

S touto myšlienkou v minulosti prišiel [Nikita Popov](http://nikic.github.io/aboutMe.html) vo svojom [článku](http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html) zo zákulisia svojej knižnice [FastRoute](https://github.com/nikic/FastRoute).

K technickým detailom komponenty napísal [Nicolas Grekas](https://medium.com/@nicolas.grekas) dva články:

* https://medium.com/@nicolas.grekas/making-symfonys-router-77-7x-faster-1-2-958e3754f0e1
* https://medium.com/@nicolas.grekas/making-symfony-router-lightning-fast-2-2-19281dcd245b

Podľa jeho meraní je nová komponenta **15x rýchlejšia ako FastRoute**!

### Ako nový router použiť?

Najprv musíte všetky Vaše doterajšie routy zmazať a ...

**Ufff, to by bol veľmi zlý scénar. Nič také nerobte, nie je potrebné!** 

Stači jednoducho po vydaní stable verzie Symfony 4.1 ak povýšime závislosť v ``composer.json``.

```yaml
"require": {
    // ...
    "symfony/framework-bundle": "^4.1",
    // ...
},
```

Následne zavoláme príkaz na aktualizáciu závislostí.
 
```bash
composer update
```

Nový router **funguje**! Úžasné, že? :)

Pri povýšení frameworku (``framework-bundle``) môže dojsť aj k povýšeniu ostatných dodatočných balíčkov, ako napríklad `security-bundle`, `twig-bundle`, ``orm-pack`` a podobne. To je v poriadku, pokiaľ ste na ich povýšenie pripravený.

Zmeny verzií odporúčam, pre poriadok, zaniesť do ``composer.json``.
 
## Internacionalizácia rout

Doteraz, ak sme potrebovali pracovať s viacjazyčnými URL adresami, museli sme použiť bundle tretej strany, napríklad [JMSI18nRoutingBundle](https://github.com/schmittjoh/JMSI18nRoutingBundle), pretože router takúto možnosť priamo nepodporoval.

To je už minulosťou, **Symfony router od verzie 4.1 podporuje internacionalizáciu rout**.

### Čo to presne znamená?

Internacionalizácia URL adries je integrovaná priamo do komponenty. Umožňuje nám definovať samostatné adresy pre každý jazyk v časti ``path`` v konfiguračnom súbore ``routes.yml``.

```yaml
admin-dashboard:
    controller: App\Controller\Admin\DashboardController
    path:
        sk: /nastenka
        en: /en/dashboard
```

Pokiaľ preferujeme inline definíciu v kontroleroch, zápis by mohol vyzerať takto.

```php
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends Controller
{
    /**
     * @Route({
     *     "sk": "/nastenka",
     *     "en": "/en/dashboard"
     * }, name="admin-dashboard")
     */
    public function __invoke()    
    // ...
}
```

Router vytvorí pre každú adresu z ``path`` samostatnú routu s identifikátorom, ktorý je napríklad ``dashboard`` pre slovenský jazyk, či ``dashboard.en`` pre anglický jazyk.

### Generovanie rout

Generovanie rout generátorom ``UrlGeneratorInterface`` alebo funkciou ``path()`` v šablóne ostalo nezmenené.

```php
// Zápis bez parametru local, použije sa hodnota z requestu
$url = $urlGenerator->generate('dashboard');

// Klasický zápis s parametrom
$url = $urlGenerator->generate('contact', ['_locale' => 'en']);

// Ekvivalentný zápis podľa označenia routy
$url = $urlGenerator->generate('dashboard.en');
```

## Inline definícia parametrov

Ak potrebujeme v URL adrese prenášať parametre, musíme k definícií routy pridať dodatočné argumenty ako ``requirements`` a prípadne ``defaults``, ak má parameter preddefinovanú hodnotu.

```yaml
admin-articles:
    path: /admin/articles/{page}
    controller: App\Controller\Admin\ArticleController
    defaults:
        page: '1'
    requirements:
        page: '\d+'
```

Takýto zápis sa zdal súdruhom zo SensioLabs ukecaný a preto navrhli jeho inlinový ekvivalent.

 ```yaml
admin-articles:
    path: /admin/articles/{page<\d+>?1}
    controller: App\Controller\Admin\ArticleController
 ```

Viacerí si pri pohľade na tento zápis spomeniete na [Nette](https://doc.nette.org/cs/2.4/routing#toc-validacni-vyrazy), kde sa niečo podobné už používa.

Skratený zápis môžeme použit vo všetkých formátoch, napríklad aj v kontrolery.

```php
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends Controller
{
    /**
     * Klasický zápis s requirements a defaults
     * @Route("/admin/articles/{page}", name="admin-articles", requirements={"page"="\d+"}, defaults={"page"="1"})
     */
    public function __invoke(int $page)
    // ...
    
    /**
     * Nový inline zápis
     * @Route("/admin/articles/{page<\d+>?1}", name="admin-articles")
     */
    public function __invoke(int $page)
    // ...
}

```

## Záver

Vydanie stable verzie **Symfony 4.1** je na spadnutie. Ostáva ešte veľa noviniek, ktoré zo sebou prináša nová verzia, o tom snáď niekedy nabudúce. :)

Ak ste nedočkaví odporúčam sledovať [oficiálny blog](https://symfony.com/blog/) Symfony frameworku.

## Zdroje

* https://symfony.com/roadmap?version=4.1#checker
* https://symfony.com/blog/new-in-symfony-4-1-fastest-php-router
* https://symfony.com/blog/new-in-symfony-4-1-internationalized-routing
* https://symfony.com/blog/new-in-symfony-4-1-inlined-routing-configuration

## Kontakt

Chcete Symfony naučiť alebo zvažujete jeho použitie na svojich projektoch? Oslovte ma - [neoweb.sk](https://www.neoweb.sk)

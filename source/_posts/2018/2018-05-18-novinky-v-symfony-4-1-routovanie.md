---
id: 74
layout: post
title: "Novinky v Symfony 4.1 - Routovanie"
perex: |
    Symfony framework pokračuje v začatom trende inovácií vo svojej verzií 4.1 a opäť prináša množstvo zaujímavých noviniek. Poďme sa teda spolu pozrieť na niektoré z nich.
author: 33
lang: sk
---

Predtým ako sa vrhneme na novinky, upresníme si termín vydania verzie 4.1. Podľa roadmapy uverejenej na [oficiálnej stránke](https://symfony.com/roadmap?version=4.1#checker) je release stable verzie naplánovaný na **máj 2018**. Hurá, už tento mesiac. :)

Keďže noviniek vo verzií 4.1 je neúrekom, rozhodol som sa v tomto článku vybrať iba pár, týkajúcich sa routovania. [Routovacia komponenta](https://symfony.com/components/Routing) je presne to o čom budeme hovoriť. 

Zbytočne sa teda nezdržujme a poďme na to. 
 
## Rýchly a zbesilý

Router vo webovej aplikácií plní dve základné úlohy, a to **generovanie URL** adries z parametrov a **mapovanie URL** adries na interné zdroje aplikácie.

Router teda môže byť vo webovej aplikácií úzkym hrdlom, cez ktoré musí prejsť každá požiadavka.

### Ako je to teraz?

Aktuálny router z dôvodu optimalizácie výkonu generuje v čase kompilácie našej aplikácie novú triedu, ktorá obsahuje všetky optimalizované definície rout.

```php
class srcProdDebugProjectContainerUrlMatcher
{
    // ...

    public function match($rawPathinfo)
    {
        // ...

        // blog_post
        if (preg_match('#^/(?P<_locale>en|fr|de|es)/blog/posts/(?P<slug>[^/]++)$#s', $pathinfo, $matches)) {
            if ('GET' !== $canonicalMethod) {
                $allow[] = 'GET';
                goto not_blog_post;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'blog_post')), array (  '_controller' => 'App\\Controller\\BlogController::postShow',  '_locale' => 'en',));
        }

        // ...
    }
}
```

Pre každú URL adresu teda existuje v tejto triede definícia regulérneho výrazu, ktorému vyhovuje.

Následne po zavolaní metódy ``match()`` s príslušným reťacom, hľadá router vyhovujúcu definíciu skúšaním, čiže dochádza k opakovanému volaniu metódy ``preg_match``.

### Ako to bude po novom?

Základnou myšlienkou refaktoringu routovacej komponenty bolo obísť opakované volanie metódy ``preg_match`` a to spojením viacerých regulárnych výrazov v jeden.

```php
$regexList = array(
    0 => '{^(?'
            .'|/(en|fr|de|es)/admin/post/?(*:82)'
            .'|/(en|fr|de|es)/admin/post/new(*:166)'
            .'|/(en|fr|de|es)/admin/post/(\\d+)(*:253)'
            .'|/(en|fr|de|es)/admin/post/(\\d+)/edit(*:345)'
            .'|/(en|fr|de|es)/blog/?(*:519)'
            .'|/(en|fr|de|es)/blog/rss\\.xml(*:603)'
            .'|/(en|fr|de|es)/blog/page/([1-9]\\d*)(*:694)'
            .'|/(en|fr|de|es)/blog/posts/([^/]++)(*:784)'
        .')$}sD',
);

foreach ($regexList as $offset => $regex) {
    // ...

    default:
        $routes = array(
            // ...
            784 => array(array('_route' => 'blog_post', '_controller' => 'App\\Controller\\BlogController::postShow', '_locale' => 'en'), array('_locale', 'slug'), array('GET' => 0), null),
        );

    // ...
}
```

S touto myšlienkou v minulosti prišiel [Nikita Popov](http://nikic.github.io/aboutMe.html) vo svojom [článku](http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html) zo zákulisia svojej knižnice [FastRoute](https://github.com/nikic/FastRoute).

O technických detailoch novej komponenty napísal [Nicolas Grekas](https://medium.com/@nicolas.grekas) dva články:

* https://medium.com/@nicolas.grekas/making-symfonys-router-77-7x-faster-1-2-958e3754f0e1
* https://medium.com/@nicolas.grekas/making-symfony-router-lightning-fast-2-2-19281dcd245b

Podľa jeho meraní je nová komponenta **15x rýchlejšia ako FastRoute**!

### Ako nový router použiť?

Najprv musíte všetky Vaše doterajšie routy zmazať a ...

**Ufff, to by bol veľmi zlý scénar. Nič také nerobte, nie je potrebné!** 

Stači jednoducho po vydaní stable verzie Symfony 4.1 ak povýšime závislosť v ``composer.json``.

```yaml
"require": {
    "php": "^7.1.3",
    "ext-iconv": "*",
    "symfony/console": "^4.0",
    "symfony/flex": "^1.0",
    "symfony/framework-bundle": "^4.1",
    "symfony/yaml": "^4.0"
},
```
Následne zavoláme príkaz na aktualizáciu závislostí.
 
```bash
composer update
```
 
Nový router **funguje**! Úžasné, že? :)

## Internacionalizácia

Zákazník potrebuje webstránku vo viacerých jazykových mutáciách a jedným z kritérií sú internacionalizované url adresy.

Každému Symfony programátorovi už v hlave začína bežať scénar s použitím bundlu tretej strany, ako napríklad [JMSI18nRoutingBundle](https://github.com/schmittjoh/JMSI18nRoutingBundle), pretože Symfony router takúto možnosť priamo nepodporuje.

A to je presne chvíľa povedať: **Routrová komponenta v Symfony od verzie 4.1 plne podporuje internacionalizáciu rout**.

### Čo to presne znamená?

Integrovaná internacionalizácia URL adries je už integrovaná priamo do komponenty a v praxi nám umožní definovať jednotlivé adresy pre každý jazyk v časti ``path`` v konfiguračnom súbore ``routes.yml``.

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
    {
        // ...
    }
}
```

Internacionálny zápis URL adresy umožní vytvoriť pre každú adresu samostatnú routu, ktorá je označená ako ``dashboard`` pre slovenský variant či ``dashboard.en`` pre anglický.

### Generovanie rout

Generovanie rout prostredníctvom generátora ``UrlGeneratorInterface`` alebo funkcie ``path()`` v šablóne ostalo nezmenené.

Ak chceme vygenerovať URL adresu v rámci nastaveného jazyka (``_locale``) stačí zavolať metódu generate.

```php
$url = $urlGenerator->generate('dashboard');
```   

V tomto prípade sa nastaví aktuálna hodnota ``_locale`` z requestu.

Stále však môžeme jazyk aj explicitne zadefinovať.

```php
// Klasický zápis s parametrom
$url = $urlGenerator->generate('contact', ['_locale' => 'en']);

// Ekvivalentný zápis podľa označenia routy
$url = $urlGenerator->generate('dashboard.en');
```

## Inline definícia

Do tretice sa pozrieme na už poslednú novinky v rámci tohoto článku.

Bude to **inline definícia zástupných znakov** v rámci parametru ``path`` pri konkrétnej definícií routy.

Teraz, ak chceme v URL adrese prenášať parametre, musíme zadefinovať routu a k nej dodatočné argumenty ako ``requirements`` a prípadne aj ``defaults``, ak má parameter preddefinovanú hodnotu.

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
 
Pekne! Výsledok je ten istý a zápis je značne kratší.

Viacerí si pri pohľade na tento zápis spomeniete na [Nette](https://doc.nette.org/cs/2.4/routing#toc-validacni-vyrazy), kde sa niečo podobné už používa.

### Inline definícia v anotáciach

Skratený zápis môžeme použit vo všetkých formátoch, pozrime sa napríklad zápis routy v kontrolery.

```php
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends Controller
{
    // Klasický zápis s requirements a defaults

    /**
     * @Route("/admin/articles/{page}", name="admin-articles", requirements={"page"="\d+"}, defaults={"page"="1"})
     */
    public function __invoke(int $page)
    {
        // ...
    }
    
    // Inline zápis
    
    /**
     * @Route("/admin/articles/{page<\d+>?1}", name="admin-articles")
     */
    public function __invoke(int $page)
    {
        // ...
    }
}

```

Pokiaľ v rámci jednej adresy budeme definovať viacero parametrov, môže hlavne pri definícií v kontrolery zápis stratiť prehľadnosť.

```php
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends Controller
{
    // Inline zápis
     
    /**
     * @Route("/{_locale<en|es|fr>?en}/admin/articles/{category<news|releases|security>?news}/{page<\d+>?1}", name="admin-articles") */
     */
    public function __invoke(string $category, int $page)
    {
        // ...
    }
    
    // Klasický zápis s requirements a defaults
    
    /**
     * @Route("/{_locale}/admin/articles/{category}/{page}", name="admin-articles",
     *   "requirements"={
     *          "_locale": "en|es|fr",
     *          "category": "news|releases|security", "page": "\d"
     *   },
     *   "defaults"={
     *      "_locale": "en",
     *      "category": "news", "page": "1"
     *   }
     * )
     */
    public function __invoke(string $category, int $page)
    {
        // ...
    }
}

```

V takejto situácií odporúčam uprednostniť čitateľnosť a použiť klasický zápis.

## Záver

Vydanie stable verzie **Symfony 4.1** je na spadnutie a preto je skvelé, že už sme aspoň zhruba pripravení na použitie nových routerových features.

Ostáva ešte veľa noviniek, ktoré zo sebou prináša nová verzia, o tom snáď niekedy nabudúce. :) Ak ste nedočkaví odporúčam sledovať [oficiálny blog](https://symfony.com/blog/) Symfony frameworku.  

Teším sa na vecné komentáre, ktoré môžu mňa a moju prácu posunúť opäť o kúsok ďalej.

## Zdroje

* https://symfony.com/roadmap?version=4.1#checker
* https://symfony.com/blog/new-in-symfony-4-1-fastest-php-router
* https://symfony.com/blog/new-in-symfony-4-1-internationalized-routing
* https://symfony.com/blog/new-in-symfony-4-1-inlined-routing-configuration

## Kontakt

Zvažujete použitie Symfony 4 na svojich projektoch? Oslovte ma - [neoweb.sk](https://www.neoweb.sk)

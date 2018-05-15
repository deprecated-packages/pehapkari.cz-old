---
id: 73
layout: post
title: "Symfony 4 - Dependency Injection"
perex: |
    O Dependency Injection už počul snáď každý programátor. Symfony posúva hranice DI ďalej a v spojení s autokonfiguráciou služieb tvorí predpoklad pre vznik flexibilnejších a prehľadnejších aplikácií.
author: 33
lang: sk
---

## Trochu teórie

Implementácia návrhového vzoru Dependency Injection formou tzv. DI kontajnera, nasleduje myšlienku vkladania závislostí objektom z vonkajšieho prostredia. V praxi DI kontajner znižuje závislosť medzi jednotlivými objektmi a odoberá im zodpovednosť za získavanie svojich závislostí.

> _The basic idea of the Dependency Injection is to have a separate object, an assembler, that populates a field in a class with an appropriate implementation for the interface._
>
> --Martin Fowler
 
Najčastejšie používané formy vkladania závislostí do objektov sú **Constructor Injection** a **Setter Injection**.

**Constructor Injection** je vkladanie závislosti objektu prostredníctvom konštruktora. Konštruktor ako vstupný bod poskytuje prehľad o všetkých závislostiach objektu na jednom mieste a zabezpečuje jeho konzistenciu, teda objekt nemôže byť inštancovný bez potrebných závislostí.

Nevýhodou je možná neprehľadnosť konštruktora pri vyššom počte závislosti, čo je však skôr otázkou správneho návrhu.

```php
final class DependantService
{
	/**
     * @var DependencyService
     */
    private $service;

    public function __construct(DependencyService $service)
    {
        $this->service = $service;
    }
}
```

**Setter Injection** ako vkladanie závislosti prostredníctvom špecifických setrov zjednodušuje vytvorenie objektu. Setovanie závislosti nie je vyžadované a môže sa volať opakovane.

To je výhodné v prípade, ak je potrebné setovať závislosť až v čase behu aplikácie. Volanie setra nemusí nastať a výsledný objekt sa môže veľmi ľahko ocitnúť v nekonzistentnom stave.

```php
final class DependantService
{
	/**
     * @var DependencyService|null
     */
    private $service;

    public function setDependencyService(DependencyService $service)
    {
        $this->service = $service
    }
}
```

## Symfony a DI

Symfony 4 prináša pokročilé vlastnosti DI kontajnera, ktoré sú od verzie 3.4 predvolene aktivované.

Definícia služieb pre DI kontajner v Symfony projekte vykonávame v ``services.yml``, ktorý nájdeme v zložke ``config``.

Po inštalácií frameworku obsahuje súbor len základné predvolené nastavenia a obecnú registráciu služieb (autodiscovery) zameranú na celú zložku ``src``.

```bash
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
        
    App\:
        resource: '../src/*'
        exclude: '../src/{Entity,Migrations,Tests,Kernel.php}'
```

Na ukážke sekcia ``_defaults`` zahŕňa tri predvolené nastavenia, platiace pre všetky zaregistrované služby v ``services.yml``. Každé zo všeobecných nastavení môžeme preťažiť pri definícií konkrétnej služby.

```bash
services:
	_defaults:
        public: false
        
	App\Model\TemplateEngine:
		public: true
```

Sekcia ``_defaults`` je platná výhradne v rámci súboru, v ktorom je definovaná. Teda každý ``services.yml`` z bundlu alebo knižnice bude obsahovať vlastnú sadu týchto nastavení.

## Autowiring

Povolenie **autowiring**-u pre všetky služby umožňuje DI kontajneru ich vkladanie primárne prostredníctvom konštruktora do iných služieb, ktoré ich vyžadujú na základe typehintu.

```php
final class MailSender
{
	/**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
```

Vkladanie pracuje na úrovni typu služby alebo interfacu, ktorý služba implementuje.

Ak existujú viaceré služby implementujúce rovnaký interface DI kontajner nebude vedieť, ktorú službu má poskytnúť ako závislosť. K interfacu ako kľúču na úrovni DI kontajneru, priradíme konkrétnu implementáciu služby.
 
```bash
services:        
    App\Logger\LoggerInterface: '@App\Logger\MailLogger'
```

Od Symfony 3.4. existuje ekvivalentný zápis v sekcii bind.

```bash
services:
    _defaults:
        bind:        
			App\Logger\LoggerInterface: '@App\Logger\FileLogger'
```

Osobne mi tento zápis vyhovuje viac, pretože mám všetky bindy interfacov na jednom mieste v rámci konfigu. 

Nie všetky naše služby vyžadujúce ``App\Logger\LoggerInterface`` musia očakávať nabindovanú inštanciu ``App\Logger\FileLogger``. V tomto prípade môžeme implementáciu prebindovať pri konkrétnej definícií služby.

```bash
services:
    _defaults:
        bind:        
			App\Logger\LoggerInterface: '@App\Logger\FileLogger'
    
    App\Logger\:
        resource: '../src/Logger/*'
		
	App\Mailer\MailGenerator:
		bind:
			App\Logger\LoggerInterface: '@App\Logger\DatabaseLogger'			
```

### Action Injection

Symfony autowiring umožňuje vkladanie závislostí **aj priamo metódam v kontroleri**. Táto funkčnosť do určitej miery zjednodušuje prácu s kontrolerom, kedy nie je nutné k získaniu závislosti vytvárať konštruktor. 

```php
final class MailSendController
{
	public function __invoke(MailSenderInterface $mailSender)
	{
		// ...
	}
}		
```

**Odporúčam používať výhradne konštruktor na vkladanie závislosti za každých okolností**. Kontroler tak jednoznačne priznáva svoje závislosti.

Viac o problémoch spojených s používaním **action injection** sa môžete dočítať v článku od [Tomáša Votrubu](https://www.tomasvotruba.cz/blog/2018/04/23/how-to-slowly-turn-your-symfony-project-to-legacy-with-action-injection/).  

### Ako autowiring pracuje?

Autowiring nie je žiadnou mágiou, pretože každá závislosť je explicitne vyžadovaná konštruktorom či metódou kontroleru.

Vyžadovanú službu sa kontajner pokúsi vyhľadať medzi existujúcimi službami podľa tzv. ID, teda plne špecifikovaného doménového názvu ([FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name)). Pokiaľ je k dispozícií vloží ju.

V opačnom prípade sa pokúsi službu nakonfigurovať podľa definície opätovne na základe zhodnosti ID s doménovým názvom ([FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name)). V prípade neúspechu vyhodí zrozumiteľnú výnimku o chýbajúcej službe.

![autowiring-exception](/assets/images/posts/2018/symfony-4-dependency-injection/autowire-exception.png)

Špecifický doménový názov ([FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name)) v prípade DI kontajnera predstavuje kompletný názov triedy s celým namespacom, teda napríklad ``App\Logger\FileLogger``.

## Autokonfigurácia

Povolenie ``autoconfigure``, ako už názov napovedá, povoľuje automatickú konfiguráciu služieb, resp. automatické tagovanie. Tagy sú interné značky kontajnera, ktoré nemajú žiaden význam mimo jeho hraníc.

Predstavme si službu, ktorá rozširuje šablónovací systém **Twig**. Služba musí implementovať rozhranie ``Twig_ExtensionInterface``.

Bez automatickej konfigurácie ju musíme ručne zaregistrovať v ``services.yml`` a zadefinovať správny tag ``twig.extension`` tak, aby kontajner vedel ako má so službou pracovať.

```bash
services:
    _defaults:
        autoconfigure: false

    App\Templating\AcmeTwigExtension:
		tags: [twig.extension]
		
    App\:
		resource: '../src/*'
```

Tento zápis môžeme zjednodušiť definíciou tagu pre interface v sekcii``_instanceof``.

```bash
services:
    _defaults:
        autoconfigure: false
		
    _instanceof:
        Twig_ExtensionInterface:
            tags: [twig.extension]			
	
    App\:
		resource: '../src/*'
```

Keďže programátor je tvor lenivý, zapneme ``autoconfigure`` a nemusíme nič explicitne definovať.

```bash
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    App\:
		resource: '../src/*'
```

## Viditeľnosť služieb

Symfony 4 predvolene nastavuje registrované služby ako privátne. K privátnym službám nie je možné pristupovať cez kontajner známym ``$container->get()``. **Odporúčam ponechať toto nastavenie a obmedziť tak prístup k službám cez kontajner**.

V nutnom prípade môžeme preťažiť parameter ``public`` u konkrétnej definície služby.

```bash
services:
    _defaults:
        public: false
        
	App\Logger\MailLogger:
		public: true
```

## Bindovanie parametrov

Častokrát potrebujeme vložiť službám skalárne argumenty, napríklad číslo alebo reťazec. V takomto prípade by sme museli explicitne definovať službu alebo viacero služieb, ak argument vyžadovali.

```bash
services:
    App\Logger\MailLogger:        
        arguments:
            $logDir: '%kernel.project_dir%/var/log'

    App\Logger\QueryLogger:        
        arguments:
            $logDir: '%kernel.project_dir%/var/log'
```

Autowiring skalárnych argumentov môžeme vyriešiť jednoduchšie pomocou tzv. **bindovania** v sekcii ``_defaults``.

```bash
services:
    _defaults:    
        bind:
            $logDir: '%kernel.project_dir%/var/log'
```

Následne všetky naše služby vyžadujúce skalárny argument ``$logDir`` získajú nabindovaný skalár.

Ak pre niektorú zo služieb potrebujeme určiť inú hodnotu argumentu ``$logDir`` musíme opäť definíciu služby preťažiť:

```bash
services:
    _defaults:
        bind:		
            $logDir: '%kernel.project_dir%/var/log'
		
	App\Logger\QueryLogger:
		bind:
            $logDir: '%kernel.project_dir%/var/log/query'
```

## Registrácia služieb

Hromadná registrácia služieb (autodiscovery) prostredníctvom špecifického doménového názvu ([FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name)) odtieňuje vývojára od zdĺhavej definície každej služby, sprehľadňuje konfiguračný súbor ``services.yml`` a v neposlednom rade značne zvyšuje efektivitu práce tým, že definície služieb vytvára automaticky.

Definícia začína určením spoločného doménového názvu ([FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name)), ktorý musí byť ukončený spätným lomítkom.

Prvý argumentom ``resource`` definujeme cestu k zložke, kde sú umiestnené súbory pre registráciu a druhým nepovinným parametrom ``exclude`` môžeme určiť, ktoré zložky alebo súbory sa majú z registrácie vylúčiť. Definícia môže obsahovať aj iné parametre napr. ``arguments``, ``tags`` a podobne.:  

```bash
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
        
    App\:
        resource: '../src/*'
        exclude: '../src/{Entity,Migrations,Tests,Kernel.php}'
```


Z ukážky vyššie je zrejmé, že v základnom nastavení sa registrujú všetky služby, ktorých doménový názov začína na ``App``.

Do parametrov ``resource`` a ``exclude`` nemusíme definovať len presnú cestu, umožňujú aj valídny zápis cesty so zástupnými znakmi v [glob patterne](https://en.wikipedia.org/wiki/Glob_(programming)). 

Predstavme si jednoduchý príklad, kde chceme vylúčiť z načítania všetky súbory, ktorých názov obsahuje reťazec **Command** alebo **Query** a tieto súbory môžu byť zanorené v ľubovoľnej hierarchií zložiek:
 
```bash
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
        
    App\:
        resource: '../src/*'
        exclude: '../src/**/*{Command,Query}.php'
```
 
V zápise ``resource`` sme použili zástupný znak ``*``, ktorý  v tomto prípade zastupuje akýkoľvek názov súboru. Zástupné znaky, môžeme spresňovať prefixom alebo sufixom, ako sme to urobili v zápise argumentu ``exclude``. Za zmienku ešte stojí znak ``**``, ktorý zastupuje rôznu úroveň vnorenie adresárov.

Viac informácií o **glob patterne** nájdete na [wikipédií](https://en.wikipedia.org/wiki/Glob_(programming)).

Definície registrácií odporúčam radiť od obecných po konkrétne. Auto registrácia má vyššiu prioritu ako ručná registrácia a teda neskorší obecný zápis preťaží všetky ručné definície v rámci [FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name).

Na takéto správanie som narazil v súvislosti s importom konfiguračného súboru so službami. Import sa vždy spracúva pred sekciou ``services`` a teda všetky moje definície s konkrétnymi zmenami mi auto registrácia odstránila.

## Debugovanie

Pre efektívnejšiu prácu a potreby ladenia kontajnera môžeme použiť základné konzolové príkazy ako ``debug:container`` a ``debug:autowiring``.

### Príkaz debug:container

Otvoríme konzolu a v koreňovom adresári Symfony projektu zadáme príkaz ```php bin/console debug:container```.

![debug-container](/assets/images/posts/2018/symfony-4-dependency-injection/debug-container.jpg)

Výsledkom príkazu je zoznam všetkých verejných služieb.

Príkaz má viacero nastavení, z ktorých stojí za zmienku napríklad parameter ``--show-private``. Tento parameter spôsobí, že výpis kontajnera bude obsahovať všetky služby vrátane privátnych, ktoré štandardne výpis neobsahuje.

Skúsme teda do konzole zadať príkaz ```php bin/console debug:container --show-private```. Výstup už bude obsahovať všetky služby, ktorými disponuje kontajner bez ohľadu na to či sú verejné alebo nie.

Keď projekt rastie služieb pribúda a zoznam rastie. Práca s ním sa stáva nepohodlná a práve teraz je vhodný čas na vyhľadávanie služieb v zozname. Skúsme teda zadať príkaz ```php bin/console debug:container RedirectController``` 

![debug-container-search](/assets/images/posts/2018/symfony-4-dependency-injection/debug-container-search.jpg)

Skvelé príkaz vyhľadá v zozname služieb také, ktoré vyhovujú hľadaniu. V prípade, ak podľa reťazca vyhovuje viacero služieb ponúkne nám jednoduchý výber.

### Príkaz debug:autowiring

Kontajner obsahuje množstvo služieb, z ktorých nie všetky môžu byť dostupné pre autowiring.

Skúsme teda do konzoly zadať príkaz ``php bin/console debug:autowiring``.

![debug-autowiring](/assets/images/posts/2018/symfony-4-dependency-injection/debug-autowiring.jpg)

Skript nám vypíše všetky dostupné služby, ktoré môžu byť vkladané ako závislosti. Modrou farbou sú označené doménové adresy ([FQCN](https://en.wikipedia.org/wiki/Fully_qualified_name)), teda ID kľúče, na základe ktorých sú služby autowirované.

Ako u predchádzajúceho príkazu aj v tomto zozname môžeme vyhľadávať uvedením prvého argumentu za príkazom, napríklad ``php bin/console debug:autowiring RedirectController``

### Nápoveda k príkazom

Viac informácií o príkazoch nájdete v nápovede pre konkrétny konzolový skript. Skúsme sa dozvedieť viac k príkazu ``debug:autowiring``. Napíšme teda do konzoly príkaz ``php bin/console debug:autowiring -h``.

![debug-autowiring-help](/assets/images/posts/2018/symfony-4-dependency-injection/debug-autowiring-help.jpg)

Príkaz vypíše možné argumenty a parametre príkazu, krátky popis a v lepšom prípade aj možnosti použitia.
 
## Záver

Depency Injection je skvelá myšlienka, ktorá nám umožňuje vytvárať lepšie a flexibilnejšie aplikácie. Symfony túto myšlienku pomaly ale iste doťahuje k dokonalosti.

Verím, že sa mi podarilo priblížiť Vám základné použitie DI v Symfony 4 a ďalšie rozšírené možnosti môžete nájsť v [oficiálnej dokumentácií](http://symfony.com/doc/current/service_container.html).

Teším sa na vecné komentáre, ktoré môžu mňa a moju prácu posunúť opäť o kúsok ďalej.

## Zdroje

* https://symfony.com/doc/current/service_container.html
* https://symfony.com/doc/current/service_container/3.3-di-changes.html
* https://symfony.com/blog/new-in-symfony-3-3-simpler-service-configuration
* https://symfony.com/blog/new-in-symfony-3-3-service-autoconfiguration
* https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
* https://symfony.com/blog/new-in-symfony-3-4-local-service-binding
* https://en.wikipedia.org/wiki/Glob_(programming)
* https://en.wikipedia.org/wiki/Fully_qualified_name
* https://www.tomasvotruba.cz/blog/2018/04/23/how-to-slowly-turn-your-symfony-project-to-legacy-with-action-injection/

## Kontakt

Zvažujete použitie Symfony 4 na svojich projektoch? Oslovte ma - [neoweb.sk](https://www.neoweb.sk)

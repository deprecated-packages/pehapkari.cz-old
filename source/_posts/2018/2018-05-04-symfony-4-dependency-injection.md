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

Implementácia návrhového vzoru Dependency Injection formou tzv. DI kontajnera, nasleduje myšlienku vkladanie závislosti objektom z vonkajšieho prostredia. V praxi DI kontajner znižuje závislosti medzi jednotlivými objektmi a odoberá im zodpovednosť za získavanie svojich závislosti.

> _The basic idea of the Dependency Injection is to have a separate object, an assembler, that populates a field in a class with an appropriate implementation for the interface._
>
> --Martin Fowler
 
Najčastejšie používané formy vkladania závislosti do objektov sú **Constructor Injection** a **Setter Injection**.

**Constructor Injection** je vkladanie závislosti objektu prostredníctvom konštruktora. Konštruktor ako vstupný bod poskytuje prehľad o všetkých závislostiach objektu na jednom mieste a zabezpečuje jeho konzistenciu, teda objekt nemôže byť inštancovný bez potrebných závislosti. Nevýhodou je možná neprehľadnosť konštruktora pri vyššom počte závislostí, čo je však skôr otázkou správneho návrhu.

````php
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
````

**Setter Injection** ako vkladanie závislosti prostredníctvom špecifických setrov zjednodušuje vytvorenie objektu. Setovanie závislosti nie je vyžadované a môže sa volať opakovane. To je výhodné v prípade, ak je potrebné setovať závislosť až v čase behu aplikácie. Keďže volanie setra nemusí nastať, objekt je nekonzistentný v prípade, ak je závislosť nutná pre jeho správne fungovanie.

````php
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
````

## Symfony a DI

Symfony 4 sprostredkúva pokročilé vlastnosti DI kontajnera. Od verzie 3.4 je autowiring a autokonfigurácia služieb predvolene aktivovaná.

Definícia služieb pre DI kontajner v Symfony projekte sa vykonáva v ``services.yml``, ktorý nájdeme v zložke ``config``. Po inštalácií frameworku obsahuje súbor len základné predvolené nastavenia a obecnú registráciu služieb (autodiscovery) zameranú na celú zložku ``src``.

````
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
        
    App\:
        resource: '../src/*'
        exclude: '../src/{Entity,Migrations,Tests,Kernel.php}'
````

Na ukážke sekcia ``_defaults`` zahŕňa tri predvolené nastavenia, platiace pre všetky zaregistrované služby v ``services.yml``. Každé zo všeobecných nastavení môžeme preťažiť pri definícií konkrétnej služby následovne:

````
services:
	_defaults:
		// ...
		
	App\Model\MailSender:
        autowire: false
        
	App\Model\TemplateEngine:
		public: true
````

Sekcia ``_defaults`` je platná výhradne v rámci súboru, v ktorom je definovaná. Teda každý ``services.yml`` z bundlu alebo knižnice bude obsahovať vlastnú sadu týchto nastavení.

## Autowiring

Povolenie **autowiring**-u pre všetky služby umožňuje DI kontajneru ich vkladanie prostredníctvom konštruktora do iných služieb, ktoré ich vyžadujú na základe typehintu.

````php
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
````

Vkladanie pracuje na úrovni typu služby alebo interfacu, ktorý služba implementuje.

Ak existujú viaceré služby implementujúce rovnaký interface DI kontajner nebude vedieť, ktorú službu má poskytnúť ako závislosť. K interfacu ako kľúču na úrovni DI kontajneru, priradíme konkrétnu implementáciu služby:
 
````
services:        
    App\Logger\LoggerInterface: '@App\Logger\MailLogger'
````

Od Symfony 3.4. existuje ekvivalentný zápis v sekcii bind.

````
services:
    _defaults:
        bind:        
			App\Logger\LoggerInterface: '@App\Logger\FileLogger'
````

Nie všetky naše služby vyžadujúce ``App\Logger\LoggerInterface`` musia očakávať nabindovanú inštanciu ``App\Logger\FileLogger``. V tomto prípade môžeme implementáciu prebindovať pri konkrétnej definícií služby:

````
!!!! Otestovať
services:
    _defaults:
        bind:        
			App\Logger\LoggerInterface: '@App\Logger\FileLogger'
    
    App\Logger\:
        resource: '../../src/Logger/*'
		
	App\Mailer\MailGenerator:
		bind:
			App\Logger\LoggerInterface: '@App\Logger\DatabaseLogger'			
````

Symfony autowiring umožňuje vkladanie závislosti **aj priamo metódam v kontroleri**. Táto funkčnosť vytvára zjednodušenie pri práci s kontrolerom, kedy nie je nutné v určitých prípadoch vytvárať konštruktor. 

````php
final class MailSendController
{
	public function __invoke(MailSenderInterface $mailSender)
	{
		// ...
	}
}		
````

**Osobne preferujem použitie konštruktora za každých okolností**.

Kontroler tak jednoznačne priznáva svoje závislosti, v kontroleroch nevzniká dvojitá cesta získavania závislostí a v neposlednom rade ak požadovaná služba chýba v DI kontajneri aplikácia skončí chybou.   

### Ako autowiring pracuje?

Autowiring nie je žiadnou mágiou, pretože každá závislosť je explicitne vyžadovaná konštruktorom či metódou kontroleru.

Vyžadovanú službu sa DI kontajner pokúsi vyhľadať medzi existujúcimi službami podľa ID, teda plne špecifikovaného doménového názvu (FCQN). Pokiaľ je k dispozícií vloží ju.

V opačnom prípade sa pokúsi službu nakonfigurovať podľa definície, ktorú hľadá medzi definíciami opätovne na základe zhodnosti ID s doménovým názvom (FQCN). V prípade neúspechu vyhodí zrozumiteľnú výnimku o chýbajúcej službe.

![autowiring-exception](/assets/images/posts/2018/symfony-4-dependency-injection/autowire-exception.png)

Vytváranie služieb je lazy a tak ich DI kontajner vytvára až v čase, keď sú potrebné.

## Autokonfigurácia

Povolenie ``autoconfigure``, ako už názov napovedá, povoľuje automatickú konfiguráciu služieb, resp. automatické tagovanie. Tagy sú interné značky DI kontajnera, ktoré nemajú žiaden význam mimo jeho hranice. Na základe tagu môže byť služba v rámci DI kontajneru spracovaná.

Predstavme si služby, ktorá rozširuje šablónovací systém **Twig**. Služba musí implementovať rozhranie ``Twig_ExtensionInterface``. Bez automatickej figurácie ju musíme ručne zaregistrovať v ``services.yml`` a zadefinovať správny tag, v tomto prípade ``twig.extension``: 

````
services:
    _defaults:
        autowire: true
        autoconfigure: false

    App\Templating\AcmeTwigExtension:
		tags: [twig.extension]
		
    App\:
		resource: '../../src/*'
````

Tento zápis môžeme zjednodušiť definíciou tagu pre interface v sekcii``_instanceof``.

````
services:
    _defaults:
        autowire: true
        autoconfigure: false
		
    _instanceof:
        Twig_ExtensionInterface:
            tags: [security.voter]			
	
    App\:
		resource: '../../src/*'
````

Keďže programátor je tvor lenivý, zapneme ``autoconfigure`` a nemusíme nič explicitne definovať.

````
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
		resource: '../../src/*'
````

## Viditeľnosť služieb

Symfony 4 predvolene nastavuje registrované služby ako privátne. K privátnym službám nie je možné pristupovať cez kontajner známym ``$container->get()``. Odporúčam zachovať nastavenie a obmedziť tak prístup k službám cez kontajner.

V nutnom prípade môžeme preťažiť paramter ``public`` u konkrétnej definície služby:

````
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false
        
	App\Logger\MailLogger:
		public: true
````

## Bindovanie parametrov

Častokrát potrebujeme vložiť službám skalárne argumenty, napríklad číslo alebo reťazec. V takomto prípade by sme museli explicitne definovať službu alebo viacero služieb, ak argument vyžadovali.

````
services:
    _defaults:
        // ...

    App\Logger\MailLogger:
        $logDir: '%kernel.project_dir%/var/log'

    App\Logger\QueryLogger:
        $logDir: '%kernel.project_dir%/var/log'
````

Autowiring skalárnych argumentov môžeme vyriešiť jednoduchšie pomocou tzv. **bindovanie** v sekcii ``_defaults``.

````
services:
    _defaults:
        // ...
		
        bind:
            $logDir: '%kernel.project_dir%/var/log'
````

Následne všetky naše služby vyžadujúce skalárny argument ``$logDir`` získajú nabindovaný skalár.

Ak pre niektorú zo služieb potrebujeme určiť inú hodnotu argumentu ``$logDir`` musíme definíciu služby preťažiť:

````
!!!! Otestovať
services:
    _defaults:
        bind:		
            $logDir: '%kernel.project_dir%/var/log'
		
	App\Logger\QueryLogger:
		bind:
            $logDir: '%kernel.project_dir%/var/log/query'
````

## Registrácia služieb

Hromadná registrácia služieb (autodiscovery) prostredníctvom špecifického doménového názvu (FQCN) odtieňuje vývojára od zdĺhavej definície každej služby, sprehľadňuje konfiguračný súbor ``services.yml`` a v neposlednom rade značne zvyšuje efektivitu práce tým, že definície služieb vytvára automaticky.

Definícia začína určením spoločného doménového názvu (FQCN), ktorý musí byť ukončený spätným lomítkom.

Prvý argumentom ``resource`` definujeme cestu k zložke, kde sú umiestnené súbory pre registráciu a druhým nepovinným parametrom ``exclude`` môžeme určiť, ktoré zložky alebo súbory sa majú z registrácie vylúčiť. Definícia môže obsahovať aj iné parametre napr. ``arguments``, ``tags`` a podobne.:  

````
services:
    _defaults:
        // ...
        
    App\:
        resource: '../src/*'
        exclude: '../src/{Entity,Migrations,Tests,Kernel.php}'
````


Z ukážky vyššie je zrejmé, že v základnom nastavení sa registrujú všetky služby, ktorých doménový názov začína na ``App``.

Do parametrov ``resource`` a ``exclude`` nemusíme definovať len presnú cestu, ale pre zvýšenie flexibility umožňujú aj valídny zápis cesty so zástupnými znakmi v [glob patterne](https://en.wikipedia.org/wiki/Glob_(programming)). 

Predstavme si jednoduchý príklad, kde chceme vylúčiť z načítania všetky súbory, ktorých názov obsahuje reťazec **Command** alebo **Query** a tieto súbory môžu byť zanorené v ľubovoľnej hierarchií zložiek:
 
````json
services:
    _defaults:
        // ...
        
    App\:
        resource: '../src/*'
        exclude: '../src/**/*{Command,Query}.php'
````
 
V zápise ``resource`` sme použili zástupný znak ``*``, ktorý  v tomto prípade zastupuje akýkoľvek názov súboru. Zástupné znaky, môžeme spresňovať prefixom alebo sufixom, ako sme to urobili v zápise argumentu ``exclude``. Za zmienku ešte stojí znak ``**``, ktorý zastupuje rôznu úroveň vnorenie adresárov.

Viac informácií o ``glob patterne`` nájdete na [wikipédií](https://en.wikipedia.org/wiki/Glob_(programming)).

Definície registrácií odporúčam radiť od obecných po konkrétne. Auto registrácia má vyššiu prioritu ako ručná registrácia a teda neskorší obecný zápis preťaží všetky ručné definície.

Na takéto správanie som narazil v súvislosti s importom súborov ``yml``, ktoré obsahovali konkrétne definície služieb. Importy sa vždy spracúvajú pred sekciou ``services`` a teda všetky moje definície s konkrétnymi zmenami mi auto registrácia odstránila.

## Debugovanie

Pre efektívnejšiu prácu a potreby ladenia kontajnera môžeme použiť základné konzolové príkazy ako napríklad ``debug:autowiring`` a ``debug:container``.

Príkaz ``debug:autowiring`` vypisuje zoznam všetkých dostupných služieb alebo ich interfacov, ktoré môžu byť vkladané ako závislosti.

… príkaz a jeho výstup … 

Nepovinný argument ``search`` vyfiltruje zoznam podľa zadanej hodnoty, nech sa nachádza v ktorejkoľvek časti názvu.

… príkaz a výpis …

Príkaz ``debug:container`` vypisuje aktuálny zoznam všetkých verejných služieb v kontajneri. Nepovinný argument ``name`` umožňuje vyhľadať konkrétnu službu.

… príkaz list + name + vypis …

Príkaz má viacero nastavení, z ktorých stojí za zmienku napríklad parameter ``--show-private``. Tento parameter spôsobí, že výpis kontajnera bude obsahovať všetky služby vrátane privátnych, ktoré štandardne výpis neobsahuje.

… príkaz + list ...

Viac informácií o príkazoch nájde v nápovede pre konkrétny konzolový skript.

… príkaz nápovedy pre autowiring -h + výpis
 
## Záver

Depency Injection je skvelá myšlienka, ktorá nám umožňuje vytvárať lepšie a flexibilnejšie aplikácie. Symfony túto myšlienku pomaly ale iste doťahuje k dokonalosti.

Verím, že sa mi podarilo priblížiť Vám základné použitie DI v Symfony 4 a ďalšie rozšírené možností môžete nájsť v [oficiálnej dokumentácií](http://symfony.com/doc/current/service_container.html).

Teším sa na vecné komentáre, ktoré môžu mňa a moju prácu posunúť opäť o kúsok ďalej.

## Zdroje

* https://symfony.com/doc/current/service_container.html
* https://symfony.com/doc/current/service_container/3.3-di-changes.html
* https://symfony.com/blog/new-in-symfony-3-3-simpler-service-configuration
* https://symfony.com/blog/new-in-symfony-3-3-service-autoconfiguration
* https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
* https://symfony.com/blog/new-in-symfony-3-4-local-service-binding
* https://en.wikipedia.org/wiki/Glob_(programming)

## Kontakt

Zvažujete použitie Symfony 4 na svojich projektoch? Oslovte ma - [neoweb.sk](https://www.neoweb.sk)

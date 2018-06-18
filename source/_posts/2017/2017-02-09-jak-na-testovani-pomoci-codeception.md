---
id: 19
title: "Jak na akceptační testovaní pomocí Codeception"
perex: "Jak automaticky testovat celé stránky a simulovat chování uživatele na webu pomocí Codeception"
author: 15
tweet: "Urodilo se na blogu: Jak na akceptační testovaní pomocí #Codeception"
---

## Co je to Codeception a proč jej používat?

Codeception je PHP knihovna umožňující testovat webové aplikace přímo v prohlížeči. Testuje tak z pohledu uživatele, jestli funguje celý web, jak backend, tak frontend, neomezuje se jen na testování jednotlivých tříd. Díky Codeception můžete snadno a rychle proklikat velkou část webu a otestovat celé uživatelské scénáře
(například registraci, přihlášení, objednávku v eshopu apod.).

Dokumentace Codeception je k přečtení [zde](http://codeception.com/quickstart).

Codeception má v sobě několik knihoven, které jde pro testy používat.

- Buď můžete na testování stránek používat
[PhpBrowser](http://codeception.com/docs-2.0/04-AcceptanceTests#PHP-Browser), který **umožňuje proklikávat stránky a
vyplňovat formuláře, ale neumožní spouštění javascriptu**.

- Pokud chcete plnohodnotný prohlížeč, **Codeception nám umožňuje testovat v reálném prohlížeči** pomocí
[Selenium Webdriveru](http://codeception.com/docs-2.0/04-AcceptanceTests#Selenium-WebDriver). Na jeho zprovoznění budeme potřebovat Selenium Server, jehož instalaci popisuji v dalším kroku. Jeho výhoda je mimo jiné v tom, že při selhání testu udělá printscreen a uloží html kód stránky, na které test selhal.

## Jak začít, co potřebuji?

Nejdříve si k projektu nainstalujeme přes composer Codeception:

```bash
composer require codeception/codeception --dev
```

### Instalujeme Selenium

A nyní si stáhneme a zprovozníme Selenium Server.

* Nejdříve musíme mít nainstalovanou [Javu](https://java.com/en/download/).
* Stáhnout Selenium Server lze na [této stránce](http://docs.seleniumhq.org/download/) v sekci "Selenium Standalone Server". Přímý link na stažení je [zde](https://goo.gl/Lyo36k).
* Stažený `.jar` soubor umístíme kamkoli, kde na něj nezapomeneme.
* Selenium WebDriver umožňuje používat různé prohlížece, my nyní budeme používat google chrome.
* Stahneme si chromedriver [zde](https://sites.google.com/a/chromium.org/chromedriver/downloads).
* Stahneme si archiv pro náš konkrétní počítač.
* Archiv extrahujeme na stejné místo, kde máme Selenium Server.
* Vytvoříme si soubor `selenium.sh` s obsahem

```bash
java -jar -Dwebdriver.chrome.driver=chromedriver selenium-server-standalone-3.0.1.jar
```
* přidáme jej do PATH, abychom jej měli odkudkoli snadno dostupný.

Pokud používáme Windows, vytvoříme místo toho analogicky `selenium.bat` s obsahem

```bash
java -jar -Dwebdriver.chrome.driver=chromedriver.exe selenium-server-standalone-3.0.1.jar
```

Tento soubor spustíme. Nyní by měl nastartovat Selenium Server.

Všechno v Codeceptionu se řídí přes CLI. Od inicializace, přes vytváření testů (generování kódu pro skelet testů),
až po spouštění.

Nejdříve Codeception inicializujeme:

```bash
codecept bootstrap
```

Pokud systém nenašel příkaz codecept, pak použijeme variantu s cestou (předpokládám, že jsme v rootu našeho projektu)

- pro Linux: `vendor/bin/codecept bootstrap`

- pro Windows: `vendor\bin\codecept bootstrap`

Tohle ve složce `tests` vytvoří složky pro různé druhy testů a konfigurační soubory se základním nastavením.

## První test

Pro ukázku budeme testovat různé stránky na serveru seznam.cz. Díky tomu vidíme, že narozdíl od unit testů není
na akceptační testování zapotřebí mít přístup ke zdrojákům, stačí běžící aplikace.

Nyní si nakonfigurujeme Codeception, aby používal URL adresu aplikace, kterou chceme testovat, a aby používal Selenium
jako prohlížeč. To uděláme tak, že upravíme soubor `acceptance.suite.yml` a jeho obsah upravíme na

```yaml
# acceptance.suite.yml

class_name: AcceptanceTester
modules:
    enabled:
        - WebDriver:
            url: https://www.seznam.cz
            browser: chrome
        - \Helper\Acceptance
```

Pokud chceme testovat naši vlastní aplikaci, použijeme stejnou adresu, jako používáme při ručném proklikávání.
Řádek, kde je ` - WebDriver:` říká, jaký prohlížeč chceme použít. Základní je PhpBrowser, ten sice neumožní testovat
javascript, ale zato je o poznání rychlejší.

Nyní máme všechno připravené a můžeme se pustit do prvního testu.

Takže první test si vytvoříme:

```bash
codecept generate:cept acceptance Homepage
```

a jako u inicializace, pokud nemáme soubor codecept v cestě, použijeme variantu

```bash
vendor/bin/codecept generate:cept acceptance Homepage
```
(a jako výše, pro Windows se zpětnými lomítky)

Tento příkaz nám vytvořil soubor `tests\acceptance\HomepageCept.php`
Otevřeme si ho a rovnou můžeme psát jednotlivé testy. Otestujeme načtení hlavní stránky a
zda obsahuje všechny důležité prvky.
Testujeme tedy [homepage seznam.cz](https://www.seznam.cz).
Testovat přítomnost prvků můžeme jak testováním, zda je na stránce nějaký text, tak CSS selectory nebo XPath selectory.
```php
// tests/acceptance/HomepageCept.php

$I = new AcceptanceTester($scenario);
$I->wantTo('Test homepage');    //nic nevykonává, ale slouží pro nás. Když test neprojde, vypíše se mimo jiné tento string
$I->amOnPage('/');              //kontrola, že jsem na homepage
$I->seeElement("form input[id=\"fulltext-field\"]");    //testuji, zda vidím vyhledávací okno
$I->seeElement("span.gadget__calendar");                //kontrola, že se vypisuje den
$I->seeElement("div.gadget--novinky");                  //je zobrazený rámeček s novinkami
$I->see("Novinky", "div.gadget--novinky");              //a v rámečku je napsán text "Novinky"
```

Test spustíme následovně:

```bash
codecept run
```

Také můžeme pouštět pouze konkrétní testovací soubory:

```bash
codecept run acceptance HomepageCept
```

## Formuláře a přechod na jiné stránky

Nyní si zkusíme otestovat vyhledávání a pár prokliků z něj.

```php
// tests/acceptance/HomepageCept.php

$I = new AcceptanceTester($scenario);
$I->amOnPage('/');
$I->fillField("form input[id=\"fulltext-field\"]", "php");  //do vyhledávacího políčka se zadá php
$I->click("form button.button--submit");
$I->waitForElement("div.searchpage", 3);                //počkáme, než se stránka s výsledky načte, ale max. 3 sekundy
$I->seeNumberOfElements("div.results > div", 13);       //13 výsledků yhledávání
$I->see("1 filipínské peso", "div.results > div");      //ve výsledcích je kurz filipínského pesa
$I->click("#navGoods");                                 //překliknu na záložku zboží
$I->waitForElement("main header", 3);                   //počkám na načtení
$I->see("Informatika a výpočetní technika", "main header > h1");    //zjistím, že  php spadá pod výpočetní techniku
$I->click("Zobrazit podrobnosti", "main article");                  //prokliknu se na detail prvního zboží
$I->seeInCurrentUrl("vyrobek/php-nejen-pro-zacatecniky-cd");        //zkontroluji slug v url
```

Přehled všech použitelných metod je dostupný v [dokumentaci Codeceptionu](http://codeception.com/docs/modules/WebDriver)

## Cest třídy a OOP psaní testů

Pokud nechceme jen procedurální testy, ale chceme je lépe členit, je možné psát testy i objektově, pomocí Cest tříd.
Ty vygenerujeme takto
```bash
codecept generate:cest acceptance Homepage
```
a vidíme, že máme vytvořenou třídu `HomepageCest` v souboru `tests/acceptance/HomepageCest.php`.

Testy můžeme psát do jakýchkoli public metod, kromě metod začínajících podrtžítkem. Ty mají specifický význam:

- metoda `_before` je zavolána před každým testem
- metoda `_after` je zavolána po každém testu
- v cest testech je navíc metoda `_failed`, která se zavolá těsně poté, co nějaký test neprojde

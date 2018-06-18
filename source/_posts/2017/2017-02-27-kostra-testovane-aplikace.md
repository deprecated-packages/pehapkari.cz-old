---
id: 23
title: "Kostra testované aplikace"
perex: "Stále tápete, jak vytvořit základní kostru aplikace, kde je Composer vč. autoloaderu a máte i testy? Podíváme se na to, že to je velmi jednoduché."
author: 16
tweet: "Urodilo se na blogu: Kostra testované aplikace #testing #sandbox"
---

## Co budeme dělat?

Vytvoříme se primitivní kalkulačku (která umí pouze sčítat celá čísla). A k tomu si napíšeme test, jestli sčítá správně.

Povedu Vás krok po kroku. Článek je určen **pro začátečníky** na Windows. Předpokládám ale, že:

* máte nainstalované [PHP 7](http://php.net/) (třeba s [XAMPP](https://www.apachefriends.org/download.html)), [Composer](https://getcomposer.org/), nějaké IDE (třeba [PhpStorm](https://www.jetbrains.com/phpstorm/)), [Git](https://git-scm.com/) a umíte to vše, alespoň trochu, použít.

Pokud s něčím budete mít problémy, podělte se v diskuzi dole pod článkem. Rádi poradíme.

## První krůčky - založení projektu

Začnu od píky. A provedu Vás po bodech.

1. Spusťte si Git Bash a vytvořte adresář pro web:

    ```bash
    cd C:\xampp\htdocs
    mkdir test-project
    cd test-project
    ```

2. Uvnitř nového adresáře inicializujte Git:

    ```bash
    git init
    ```

3. Otevřete si adresář ve svém oblíbeném IDE.

4. Vytvořte si nový `.gitignore` soubor a zakažte verzování `vendor` (právě ten adresář si bude spravovat Composer)

    ```bash
    vendor
    ```

    Jestliže zastáváte názor, že se i vendor verzuje, klidně si jej zaverzujte. V tom případě tenhle krok zkrátka ignorujte.

5. Přidejte si `composer.json` a vyžádejte si [PHPUnit](https://phpunit.de/), abyste mohli psát testy a nastavte si autoloader tak, aby se načítaly používané třídy:

    ```json
    {
        "name": "example/test-project",
        "description": "Test Project",
        "minimum-stability": "stable",
        "license": "MIT",
        "authors": [
            {
                "name": "author's name",
                "email": "email@example.com"
            }
        ],
        "require": {
            "php": ">=7.0"
        },
        "require-dev": {
            "phpunit/phpunit": "^6.0"
        },
        "autoload": {
            "psr-4": {
                "App\\": "src"
            }
        }
    }

    ```

6. Nainstalujte si závislosti spuštěním `composer install`.

7. Nakonec si vytvořte adresář `src` a v něm soubor `Calculator.php`. Jeho obsah bude zatím následující:

    ```php
    <?php

    namespace App;

    class Calculator
    {

        public function sum(int $a, int $b): int
        {

        }

    }

    ```

8. Vše si commitněte:

    ```bash
    git add -A
    git commit -m "Initial commit"
    ```

Tímto máte základní aplikaci. Zatím žádné testy. Jen naprosté minimum.

## Přidáme tam testy

V projektu již máme připravený testovací framework (PHPUnit) a kostru třídy, kterou máme implementovat (Calculator). Nyní tomu přidáme testy a naimplementujme funkci.

1. Vytvořte si adresář `tests` a do něj předejte zaváděcí soubor, který zajistí autoloading. Pojmenujte jej `bootstrap.php` a jeho obsah bude:

    ```php
    <?php

    require_once __DIR__ . '/../vendor/autoload.php';

    ```

    Do tohoto souboru můžete později přidat další kód který by Vaše testovaná aplikace vyžadovala (třeba vypnutí cache apod.).

2. V rootu aplikace si vytvořte soubor `phpunit.xml`. To je konfigurační soubor pro testovací framework. Zde nadefinujeme cestu k testům a našemu bootstrapu:

    ```xml
    <?xml version="1.0"?>
    <phpunit
            bootstrap="tests/bootstrap.php"
            verbose="true"
    >
        <!-- tests directories to run -->
        <testsuites>
            <testsuite>
                <directory suffix="Test.php">tests</directory>
            </testsuite>
        </testsuites>
    </phpunit>

    ```

3. Pak si vytvořte uvnitř složky `tests` soubor `CalculatorTest.php`. To bude právě test naší kalkulačky. Jednoduchý testovací případ může vypadat takto:

    ```php
    <?php

    namespace Tests\App;

    use PHPUnit\Framework\TestCase;

    class CalculatorTest extends TestCase
    {

        public function testSum()
        {
            $calculator = new Calculator();
            $this->assertSame(0, $calculator->sum(0, 0));
            $this->assertSame(99, $calculator->sum(50, 49));
            $this->assertSame(-99, $calculator->sum(-100, 1));
        }

    }

    ```

    Do této složky můžete psát další a další testy. Podmínkou je pouze to, aby názvy souborů končily na `Test.php` - jak jsme si nastavili v konfiguračním souboru.

4. Spusťte si testy. Lze to udělat [jednoduše v IDE](https://www.jetbrains.com/help/phpstorm/2016.3/phpunit.html) nebo přes příkazový řádek:

    ```bash
    php vendor/bin/phpunit
    ```

    Není nutné nic více nastavovat, jelikož si PHPUnit sám načte konfiguraci definovanou uvnitř `phpunit.xml` v rootu aplikace.

    Testy skončí chybou, jelikož funkce ještě není naimplementována.

5. Naimplementujte kalkulačku. Soubor `src/Calculator.php` může vypadat takto:

    ```php
    <?php

    namespace App;

    class Calculator
    {

        public function sum(int $a, int $b): int
        {
            return $a + $b;
        }

    }

    ```

## Commit

A je to hotovo. Mám vytvořenou kostru aplikace, kde mám Composer a prostředí pro testy.

To je vše co jsem chtěl ukázat. Nyní byste již neměli mít problém založit nový projekt a rovnou u něj psát testy.

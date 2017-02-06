---
layout: post
title: "Kostra testované aplikace"
perex: "Stále tápete, jak vytvořit základní kostru aplikace kde je Composer vč. autoloaderu a máte i testy? Podíváme se na to, že to je velmi jednoduché."
author: 16
---

## Co budeme dělat?

Vytvoříme se primitivní kalkulačku (která umí jen sčítat). A k tomu si napíšeme test, jestli kalkulačka sčítá správně.

Povedu Vás krok po kroku. Článek je určen **pro začátečníky**. Předpokládám ale, že:

* máte nainstalované [PHP 7](http://php.net/) (třeba s [XAMPP](https://www.apachefriends.org/download.html)), [Composer](https://getcomposer.org/), nějaké IDE (třeba [PhpStorm](https://www.jetbrains.com/phpstorm/)), [Git](https://git-scm.com/) a umíte to vše, alespoň trochu, použít.

Pokud s něčím budete mít problémy, podělte se v diskuzi dole pod článkem. Rádi poradíme.

## První krůčky - založení projektu

Začnu od píky. A provedu Vás po bodech.

1. Vytvořte si adresář pro web. Na Linuxu třeba takto:

    ```
    cd /var/www
    mkdir test-project
    cd test-project
    ```

1. Uvnitř nového adresáře inicializujeme GIT:

    ```
    git init
    ```

1. Otevřu si adresář v mém oblíbeném IDE.

1. Vytvořím si nový `.gitignore` soubor a zakážu verzování `vendor` (právě ten adresář si bude spravovat Composer)

    ```
    vendor
    
    ```

    Jestliže zastáváte názor, že se i vendor verzuje, klidně si jej zaverzujte. V tom případě tenhle krok zkrátka ignorujte.

1. Přidám si `composer.json`, vyžádám si [PHPUnit](https://phpunit.de/) abych mohl psát testy a nastavím autoloader aby se mi samy načítaly používané třídy:

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
            "phpunit/phpunit": "^5.7"
        },
        "autoload": {
            "psr-4": {
                "App\\": "src"
            }
        }
    }
    
    ```

1. Nainstaluji si závislosti spuštěním `composer install`.

1. Nakonec si vytvořím adresář `src` a v něm soubor `Calculator.php`. Jeho obsah bude zatím následující:

    ```php
    <?php
    
    namespace App;
    
    class Calculator
    {
    
        public function sum(float $a, float $b): float
        {
    
        }
    
    }
    
    ```

1. Vše si commitnu:

    ```
    git add -A
    git commit -m "Initial commit"
    ```

Tím bych měl základní aplikaci. Zatím žádné testy. Jen naprosté minimum.

## Přidáme tam testy

V projektu již máme připravený testovací framework (PHPUnit) a kostru třídy, kterou máme implementovat (Calculator). Nyní tomu přidejme testy a naimplementujme funkci.

1. Vytvořím si adresář `tests` a do něj přidám zaváděcí soubor, který mi zajistí autoloading. Pojmenuji si jej `bootstrap.php` a jeho obsah bude:
    
    ```php
    <?php
    
    require_once __DIR__ . '/../vendor/autoload.php';
    
    ```

1. Pak si vytvořím uvnitř složky `tests` soubor `CalculatorTest.php`. To bude právě test naší kalkulačky. Jednoduchý testovací případ může vypadat takto:
    
    ```php
    <?php
    
    namespace Tests\App;
    
    use PHPUnit\Framework\TestCase;
    
    class CalculatorTest extends TestCase
    {
    
        public function testSum()
        {
            $calculator = new Calculator();
            $this->assertSame(0.0, $calculator->sum(0.0, 0.0));
            $this->assertSame(99.99, $calculator->sum(50.50, 49.49));
            $this->assertSame(-99.99, $calculator->sum(-100.99, 1.0));
        }
    
    }
    
    ```

1. Spustím si testy. Lze to udělat [jednoduše v IDE](https://www.jetbrains.com/help/phpstorm/2016.3/phpunit.html) nebo přes příkazový řádek:

    ```
    php vendor/bin/phpunit --bootstrap tests/bootstrap.php tests

    ```

    Testy skončí chybou, jelikož funkce ještě není naimplementována.

1. Naimplementujeme naší kalkulačku. Soubor `src/Calculator.php` může vypadat takto:

    ```php
    <?php
    
    namespace App;
    
    class Calculator
    {
    
        public function sum(float $a, float $b): float
        {
            return $a + $b;
        }
    
    }
    
    ```

## Commit

A je to hotovo. Mám vytvořenou kostru aplikace kde mám Composer a prostředí pro testy.

To je vše co jsem chtěl ukázat. Nyní by jste již neměli mít problém založit nový projekt a rovnou u něj psát testy.

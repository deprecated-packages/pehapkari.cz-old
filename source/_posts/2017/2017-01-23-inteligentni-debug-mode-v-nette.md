---
id: 15
title: "Inteligentní debug mode v Nette"
perex: "Jak se poprat se zapínáním debug módu při vývoji a jeho vypnutím na produkci? A co debug mód v konzoli? Pojďme se podívat, jak to řešit lépe."
author: 12
tweet: "Urodilo se na blogu: Inteligentní debug mode v #NetteFw"
---

## Jak se to běžně dělává?

### 1. Dočasná úprava bootstrap.php

Často jsem se setkal, že programátoři při vývoji zapínají debug mód přímo
v `bootstrap.php` prostým zavoláním:
```php
$configurator->setDebugMode(TRUE);
```

**Výhody:** Je to jistě nejjednodušší řešení a debug mód funguje všude, včetně konzole.

**Nevýhody:** Snadno se omylem commitne zapnutý debug do produkce.

### 2. Výčet IP adres

Do metody `setDebugMode()` jde také předat seznam IP adres, pro které se debug mód zapne:

```php
$configurator->setDebugMode(['1.1.1.1', '2.2.2.2']);
```

Jak ale potom zapnout debug mód pro konzoli? Tam přece IP adresu nemáme. Lze to přidáním hostname
našeho stroje do seznamu IP:

```php
$configurator->setDebugMode(['1.1.1.1', '2.2.2.2', 'myhostname']);
```

**Výhody:** Nenasadíte debug na produkci nechtěným commitem.

**Nevýhody:** Pokud pracujete na více strojích nebo v týmu, tento výčet může snadno narůst a ztratíte
v něm přehled. Může se také často měnit a tak se stále dokola upravuje kód kvůli debug módu. Nelze
použít, pokud máte sdílenou IP adresu. Musíte udržovat výčet hostname vývojových strojů, aby fungoval
debug i v konzoli.

### 3. Prázdný výčet

Debug lze povolit i s prázným výčtem:

```php
$configurator->setDebugMode([]);
```

Nette pak do něj automaticky přidá IP adresy `127.0.0.1` a `::1`

**Výhody:** Nenasadíte debug na produkci nechtěným commitem.

**Nevýhody:** Musíte mít vývojové prostředí nastaveno tak, že se hostname aplikace vždy přeloží na jednu
z daných IP.

### 4. Nastavení cokie

Debug mód Nette zapne i při přítomnosti určité cookie:

```php
// Nasetování cookie
setcookie('nette-debug', 'mysecret', strtotime('1 years'), '/', '', '', TRUE);

// Zapnutí debugu podle cookie a IP
$configurator->setDebugMode('mysecret@1.1.1.1');
```

**Výhody:** Lze použít, i když máte sdílenou IP adresu. Lze zapnout debug mód trvale pro vývojový stroj
a podmíněně na produkci.

**Nevýhody:** Musíte mít v aplikaci zabezpečené místo, které vám cookie nasetuje. Krom sdílené IP stále
stejné nevýhody jako postup 2.

## Pozdravte proměnné prostředí

Proměnné prostředí, neboli environmental variables jsou definovány operačním systémem. Můžeme si ale také
přidat vlastní a jimi zapínání debug módu řídit. Jak to udělat? Zapíšeme do `bootstrap.php` následující kód:

```php
if (getenv('NETTE_DEVEL') === '1') {
    $configurator->setDebugMode(TRUE);
}
```

Tím zajistíme, že při přítomnosti proměnné prostředí `NETTE_DEVEL` a jejím nastavení na hodnotu `1` se nám
debug mód zapne vždy. Aby to fungovalo však musíme tuto proměnnou někde nastavit. Jak to udělat se
liší podle toho, jak máme PHP nainstalované.

### PHP jako FPM

Pro PHP-FPM proměnnou nejlépe nastavíme v konfiguraci poolu. V mém případě mám konfiguraci hlavního poolu
v souboru `/etc/php/7.0/fpm/pool.d/www.conf`. Nastavení proměnné pak vypadá takto:

```ini
env[NETTE_DEVEL] = 1
```

Po nastavení nezapomeňte PHP-FPM restartovat.

### PHP jako modul Apache

Pokud máte PHP jako modul webserveru Apache, nejlépe proměnnou nastavíte ve VirtualHostu aplikace:

```bash
<VirtualHost *:80>

    # ...

    SetEnv NETTE_DEVEL 1

    # ...

</VirtualHost>
```

Před restartem Apache si zkontrolujte, zda máte povolen modul `mod_env`.

#### PHP z konzole

Pokud používáte bash, nejvíce se mi osvědčilo nastavit proměnnou v souboru `~/.bashrc` takto:

```bash
export NETTE_DEVEL=1
```

Nezapomeňte, že se proměnná nastaví až při novém načtení. Buďto tedy zavřete a znovu otevřete shell,
nebo spusťte příkaz `source ~/.bashrc`.

Nyní vám poběží debug mód i při spouštění konzolových příkazů. Navíc jej ale sndno zapnete i na prodkčním
serveru, pokud potřebujete rychle zjistit, proč nějaký konzolový příkaz neběží:

```bash
$ NETTE_DEVEL=1 php /cesta/k/nette/index.php
```

Pro logičtější spouštění konzole si pak v aplikaci vždy vytvořím ve složce `bin` soubor `console` a
nastavím mu práva ke spuštění:

```php
#!/usr/bin/env php
<?php

$container = require __DIR__ . '/../app/bootstrap.php';
$container->getByType(Nette\Application\Application::class)->run();
```

### Proč právě takto?

* Nemusím hlídat dočasné úpravy v `bootstrap.php`
* Nemusím řešit seznamy IP adres a hostnames, pro které se debug mód zapne
* Na dev stroji mám debug mód vždy zaplý pro všechny své aplikace a to i pro konzoli
* Na ostrém serveru je debug mód vždy vyplý, ale jde jej jednoduše dočasně zapnout bez zásahu do kódu
* Na ostrém serveru mohu jednoduše spuštět konzolové příkazy v debug módu opět bez úprav kódu

### Nevýhody?

* Nelze zapnout debug mód na produkci jen pro jediného uživatele webu
* Musíte proměnnou `NETTE_DEBUG` nastavit v konfiguračních souborech webserveru
a shellu.

Tohle jistě není jediný, ani ten nejlepší přístup, jak se s tímto poprat. Já ho však používám již několik
let a plně mi vyhovuje. Jak to ve svých aplikacích řešíte vy?

---
id: 53
layout: post
title: "Debugování CLI skriptů s Xdebugem"
perex: |
    Debugování je každodenním chlebem programátorů. Ukáži vám, jak rychle a jednoduše se dají debugovat konzolové skripty s Xdebugem.

    Už žádné `print()` a `echo` v konzolových commandech pro zjištění hodnoty proměnné  :-).
author: 32
lang: cs
tweet: "Urodilo se na blogu: Debugování CLI skriptů s #Xdebug"
---

Nezáleží na tom, jestli používáš `Symfony\Console` nebo pouštíš běžný PHP skript, většinou to děláš nějak takto:
```bash
php script.php
# nebo
bin/console myCommand
```

Znáš to, ve tvém kódu se něco rozbilo, děje se tam černá magie a ty potřebuješ ověřit, jaká je hodnota proměnné, kolik iterací se provede atd.. Není nic jednoduššího, než si vypsat potřebné informace a pak ukončit běh programu.

Představ si situaci s následujícím kódem, kdy potřebuješ zjistit, jaká je hodnota `$c` a že to doopravdy je `9765625`.
```php
$a = 5;
$b = 10;
$c = $a ** $b;
```

<div class="text-center">
    <img src="/assets/images/posts/2017/xdebug-cli-scripts/debug-cli-script-without-xdebug.gif">
</div>

`echo $variable; die();` je něco co vídám velice často, je to rychlé a splní to účel.

Ale co když chci vypsat více proměnných a v kódu se chci posouvat nebo jej zastavit na více místech? Jak to dělat lépe?

## Xdebug na pomoc

<small>*Následující řádky předpokládají nainstalovanou a [aktivní xdebug extension](https://stackoverflow.com/a/14046603) pro vaše PHP. Nejrychlejší kontrola je přes příkaz `php -v|grep 'Xdebug'`.*</small>

Postup je opět velice jednoduchý:
1. Zapnu naslouchání IDE pro příchozí spojení `Run → Listen for debug connections`
2. Umístím **breakpoint** na místo, kde chci běh skriptu zastavit
3. Spustím skript se zapnutým xdebugem & Profit

Skript s xdebugem pustíme takto:
```bash
php -d xdebug.remote_autostart=on -d xdebug.remote_enable=1 script.php myCommand
# nebo
php -d xdebug.remote_autostart=on -d xdebug.remote_enable=1 bin/console myCommand
```

<div class="text-center">
    <img src="/assets/images/posts/2017/xdebug-cli-scripts/debug-cli-script-with-xdebug.gif">
</div>

## Alias jako příjemný bonus

Super, ale komu se chce stále dokola psát `php -d xdebug.remote_autostart=on -d xdebug.remote_enable=1` a hlavně, kdo si to má pamatovat?

Zde přichází na pomoc **alias**, mě osobně vyhovuje `phpx`, ale fantazii se meze nekladou:
```
alias phpx='php -d xdebug.remote_autostart=on -d xdebug.remote_enable=1'
```

S touto parádou je použití následující:
```bash
phpx script.php
# nebo
phpx bin/console myCommand
```

Aby byla změna permanentní, je potřeba řádek s aliasem přidat do [bash_profile](https://www.quora.com/What-is-bash_profile-and-what-is-its-use), cesta k souboru se liší dle OS a příkazové řádky, nejčastěji se jedná o `~/.bash_profile`, `~/.bashrc`  nebo `~/.zshrc`.

*Přeji šťastné a veselé debugování CLI skriptů!*

## Chci se dozvědět více!
Přihlaš se na školení [Začněte debugovat jako profíci s xdebugem](https://pehapkari.cz/kurz/zacenete-debugovat-jako-profici-s-xdebugem/), kde probereme téma více do hloubky a ukážeme si pokročilé tipy!

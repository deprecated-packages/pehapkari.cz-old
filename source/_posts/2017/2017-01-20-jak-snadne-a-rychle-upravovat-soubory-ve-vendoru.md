---
layout: post
title: "Jak snadno a rychle upravovat soubory ve vendoru?"
perex: "Už si se někdy dostal do situace, kdy jsi potřeboval opravit chybu nějaké závislosti ve složce vendor? Jak takovou úpravu sdílet v týmu s ostatními programátory a jak ji udržet v souboru i po spuštění composeru? V tomto článku se dovíš, jak snadně a elegantně se tento problém dá vyřešit během 1 minuty."
author: 11
---

## Chyba je ve vendoru...

Občas se může stát, že aplikace po spuštění `composer update` začne vyhazovat notice, warning nebo dokonce fatal error. Co se stalo? Kde je chyba? Je v mojí aplikaci nebo někde jinde... Po pár minutách až hodinách :) zjistíš, že **chyba není v aplikaci, ale v balíčku**, který se ti právě aktualizoval. 

Jak je možné, že někdo otaguje balíček, který obsahuje takovou chybu? Každy z nás je pouze člověk a i sebelepší programátor se semtam sekne a vytvoří bug, ať už spravuje svůj osobní nebo celosvětově používaný balíček. 

Mně se například po přechodu na PHP7 stalo to, že [Doctrine\DBAL](https://github.com/doctrine/dbal) špatně bindoval parametry do dotazů viz. [OCI8 - bindValue overwrite previous values issue](https://github.com/doctrine/dbal/issues/2261). Takže chybu jsme už našli co dál?


## Jak chybu opravit?

### Udělám vlastní fork
Tak to je přeci jednoduché! Pošlu **pull-request s opravou** a počkám až to autor spojí. To ale může trvat dny i měsíce a tag v nedohlednu. Mezitím moje **aplikace nepojede**? Dobře, půjdu na to chytřeji... 

Pošlu pull-request a ve své aplikaci nasměruju composer na **svoji forknutou verzi** balíčku a je hotovo. OK, ale než se můj pull-request spojí, tak si musím fork udržovat aktuální... 

### Upravím si soubor lokálně
Co to tedy udělat trochu na prasáčka? Otevřu si soubor ve vendoru a **opravím si to sám** a bude - ehm počkat... Složku vendor si automaticky vytváří a spravuje [Composer](https://getcomposer.org/) nepřepíše se mi tedy upravený soubor? Přepíše, ale pouze při vydání nové verze balíčku - bezva! Nové verze balíčku nevychází tak často a až vyjde, tak už to bude třeba opravené. 

V tento moment mám vyhráno! Soubor jsem si upravil u sebe - aplikace jede a autorovi balíčku jsem poslal pull-request s opravou. Je čas slavit! Nebo ne?


### Stačí tohle řešení?

To záleží na pár otázkách: 
 - **Pracuji v týmů** a je tedy možné, že stejnou chybu bude mít i kolega?
 - Nahrávám aplikaci **na server bez vendoru**, který se následně vytvoří přes `composer install`?

Pokud si alespoň na jednu otázku odpovím ano, tak mám opět problém. Společným problémem pro obě otázky je to, že se do vendoru dostane opět ten zabugovaný soubor. Psát kolegům co a kde si mají upravit, aby aplikace fungovala, je velmi nespolehlivé. A jak řešit ten problém na serveru? Oslava se musí odložit... Co s tím?


## cweagans/composer-patches

Naštěstí existuje balíček, který za tebe **vyřeší všechny problémy**, na které jsi zde narazil! [cweagans/composer-patches](https://github.com/cweagans/composer-patches) je balíček, který obsahuje nástroje pro patchování souborů (co je to [patch](https://cs.wikipedia.org/wiki/Patch)?). Zároveň je natolik chytrý, že poslouchá Composer a při instalaci/aktualizaci balíčku dokáže určit, zda pro daný balíček existuje patch a zda ho má aplikovat nebo ho už aplikoval. 

Jak je to možné? Composer při instalaci balíčků vyvolává události, na které `cweagans/composer-patches` poslouchá a podle toho reaguje (jak fungují [události](http://pehapkari.cz/blog/2016/12/05/symfony-event-dispatcher/)?). 

Dost teorie - jdeme opravit chybu!


## Oprava chyby ve 4 krocích

### 1. Nainstalování cweagans/composer-patches

Nainstalujeme balíček `cweagans/composer-patches`.

`composer require cweagans/composer-patches`

### 2. Vytvoření patch souboru

Ve vendor složce si najdeš zabugovaný soubor a zkopíruješ ho do toho samého adresáře pouze s jiným názvem souboru (já používám suffix "-fixed" např. `bugged-file-fixed.php`). Následně si zkopírovaný soubor otevřeš a opravíš v něm co potřebuješ. Pak už jen zbývá spustit v CLI příkaz pro vygenerování patch souboru:
 
```bash
# diff -u ./vendor/package-name/path/to/bugged/file/BuggedFile.php ./vendor/path/to/bugged/file/BuggedFile-fixed.php > patches/bugged-file.patch
```

Pokud ti CLI napíše, že příkaz `diff` nebyl nalezen, tak ho bude potřeba doinstalovat viz. postupy níže. A pokud ho máš, můžeš [přeskočit sem](#3-Úprava-patch-souboru-pro-cweaganscomposer-patches).

#### Linux

Je potřeba mít dostupné příkazy `diff` a `patch` (dočteš se dál). Oba jsou již v Linuxu dostupné po instalaci, takže na Linuxu jsi v pohodě. :) Pokud je tam náhodou nemáš, tak trochu pogůgli a doinstaluj si je podle své Linuxové distribuce.

#### Windows

Zde budeš potřebovat nainstalovat příkazy `diff` a `patch` (dočteš se dál). Pro jejich instalaci si stačí nainstalovat [Cygwin](http://cygwin.org/), který portuje základní příkazy z Linuxu do Windows. Součástí instalace je stažení instalačních souborů pro jednotlivé příkazy, takže v průběhu instalace budeš vyzván k volbě mirroru pro stažení dat.
 - stáhni [http://cygwin.org/setup-x86.exe](http://cygwin.org/setup-x86.exe)
 - spusť instalaci a pokračuj příkazem "Next"
 - zvol libovolný mirror (třeba hned ten první - http://cygwin.mirror.constant.com)
 - v tabulce "Select Packages" vyhledej slovo "patch" (mělo by se ti zobrazit cca 7 rozkliknutelných položek)
 - vyber "Devel", "Perl", "Text" a "Utils" a zaškrtni jednotlivé subpoložky
 - dokonči instalaci 

Nyní je třeba zaregistrovat cestu k cygwinu do Path. V promměnném prostředí tedy přidáš do Path cestu k bin složce ("C:\cygwin\bin" - výchozí nastavení).


#### Mac

I zde budeš potřebovat příkazy `diff` a `patch` (dočteš se dál). Podle google by jsi měl mít příkazy již součástí systému, pokud ne, tak trochu pogůgli a doinstaluj si je podle své verze.


### 3. Úprava patch souboru pro cweagans/composer-patches

Otevři si vygenerovaný patch soubor a uprav hlavičku.

Před:

```text
--- ./vendor/package-name/path/to/bugged/file/BuggedFile.php 2016-12-16 18:50:47.642172308 +0100
+++ ./vendor/package-name/path/to/bugged/file/BuggedFile-fixed.php 2017-01-13 11:42:07.000000000 +0100
```

Po:

```text
--- /dev/null
+++ path/to/bugged/file/BuggedFile.php
```

zbytek nech tak jak je. Všimni si, že cesta k souboru **musí být uvedena relativně** ke složce ve vendoru, která obsahuje balíček.


### 4. Nastavení cesty k patch souboru pro cweagans/composer-patches

`cweagans/composer-patches` se konfiguruje přes soubor `composer.json`, takže do něj přidáme sekci `patches`:

```json
"extra": {
    "patches": {
        "bugged/package": {
            "Patch message": "patches/bugged-file.patch"
        }
    }
}
```

 - `bugged/package` je klasický název balíčku např. `nette/di`, `symfony/console` apod., na který chceme patch aplikovat
 - `Patch message` je zpráva, která se vypíše v CLI po aplikování patche.
 - `patches/bugged-file.patch` je relativní cesta k patch souboru.
 
Toto je základní konfigurace pro lokální patch soubory, ale `cweagans/composer-patches` podporuje celou řadu dalších možností, které najdeš v [readme](https://github.com/cweagans/composer-patches/blob/master/README.md).
 
### Test

Spustíš příkaz `composer install` nebo `composer update` a ve výpisu z composeru uvidíš text:

```text
 - Installing bugged/package
    Loading from cache

  - Applying patches for bugged/package
    patches/bugged-file.patch (Patch message)
```

V tuto chvíli máš upravený soubor ve vendor složce. `cweagans/composer-patches` ti na pozadí provedl příkaz `patch`, který aplikuje vygenerovaný patch. Aplikace jede - jak u tebe, tak u kolegů a i na serveru. **Je čas slavit!**


## Shrnutí

V tomto článku jsi našel nástroj, kterým snadně a rychle řešit buggy ve vendoru a zároveň cestu, kterou můžeš opravy sdílet dál (mezi kolegy, na server apod.). 

Zde je shrnutí v bodech, jak postupovat:
 
1. `composer require cweagans/composer-patches`
2. Zkopírovat soubor s buggem a opravit ho
3. Vytvořit patch soubor (příkaz `diff`)
4. Upravit vygenerovaný soubor (opravit hlavičku)
5. Přidat cestu k patch souboru do composer.json
6. Spustit `composer install` nebo `composer update`
7. Profit!
 
## Chci se dozvědět více!

Zde jsou materiály, které ti pomohou pochopit, jak takový nástroj funguje a jak ho můžeš použít.
 
 - https://github.com/cweagans/composer-patches
 - https://getcomposer.org/doc/articles/scripts.md

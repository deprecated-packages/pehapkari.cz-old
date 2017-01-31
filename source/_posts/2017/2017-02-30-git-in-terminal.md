---
layout: post
title: "Git - proč se jej učit v příkazové řádce"
perex: "Tento článek je určen pro začátečníky, kteří se chystají s učením verzovacího systému Git. Popisuji proč se vyplatí používat Git v příkazové řádce namísto v grafickém prostředí externích SW či IDE."
author: 101010101
---

Ještě před tím, než se programátor začne učit s Gitem vybírá / nechává náhodě jak s Gitem bude pracovat. To jak začne může ovlivnit dále jeho práci. Chci vám ukázat, že se vyplatí používat Git v příkazové řádce namísto všech ostatních GUI aplikací. Důvody jsem rozdělil do několika bodů jenž jsem seřadil dle svého subjektivního hodnocení podle důležitosti pro začátečníky.

## Dokumentace, návody
Co se týče [dokumentace](https://git-scm.com/doc) samotné, lze už z principu předpokládat, že je psána v základní podobě jenž Git nabízí a tedy v příkazech CLI.
Drtivou většinu toho, co jsem kdy četl ke Gitu a obsahovalo postupy, tak bylo psáno formou CLI příkazů. Ale i návody, články na internetu jsou psány právě s pomocí příkazů. Učením se z příkazové řádky tedy odstraníte mezikroky, které by tvořily pokusy o aplikování příkazů v rámci vašeho GUI.

## Vyhledatelnost
Pokud hledám řešení nějakého problému, tak jej na Stackoverflow velmi často dostanu. Ovšem většina těchto řešení je zapsána v příkazech. Pokud používám denně CLI, tak není problém příkaz nejen použít, ale zároveň se z něj i něco naučit.

Ukažme si příklad. Když jsem na pracovní stanici pracoval (přenastavoval Ansible) tak se mi omylem povedlo v projektovém adresáři změnit práva k souborům. Jelikož jsem měl nezaverzované úpravy na projektu, tak jsem chtěl vyresetovat pouze nastavení práv do původního nastavení. Změny v souborech, tedy moji rozdělanou práci, jsem chtěl ponechat. Jak toto udělat v GUI? Popravdě vůbec netuším, odhaduji že to rychlou cestou nelze. Ale na StackOverflow jsem [řešení](http://stackoverflow.com/questions/2517339/how-to-recover-the-file-permissions-to-what-git-thinks-the-file-should-be) našel ihned. A samozřejmě bylo napsáno formou příkazu:

```
git diff -p -R --no-color \
    | grep -E "^(diff|(old|new) mode)" --color=never  \
    | git apply
```

## WOW efekt
Čím dál více lidí umí a nějakou část pracovní doby s počítačem pracuje. Tudíž jsou na programy s GUI poměrně zvyklí. Ovšem práce s příkazovým řádkem bývá obdivována jako magie znalých vývojářů. Neplatí to vždy jen pro běžné lidi mimo IT. Stává se mi, že se diví i vývojáři programující řadu let.

## Rozšiřitelnost
Podívejme se na Git ve dvou různých pohledech.

Prvním z nich je spolupráce s dalšími nástroji. Může jimi být například CI server, privátní repositář hostovaný na vlastním serveru a podobně. Ke konfiguraci se hodí umět pracovat s příkazy Gitu. Například vytvoření prázdného repositáře při nastavování [pro deploy](https://www.zdrojak.cz/clanky/deploy-aplikace-pres-git/) může vypadat takto:

```
cd /var/projects/
mkdir project-name
cd project-name
git init --bare
```

Druhým pohledem je používání Gitu s ostatními nástroji příkazové řádky. Například zobrazení všech commitů jenž v commit message obsahují `fixed` a zároveň neobsahují `date` je jednoduché. Využiji toho, že v příkazové řádce je výstupem Gitu text jenž mohu dále zpracovávat pomocí Grepu.

```
git log --oneline | grep -i fixed | grep -v string
```

## Funkcionalita
CLI podporuje naprosto všechnu funkcionalitu Gitu. Oproti tomu funkcionalitu v GUI bychom mohli zjednoduše shrnout tak, že obsahuje jen to, k čemu vývojáři GUI softwaru udělali "tlačítko".

## Reprodukovatelnost
Velmi často se hodí opakovat sekvenci kroků. Může jít o to, že posíláte své řešení problému kolegovi či skriptujete na serveru. Příkazy můžete zapsat v textové podobě například do spustitelného souboru a ten poslat kolegovi do Slacku, všem programátorům do StackOverflow či třeba zaverzovat u projektu a používat na produkci.

## Rychlost
Naštěstí jsou už pryč doby kdy jsme čekali na operace v SVN (historický verzovací systém jenž je i v dnešní době v některých firmách používán). Zvykli jsme si, že některé operace v IDE trvají delší dobu. Menší rychlost při používání IDE vyvažuje jejich skvělá použitelnost a pomoc při psaní kódu oproti běžným textovým editorům. Ovšem vyšší přínos používání Gitu v GUI nevidím. Proto preferuji Git v příkazové řádce kde je o mnoho rychlejší.

## Univerzálnost
Práce v CLI je stejná v každém OS, tak zároveň v každé technologii. Je jedno jestli právě programujete aplikační kód v PhpStormu, řešíte PL/pgSQL procedury v DataGripu nebo si připravujete diplomovou práci v LaTeXu. Zaverzujete cokoliv je potřeba.

----

A abyste se nemysleli, že jsem na GUI úplně zanevřel, tak existuje use-case kde mi dává GUI smysl. Jsou jím diffy. Zkoumání rozdílů v GUI, minimálně pro začátek, je o mnoho přehlednější.

Abych byl spravedlivý, musím zmínit zápor použití práce s Gitem v příkazové řádce. Je jím určitá složitost a nekonzistence CLI rozhraní. To způsobuje minimálně ze začátku nepříjemné stavy, kdy člověk používá jeden a ten samý příkaz na několik různých činností.

Kladných důvodů bylo uvedeno mnoho. Napadá vás další? Nebo máte přesvědčivé argumenty pro nepoužívání Gitu v CLI? Napište do diskuze pod článkem!

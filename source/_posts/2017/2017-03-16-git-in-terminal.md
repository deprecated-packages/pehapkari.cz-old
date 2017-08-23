---
id: 27
layout: post
title: "Git - proč se jej učit v příkazové řádce"
perex: "Tento článek je určen pro začátečníky, kteří se chystají začít učit verzovací systém Git. Popisuje proč se vyplatí používat Git v příkazové řádce namísto v grafickém prostředí externích SW či IDE."
author: 19
reviewed_by: [1]
---

Ještě před tím, než se programátor začne učit s Gitem, vybírá či nechává náhodě volbu jak s Gitem bude pracovat. To, jak začne, může ovlivnit dále jeho práci. Chci vám ukázat, že se vyplatí používat Git v příkazové řádce namísto všech ostatních GUI aplikací. Důvody jsem rozdělil do několika bodů seřazených podle důležitosti pro začátečníky.

## Dokumentace i návody jsou psané pro příkazovou řádku

Co se týče [dokumentace](https://git-scm.com/doc) samotné, lze už z principu předpokládat, že je psána v základní podobě, jíž Git nabízí, tedy v příkazech pro CLI. Návody a články na internetu jsou převážně psány stejně. Učením se z příkazové řádky tedy odstraníte mezikroky, které by tvořily pokusy o aplikování příkazů v rámci vašeho GUI.

## Vyhledatelnost řešení

Pokud hledám řešení nějakého problému, tak jej na [Stack Overflow](http://stackoverflow.com/) velmi často dostanu. Ovšem většina těchto řešení je zapsána v příkazech. Pokud používám denně CLI, tak není problém příkaz nejen použít, ale zároveň se z něj i něco naučit.

### Ukažme si příklad

Při práci na pracovní stanici (přenastavoval jsem Ansible) se mi omylem povedlo v projektovém adresáři změnit práva k souborům. Jelikož jsem měl nezaverzované úpravy na projektu, chtěl jsem resetovat pouze nastavení práv do původního nastavení. Změny v souborech, tedy moji rozdělanou práci, jsem chtěl ponechat. Jak toto udělat v GUI? Popravdě vůbec netuším, odhaduji, že to rychlou cestou nelze provést. Na Stack Overflow jsem [řešení](http://stackoverflow.com/questions/2517339/how-to-recover-the-file-permissions-to-what-git-thinks-the-file-should-be) našel ihned, bylo napsáno formou příkazu:

```bash
git diff -p -R --no-color \
    | grep -E "^(diff|(old|new) mode)" --color=never  \
    | git apply
```

## Ohromení okolí

Čím dál více lidí nějakou část pracovní doby s počítačem pracuje. Tudíž jsou na programy s GUI poměrně zvyklí. Ovšem práce s příkazovým řádkem bývá obdivována jako magie znalých vývojářů. Neplatí to vždy jen pro běžné lidi mimo IT. Stává se mi, že se diví i vývojáři programující řadu let.

## Rozšiřitelnost a přenositelnost

Podívejme se na Git ve dvou různých pohledech.

Prvním z nich je spolupráce s dalšími nástroji. Může jimi být například CI server, privátní repositář hostovaný na vlastním serveru a podobně. Ke konfiguraci se hodí umět pracovat s příkazy Gitu. Například vytvoření prázdného repositáře při nastavování [pro deploy](https://www.zdrojak.cz/clanky/deploy-aplikace-pres-git/) může vypadat takto:

```bash
cd /var/projects/
mkdir project-name
cd project-name
git init --bare
```

Druhým pohledem je používání Gitu s ostatními nástroji příkazové řádky. Například: jak rychle zjistit, kolik commitů řeší fixování chyb? Předpokládejme, že commit opravující chybu má v názvu "fix". Využijeme příkazu [wc](https://cs.wikipedia.org/wiki/Wc_(Unix)), jenž umí spočítat počet řádků.

```bash
git log --oneline | wc -l  # počet všech commitů
git log --oneline | grep -i "fix" | wc -l  # počet commitů řešících opravy chyb
```

## Funkcionalita

CLI podporuje naprosto všechnu funkcionalitu Gitu. Oproti tomu funkcionalitu v GUI bychom mohli zjednoduše shrnout tak, že obsahuje jen to, k čemu vývojáři GUI softwaru udělali "tlačítko". Tudíž nejsem limitován funkcionalitou a vše co přečtu v článcích o Gitu mohu použít.

## Reprodukovatelnost

Velmi často se hodí opakovat sekvenci kroků. Může jít o to, že posíláte své řešení problému kolegovi či skriptujete na serveru. Příkazy můžete zapsat v textové podobě například do spustitelného souboru a ten poslat kolegovi do Slacku, všem programátorům do Stack Overflow či třeba zaverzovat u projektu a používat na produkci.

## Univerzálnost

Práce v CLI je stejná v každém OS, ale i v každé technologii. Je jedno jestli právě programujete aplikační kód v PhpStormu, řešíte PL/pgSQL procedury v DataGripu nebo si připravujete diplomovou práci v LaTeXu. Zaverzujete cokoliv je potřeba.

## Kde bych GUI použil?

A abyste si nemysleli, že jsem na GUI úplně zanevřel, existuje use-case, kde mi dává GUI smysl. Jsou jím diffy. Zkoumání rozdílů v GUI, minimálně pro začátek, je o mnoho přehlednější.

Nejčastěji používáným GUI právě pro zkoumání rozdílů jsou služby typu GitHub, BitBucket a GitLab. Jejich výhodou je jejich webové rozhraní a tudíž možné rychlé vyzkoušení. Podívejte se na [ukázku](https://github.com/pehapkari/pehapkari.cz/commit/3ff82cc7eabe4e96bb54a92858e21d0f1af9f8fb?diff=split) z webu pehapkari.cz.

## Zápory použití příkazové řádky

Abych byl spravedlivý, musím zmínit zápor práce s Gitem v příkazové řádce. Je jím určitá složitost a nekonzistence CLI rozhraní. To způsobuje minimálně ze začátku nepříjemné stavy, kdy člověk používá jeden a ten samý příkaz na několik různých činností. Například použití příkazů `checkout` a `reset`.

### Příklad složitějšího rozhraní

Nekonzistence rozhraní je kupříkladu vidět při mazání větví oproti smazání sledovaných repositářů (například vzdálené repositáře na GitHubu). Podívejte se na ukázku:

```bash
git branch -d nazev-vetve  # odstraní lokální začleněnou větev
git branch -D nazev-vetve  # odstraní lokální jakoukoliv větev
git remote remove origin   # odstraní sledování repositáře
```

Kladných důvodů bylo uvedeno mnoho. Napadá vás další? Nebo máte přesvědčivé argumenty pro nepoužívání Gitu v CLI? Napište do diskuze pod článkem!

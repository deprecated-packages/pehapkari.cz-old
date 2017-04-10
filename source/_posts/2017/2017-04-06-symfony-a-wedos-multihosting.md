---
layout: post
title: "Rozběhnutí symfony na Wedos multihostingu"
perex: "Spuštění webu postaveném na symfony na wedos multihostingu vypadalo na první pohled jako snadná věc. Nakonec jsem se na tom zasekl na 3 dny než jsem přišel jak hosting správně nastavit.
Aby se s tím nemusel trápit někdo další, tak jsem se rozhodl sepsat tento článek s návodem a s problémy na které jsem narazil. Finální řešení je nakonec jednoduché."
author: 21
---

## Hosting a jeho struktura

Jedná se o klasický NoLimit webhosting s neomezeným počtem aliasů. Provozuji tedy na něm několik webů a tomu odpovídá jeho adresářová struktura.

```
* logs
* session
* tmp
* www
    * domains
        * domena1.cz
        * domena2.cz
        * symfonyprojekt.cz
            * app
            * .
            * .
            * web
                * app.php
                .htaccess            
    * subdom
    * .htaccess
```

## Logování

Největší problém byl, že v základu není přístup k žádným logům a když selže přesměrování, tak není z čeho zjistit co se nepovedlo. Errorlog je příplatková služba a vyjde na 25 Kč měsíčně. Jednou za 30 dní je možné logování zapnout na 24 hod.


## Postup rozběhnutí projektu

Zde popíšu jednotlivé kroky co je potřeba udělat, aby se webové stránky načetly. Celé nastavení je o správném .htaccess a na většinu věcí jsem přišel pokus omyl.

### Hlavní wedos .htaccess

Wedos má návod pro nastavení multihosingu (https://kb.wedos.com/cs/webhosting/samostatne-weby-aliasy.html). Díky tomuto návodu existuje ve složce www hlavní .htaccess, který slouží pro směrování podle domén. Tento .htacces může zůstat tak jak je.

### .htaccess v root složce symfony

Wedosí .htaccess přesměrovává do root složky (symfonyprojekt.cz) kde hledá index. U symfony ho zde nenajde, protože je až ve složce web. Proto je potřeba sem do root složky přidat nový .htaccess s obsahem, který se postará o další přesměrování.

```
RewriteEngine On
RewriteRule (.*) web/$1 [L]
```

### Symfony .htaccess

Symfony má ve složce web svůj vlastní .htaccess. Ten je nutné pro Wedos upravit a to tak, že se zakomentuje nebo odstraní následující část:

```
<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>
```

Je to z důvodu, že wedos nepodporuje mod_negotiation. Nejsem si jistý co přesně se smazaním ovlivní, ale v mém případě jsem na žádný problém nenarazil.

## To je vše

Tahle kombinace nastavení je na mém webu funčkní. Určitě existuje více variant nastavení, ale tohle byla moje první funkční verze a zatím jsem nenarazil na žádný problém.
Doufám, že tento stručný návod alespoň někomu pomůže a nebude se s tím trápit jako já.

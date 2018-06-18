---
id: 32
title: "Rozběhnutí Symfony na Wedos multihostingu"
perex: |
    Spuštění webu postaveném na Symfony na Wedos multihostingu vypadalo na první pohled jako snadná věc. Nakonec jsem se na tom zasekl na 3 dny, než jsem přišel na to, jak hosting správně nastavit.<br>
    Aby se s tím nemusel trápit někdo další, tak jsem se rozhodl sepsat tento článek s návodem a s problémy na které jsem narazil. Finální řešení je nakonec jednoduché.
author: 22
tweet: "Urodilo se na blogu: #Rozběhnutí Symfony na Wedos multihostingu"
---

## Hosting a jeho struktura

Jedná se o klasický [NoLimit webhosting](https://hosting.wedos.com/cs/webhosting.html) s příplatkovou službou [neomezený počet aliasů](https://hosting.wedos.com/cs/webhosting/neomezeny-pocet-aliasu.html?lsm=1). Na něm lze provozovat několik webů čemuž odpovídá jeho adresářová struktura.

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

Největší problém byl, že v základu není přístup k žádným logům, a když selže přesměrování, tak není z čeho zjistit co se nepovedlo. Errorlog je příplatková služba a vyjde na 30 Kč měsíčně. Jednou za 30 dní je možné logování zapnout na 24 hod.


## Postup rozběhnutí projektu

Zde popíšu jednotlivé kroky, které je potřeba udělat, aby se webové stránky načetly. Celé nastavení je o správném `.htaccess` a na většinu věcí jsem přišel pokus-omyl.

### Hlavní Wedos .htaccess

Wedos má návod pro nastavení multihostingu ([návod pro nastavení multihosingu](https://kb.wedos.com/cs/webhosting/samostatne-weby-aliasy.html)). Díky tomuto návodu existuje ve složce `www` hlavní `.htaccess`, který slouží pro směrování podle domén. Tento `.htaccess` může zůstat tak, jak je.

### .htaccess v root složce Symfony

Wedosí `.htaccess` přesměrovává do root složky (symfonyprojekt.cz), kde hledá index. U Symfony ho zde nenajde, protože je až ve složce `web`. Proto je potřeba sem do root složky přidat nový `.htaccess` s obsahem, který se postará o další přesměrování.

```
RewriteEngine On
RewriteRule (.*) web/$1 [L]
```

### Symfony .htaccess

Symfony má ve složce web svůj vlastní `.htaccess.` Ten je nutné pro Wedos upravit, a to tak, že se zakomentuje nebo odstraní následující část:

```
<IfModule mod_negotiation.c>
    Options -MultiViews
</IfModule>
```

Je to z důvodu, že tyto řádky vypínají mod_negotiation pokud je zapnutý. Wedos, ale nepodporuje vypnutí tohoto modulu a stránka se vůbec nenačte. Čerpáno z [Spuštění Laravelu na sdíleném hostingu Wedos](http://laravelblog.cz/spusteni-laravelu-na-sdilenem-hostingu-wedos/).

## To je vše

Tahle kombinace nastavení je na mém webu funkční. Určitě existuje více variant nastavení, ale tohle byla moje první funkční verze a zatím jsem nenarazil na žádný problém.
Doufám, že tento stručný návod alespoň někomu pomůže a nebude se s tím trápit jako já.

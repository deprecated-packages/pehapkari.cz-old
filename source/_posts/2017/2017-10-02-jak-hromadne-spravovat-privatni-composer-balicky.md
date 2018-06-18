---
id: 72
title: "Jak hromadně spravovat privátní composer balíčky"
perex: |
 Composer je dobrý sluha, ale zlý pán, pokud nevíte jak s ním pracovat. Podívejte se na naše workflow vývoje, kdy je dána plně modulární aplikace a ta se řídí závislostmi na konkrétních balíčcích.
author: 27
tweet: "Urodilo se na blogu: Jak hromadně spravovat privátní composer balíčky #git #monorepo"
---
Při vývoji člověk dojde do určité fáze, kdy je potřeba projekt nějakým způsobem rozšiřovat dál, nabídnout více klientům. Tady se pak dostáváme do situace, kdy má sice funkční aplikaci, ale naráží na různé problémy. Jedním z nich může být když chce klient nějakou modifikaci. Nebo se najde nějaká chyba. To vše vede k tomu že se musí upravovat kód u každého klienta zvlášť. Řešení je jednoduché. Rozdělit kód do balíčků a ty verzovat. Klientům lze pak poskytovat konkrétní verze.

## Podívejme se na to, jak to řešíme u nás


Nejdříve je potřeba si rozdělit aplikaci na logické celky tak, aby to dávalo smysl. Mějme např. klienta „klient“ a ten bude mít závislosti v composer.json.

```json
{
  "name": "clear01/klient",
  "require": {
    "clear01/comgate": "^v1.1",
    "clear01/pohoda": "^v1.1.6",
    "clear01/component": "^v1.0.0",
    "clear01/ecommerce": "^v1.3.14",
    "clear01/zasilkovna": "^v1.0.1",
    "clear01/html-meta": "^v1.0.2"
  }
}
```

Každý jednotlivý balíček obsahuje svůj vlastní `composer.json` s případnými dalšími závislostmi. Jakmile máme balíček hotový, můžeme ho nahrát do svého privátního repozitáře (bitbucket, github). Další možností je využít nějakého [package managera](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md) ([packagist](https://packagist.com/) – placený pro privátní balíčky, [satis](https://github.com/composer/satis) – open source pro vlastní hostování). Rozhodování je jednoduché. Pokud vám nevadí placená verze, je pohodlnější packagist. Satis je sice zdarma, ale musíte si ho nainstalovat sami a řešit zabezpečení apod.

Aby vám composer načítal privátní balíčky, je potřeba přidat repozitáře / managera do configu, aby o něm composer věděl. To provedete pomocí `composer config -e` (případně přidáte `-g` pro globální config).

### Přidání lokálního repozitáře


Můžete přidat balíček z lokálního disku. Tady jen pozor, composer pak neumí pracovat s tagy, udělá to, že vytvoří symlink do vendoru, což je obzvláště výhodné při vývoji, protože není potřeba při každé změně volat `composer update`.

```json
{
  "repositories": [
    { "type": "path", "url": "/var/www/clear01/ecommerce" }
  ]
}
```

### Přidání privátního repozitáře


Tady je potřeba mít nastavený správný přístup ke git (bitbucket,...) repozitářům, zejména autentizaci tak, aby si composer správně načetl balíčky z privátních repozitářů.

```json
{
  "repositories": [
    {"type": "vcs", "url": "git@github.com:clear01/comgate.git" }
  ]
}
```

### Přidání managera balíčků


Lze přidat např. satis, což je obdoba packagist pro privátní balíčky. Ten dělá balíčky konkrétních verzí, příp. načítá automaticky nové z repozitářů a následně je servíruje klientům, což je výhodné, protože nemusíte pak na všechny klienty přidávat jednotlivé repozitáře. Jen přidáte managera a máte vystaráno.

```json
{
  "repositories": [
    { "type": "composer", "url": "https://satis.example.com" }
  ]
}
```

U vlastního managera je potřeba obvykle ještě nastavit příp. [autentizaci](https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#authentication), která je ale lepší řešit např. v `auth.json` (http-auth, apod.)

Pokud máme správně nastaveny přístupy k privátním balíčkům, musíme si ještě nastavit vývojové prostředí:

## PhpStorm


Takto připravenou aplikaci jednoduše přidáme jako projekt v PhpStormu. Ten se nás v aktuální verzi zeptá, zda chceme nastavit vývojové prostředí z `composer.json`, což nám ulehčuje práci. Toto nastaví verzi php (pokud je v `composer.json`), cesty a příp. přidá vendor knihovny do external libraries.



Při zavolání `composer install` by se nám již měly nainstalovat správné verze našich balíčků. Takto máme připravenu aplikaci pro jednoho klienta. No jo, řeknete si, ale co když potřebuju upravit jeden balíček, který je závislý na druhém? Běžně by jste museli stáhnout a upravit každý balíček zvlášť, commitnout, přidat tag, pushnout a spustit composer update. To je docela zdlouhavé, pokud upravujete třeba 4 balíčky které jsou na sobě závislé.

### Jak na to chytře?


Nejdříve si načtete aplikaci pomocí `composer install –-prefer-source`, což vám **načte balíčky včetně git repozitářů**, takže nad nimi můžete pracovat, přepínat větve, přidávat tagy atd. Pokud chcete upravovat jen konkrétní balíčky, **je lepší zavolat `composer update namespace/package --prefer-source` nad konkrétním balíčkem**, protože jinak se stahují všechny balíčky z composer včetně git meta dat a tato akce může nějakou dobu trvat, obzvláště pokud máte hodně závislostí.

Poté jednoduše přidáte v phpstormu repozitáře do jeho správy.

 <div class="text-center">
     <img src="/assets/images/posts/2017/composer/git.png">
 </div>
<br/>

Tím se dostáváme k tomu **největšímu usnadnění**. Teď již můžeme dělat změny ve všech balíčcích v jednom projektu. Pro usnadnění je dobré ještě v nastavení phpstormu zaškrtnout volbu **control repositories synchronously**, která vám umožní pracovat s větvemi jako by to byla jedna. **Tzn. pokud vytvoříte jednu větev test, vytvoří se ve všech repozitářích a udělá se checkout.**

 <div class="text-center">
      <img src="/assets/images/posts/2017/composer/synchro.png">
  </div>
 <br/>

**PhpStorm tedy umí pracovat s několika git repozitáři současně.** Pro práci doporučuji vytvořit novou větev, což lze udělat jednoduše a phpstorm už se postará o zbytek. Dole na obrázku vidíte že se mi automaticky všechny repozitáře přeply do větve test. Commit a push probíha obdobně.
<div class="text-center">
     <img src="/assets/images/posts/2017/composer/branch.png">
 </div>
<br/>


Pak už jen stačí vytvořit tag a profit.

*Tip na závěr:* pro hromadný update repozitářů z remote doporučuji plugin **[git extender](https://plugins.jetbrains.com/plugin/7835-git-extender)**


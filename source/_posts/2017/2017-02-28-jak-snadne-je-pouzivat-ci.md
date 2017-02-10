---
layout: post
title: "Jak snadné je používat CI"
perex: "Nejspíš jste zkratku CI již někde viděli, možná tušíte, co Continuous Integration znamená. Chcete vědět, jak snadno CI používat na vašich projektech? Pak čtěte dále."
author: 16
---

## Co je to CI

Viděli jste už náš [slovníček](https://pehapkari.cz/slovnicek/#ci)?

Pokud (nejen) zde nebudete nějakému pojmu rozumět, zkuste se tam podívat. Jestliže ani tam nenaleznete odpověď, napište do diskuze a třeba jej někdo přidá. :-)

Tenhle post je určen **především začátečníkům** a lidem, kteří s CI ještě nemají žádné zkušenosti nebo mají pouze minimální.

Článek navazuje na jiný článek: [Kostra testované aplikace](https://pehapkari.cz/blog/2017/02/27/kostra-testovane-aplikace/).

## Cesta časem

První web jsem si zkusil napsat na střední škole, řekněme 10 let zpátky. Chtěl jsem si udělat seznam filmů a mít ho vždy s sebou. Našel jsem si nějaké informace jak na to, dostal jsem se k PHP a nějak jsem to "nabastlil". S trochou štěstí to (většinou) fungovalo. Nevěděl jsem příliš proč, ale fungovalo. Více jsem to neřešil.

Zkusil jsem si taky udělat několik webů pro různé známé. Způsob práce byl ale vždy podobný. Nějak jsem to neřešil, byl jsem s tím spokojený a dělal to tak skoro každý. Pamatujete?

Pak jsem se začal živit tvorbou webů profesionálně. A přesvědčil se, že ten punkový vývoj není vážně vůbec nic neobvyklého. Po desítkách probdělých nocí hledáním chybějících středníků jsem se dostal k tomu, že se dá ten kód také nějak testovat.

Teď je rok 2017 a nástrojů, jak držet kvalitu kódu na uzdě, je velké množství. A jejich použití je tak snadné, že není rozumné se jim dále vyhýbat.

## Co budeme dělat?

Vezmeme si jednoduchý projekt, který jsme vytvořili nedávno: [Kostra testované aplikace](https://pehapkari.cz/blog/2017/02/27/kostra-testovane-aplikace/).

Projekt si ale nahrajeme na [GitLab.com](https://gitlab.com/) - bezplatnou hostovací službu pro Git repozitáře.

A ukážeme si, jak velice snadno využít integrované CI GitLabu a spouštět tak testy zcela automatizovaně.

Povedu Vás krok po kroku. Předpokládám ale, že:

* jste zaregistrovaní a přihlášeni na [gitlab.com](https://gitlab.com/),
* jste prošli článek [Kostra testované aplikace](https://pehapkari.cz/blog/2017/02/27/kostra-testovane-aplikace/), rozumíte mu a výsledný kód máte po ruce.

## První krůčky - nahrání projektu na GitLab

Nejdříve si nahrajeme již rozpracovaný Git repozitář na GitLab.com

1. [Vytvořte si repozitář](https://gitlab.com/projects/new) na GitLabu. Pojmenujte jej třeba `test-ci`.

2. Otevřete si adresář [s rozpracovaným projektem](https://pehapkari.cz/blog/2017/02/27/kostra-testovane-aplikace/):

    ```bash
    cd C:\xampp\htdocs\test-project
    ```

3. Teď si nastavte cestu k *remote* repozitáři. To bude "kopie" na GitLab.com. A rovnou *pushnětě* (nahrajte) kód:

    ```bash
    git remote add origin git@gitlab.com:example/test-ci.git
    git push --set-upstream origin master
    
    ```

    Vaší cestu k repozitáři uvidíte na stránce po vytvoření projektu na GitLab.com.

Nyní byste měli mít nahraný repozitář na GitLab.com. Zatím ale nic jiného, žádné CI.

Můžete se ujistit, zda-li tomu tak opravdu je.

## Na řadu přichází CI

Když máte kód, k němu testy, jak moc je tedy složité spouštět testy automatizovaně?

V případě hostování na GitLabu se přímo vybízí využít [integrované CI](https://about.gitlab.com/gitlab-ci/).

Do projektu přidejte soubor `.gitlab-ci.yml` který CI řekne, co se má dít.

```yaml
build:
  image: phpdocker/phpdocker:7.0
  script:
    - composer install
    - php vendor/bin/phpunit

```

Co to všechno znamená?

1. `build:` říká, jak se jmenuje aktuální úloha. Může jich být více. My máme na ukázku jednu.

2. `image: phpdocker/phpdocker:7.0` říká, že se má test spustit v [Docker](https://www.docker.com/what-docker) image [phpdocker/phpdocker](https://hub.docker.com/r/phpdocker/phpdocker/). Abyste mohli používat CI, vystačíte si s informací, že je to jakýsi obraz Linuxu, ve kterém se budou spouštět skripty. Konkrétně tenhle obraz obsahuje většinu toho, co je potřeba pro běžné PHP aplikace.

3. `script:` sekce obsahující jednotlivé příkazy, které se budou spouštět. Co řádek, to jeden příkaz. Jsou to vlastně stejné příkazy, které spouštíte v příkazové řádce. Jsou povědomé, že? Oba jste totiž používali.

4. `composer install` říká, že se mají nainstalovat Composer závislosti.

5. `php vendor/bin/phpunit` konečně spustí naše testy.

Jen commitnu a pushnu. GitLab sám spustí CI. **To je vážně vše!**

Výsledky buildů uvidíte pak u commitů i v pull-requestech. Ty je možné nastavit tak, aby nešly mergnout, pokud nemají úspěšný build (procházející testy).

## Jde to ještě vylepšit?

### Composer

Můžeme trošku zrychlit instalaci Composeru a vyhnout se případným dotazům na uživatelský vstup:

```bash
composer install --no-interaction --prefer-dist
```

### Code Coverage

Také můžeme nechat generovat Code Coverage report.

V rootu aplikace již máme vytvořený konfigurační souboru `phpunit.xml`. Stačí v něm nastavit, které složky se mají procházet pro generování code coverage reportu.

Obsah `phpunit.xml` by měl vypadat následovně:

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
    <!-- source to check coverage for -->
    <filter>
        <whitelist>
            <directory suffix=".php">src</directory>
        </whitelist>
    </filter>
</phpunit>

```

Pro generování code coverage reportu je vyžadována extenze `XDebug`.

Při spouštění v CI a použití image `phpdocker/phpdocker` (viz. obsah `.gitlab-ci.yml`) je potřeba XDebug explicitně povolit, takto:

```bash
php -d$XDEBUG_EXT vendor/bin/phpunit --coverage-text
```

Pokrytí kódu testy umí zobrazovat přímo GitLab, jen je potřeba u projektu nastavit pod možnostmi `CI/CD Pipelines` část `Test coverage parsing` na `^\s*Lines:\s*\d+.\d+\%` (pro PHPUnit - více vzorů naleznete přímo ve formuláři).

## Hotový kód ke stažení

Pokud byste se chtěli podívat, jestli jste postupovali správně, zde je optimální výsledek:
https://gitlab.com/hranicka/pehapkari-test-ci

## Co lze ještě dělat s CI?

My jsme CI použili jen pro spuštění PHPUnit testů.

Lze ale spouštět vše, co se dá spuštět přes příkazový řádek. Pokud bych narážel na možnosti využívaného Docker image, můžu si vytvořit libovolný vlastní. Možnosti jsou tak (skoro) neomezené.

Často v CI probíhá celá řada úloh, jako:

* sestavení aplikace (Composer, Bower, Grunt/Gulp, ...),
* kontrola code style (PHP CodeSniffer),
* statická analýza (PHPStan),
* test databázových migrací,
* samotný test kódu (PHPUnit),
* deploy aplikace,
* a další...

## Existuje i něco jiného než GitLab CI?

Ano. A alternativ je spousta. Například:

* [Travis CI](https://travis-ci.org/) - běžně se používá u open-source projetků na github.com; podporuje pouze GitHub.
* [Shippable](https://app.shippable.com/) - podporuje GitLab, BitBucket i GitLab. Omezení v bezplatné verzi je jeden souběžný build a jeden Docker container.
* [Codeship](http://codeship.com/) - podporuje GitHub, BitBucket i GitLab. Zdarma umožňuje 100 builů/měsíc a jeden souběžný build.
* [Circle CI](http://circleci.com/) - podporuje GitHub a BitBucket. V bezplatné verzi je možný pouze jeden souběžný build a jeden Docker container. Navíc je omezení 1500 build minut/měsíc.

Vybral jsem si GitLab pro ilustraci z toho důvodu, že je nativní součástí hostovací služby pro repozitáře a není tak potřeba nic explicitně nastavovat.

Vedle toho, GitLab nefunguje pouze na gitlab.com, ale existuje také očesaná open-source verze (GitLab CE), která je bezplatná a self-hosted. Pro náročné uživatele a velké firmy pak ještě placená self-hosted (GitLab EE), která by měla být shodná s tou provozovat bezplatně na gitlab.com.

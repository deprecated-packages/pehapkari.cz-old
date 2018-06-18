---
id: 31
title: "Nastavení GitLab CI pro produkční aplikaci"
perex: "GitLab CI je dnes moderní. Vychází o něm články s krásnými, jednoduchými příklady, jak jej začít používat. Pak ale člověk narazí. Jak se utkat s překážkami a vyjít z toho jako vítěz?"
author: 12
related_items: [25]
tweet: "Urodilo se na blogu: Nastavení #GitLab #CI pro produkční aplikaci"
---

Už delší dobu jsem uvažoval, že vyzkouším GitLab a jeho [CI](https://cs.wikipedia.org/wiki/Pr%C5%AFb%C4%9B%C5%BEn%C3%A1_integrace)
jako alternativu k Bitbucketu. Prozatím jsem jako CI používal [PHPCI](https://www.phptesting.org/), který sice nebyl špatný,
ale spokojený jsem s ním zrovna také nebyl. Nakonec mě přesvědčil až [článek Martina Hujera na Zdrojáku](https://www.zdrojak.cz/clanky/gitlab-jako-continuous-integration-nejen-pro-php/),
který nastavení popisoval velice jednoduše. Tak jednoduché jsem to bohužel neměl. Nakonec jsem se tím ale prokousal a zde je výsledek.
Před pokračováním doporučuji si článek přečíst, můj článek na něj reaguje.

## Jen pro pořádek

* Používám hostovanou verzi GitLabu. Rozcházet jej u sebe se mi zatím nechtělo a vzhledem k "ceně" to pro mě asi ani nemá smysl.
* Používání skriptů v Composeru mi příliš nevyhovuje. Při testování na CI používám jiný sled příkazů než na lokále.
* Pro GitLab jsem použil vlastní Runner. Některé věci tedy mohou platit pouze pro tento use-case.

## Dodatečná PHP rozšíření

V odkazovaném článku je zmíněn Docker image `geertw/docker-php-ci`. Většina lidí asi sáhne buď po něm, nebo po čistém PHP image.
Po chvíli jsem ale zjistil, že mi v něm spousta rozšíření chybí. Musel jsem tedy řešit jak je doinstalovat. Originální image
kompiluje PHP ze zdrojáků a tak když chci rozšíření doinstalovat, musím ty standardní instalovat přes `docker-php-ext-install`
a ty nestandardní přes `pecl install`. Některé je navíc potřeba nastavit přes `docker-php-ext-configure` a abych nezapomněl,
musím mít nainstalovány potřebné dev knihovny. Občas byl oříšek přijít na to, co dané rozšíření chce. Co je horší, samotný
update APT a instalace rozšíření trvá zbytečně dlouho.

Nakonec jsem se tedy rozhodl udělat si image vlastní. Vyšel jsem z [phusion/baseimage-docker](https://github.com/phusion/baseimage-docker)
a vyházel z něj vše co nebylo nutné (je koncipován spíše na produkci). PHP jsem do něj nainstaloval z mého oblíbeného
[PPA od Ondřeje Surého](https://launchpad.net/~ondrej/+archive/ubuntu/php). Jaké to má výhody?

* Balíčky se nainstalují jen jednou, při buildu image a samotný běh testů na CI je pak velmi rychlý.
* Jsem zvyklý více na Ubuntu než Debian a baseimage-docker vychází z Ubuntu.
* PPA od Ondřeje Surého obsahuje všechny rozšíření, na které si jen vzpomenu. Nemusím tedy řešit závislosti rošíření a nechat je kompilovat, vše si nainstaluje APT.
* Rozšíření v image instaluji jednotným zůsobem, nemusím řešit co je z PECLu a co ne.
* Mohu si v něm rovnou přes Composer globálně nainstalovat balíčky jako [hirak/prestissimo](https://github.com/hirak/prestissimo) a zrychlit tím instalaci závislostí. Do konfigurace `cache` v `.gitlab-ci.yml` lze totiž zapsat jen věci uvnitř build složky, tedy ne `~/.composer`
* Buildy image lze na Docker Hubu zautomatizovat. Buildí se rovnou z Githubu, např. když vyjde nová verze PHP, a více se o ně nestarám.
* Hodně se tím vyčistí konfigurace CI.

Výsledek si můžete vyzkoušet použitím image [sunfoxcz/docker-php-build](https://hub.docker.com/r/sunfoxcz/docker-php-build/).

## Spouštění na různých verzích PHP

Věcí, které se stahovaly, instalovaly a spouštěly, bylo hodně. Build pak běžel zbytečně dlouho. Chvíli mi trvalo přijít na to, jak to správně sestavit.

* Je potřeba nedefinovat si `stages`, aby CI vědělo, jak má po sobě joby pouštět. Jinak je bude pouštět klidně paralelně. Jindy zase paralelní spouštění chceme.
* U jobů, které mají pouze připravit nějaká data dalším jobům, je pak potřeba nastavit `artifacts`, aby data byla zachována.
* <del>Artefaktům je zase potřeba nastavit `expire_in`, aby se neuchovávaly věčně.</del> (v [GitLab > 9.0 automaticky](https://about.gitlab.com/2017/03/22/gitlab-9-0-released/))
* I tak je to hodně řádků. Hodilo by se to něčím zjednodušit. Jen náhodou jsem narazil na [šablony](https://docs.gitlab.com/ce/ci/yaml/#special-yaml-features), když jsem hledal v dokumentaci něco úplně jiného :-)
* Každý job by měl mít definován nějaký image, jinak používá image defaultní, definovaný na CI runneru. Lze ovšem definovat i globálně pro všechny joby v rootu konfigurace.

Takto vypadá výsledek:

```yaml
stages:
    - download
    - test

download:
    stage: download
    image: sunfoxcz/docker-php-build:5.6
    script:
        - composer create-project --no-interaction --no-progress --prefer-dist jakub-onderka/php-parallel-lint temp/php-parallel-lint ~0.9
        - composer create-project --no-interaction --no-progress --prefer-dist nette/code-checker temp/code-checker ~2.5.0
    artifacts:
        paths:
            - temp/php-parallel-lint
            - temp/code-checker
        expire_in: 1 hour

.test_template: &test_template
    stage: test
    services:
        - mongo:latest
        - mysql:latest
    before_script:
        - composer install --no-interaction --no-progress --no-suggest --optimize-autoloader
        - cp app/Config/config.test.neon app/Config/config.local.neon
        - "mysql -h mysql $MYSQL_DATABASE -p$MYSQL_ROOT_PASSWORD < sql/000_structure.sql"
        - "mysql -h mysql $MYSQL_DATABASE -p$MYSQL_ROOT_PASSWORD < tests/data/testdata.sql"
        - vendor/bin/phinx migrate -e production
    script:
        - php temp/php-parallel-lint/parallel-lint.php -e php,phpt -j $(nproc) app tests
        - php temp/code-checker/src/code-checker.php --short-arrays -d app
        - php temp/code-checker/src/code-checker.php --short-arrays -d tests
        - vendor/bin/tester -s -p php -c tests/php.ini tests

test:5.6:
    image: sunfoxcz/docker-php-build:5.6
    <<: *test_template

test:7.0:
    image: sunfoxcz/docker-php-build:7.0
    <<: *test_template

test:7.1:
    image: sunfoxcz/docker-php-build:7.1
    <<: *test_template
```

## Další služby

Když přidám `services`, jejich hostname je vždy jejich jméno. Tohle mi chvíli trvalo dohledat. Co mě dost překvapilo,
že služby nejde mezi joby sdílet. Ani když jejich definici dám globálně. Nechal jsem je proto jen v šabloně pro testy,
aby ostatní joby zbytečně nespouštěly jejich instance. Přitom by to bylo super, udělat si job, který nakrmí databázi a
ostatním jobům tak ušetří spoustu práce. Dokonce je na to založená issue, ale implementace zatím bohužel není.

## Volání externích zdrojů

Po skončení buildu jsem chtěl poslat notifikaci na Slack. Moje staré CI na to mělo přímo plugin. Tady jsem musel opět
hledat. Nakonec jsem našel, že se notifikace do Slacku dají nastavit ve webovém rozhraní GitLabu, a to pro více věcí než
jen build, tak jsem to nastavil tam a neřešil, jak to nastavit v konfiguraci buildu.

Po úspěšném buildu jsem ale chtěl také zavolat [Deployer](https://github.com/REBELinBLUE/deployer), přes který aktuálně
nasazuji na server. Tam už jsem to nakonec řešit musel. Po nějakém tom zkoumání vypadal výsledek takto:

```yaml
stages:
    - download
    - test
    - deploy

deploy to production:
    stage: deploy
    environment: production
    image: sunfoxcz/docker-php-build:5.6
    script:
        - curl -s -X POST https://deployer.domain.tld/deploy/$DEPLOYER_WEBHOOK_KEY
    only:
        - master@skupina/repozitar
```

* Přidal jsem další položku do `stages`, aby se deploy spouštěl až na konec.
* Při prvním nasazení se `environment` objeví v rozhraní GitLabu. Poté je tam vidět, co je nasazeno. Snadno se pak přidá např. staging prostředí.
* Nasazovat do produkce chci jen z masteru. Je také dobré napsat do `only` celý název repozitáře, aby se job nespouštěl i ve forku.
* Super je, že k nasazení nedojde, ať už skončí špatně testy v jakékoliv verzi PHP.
* Ve webovém rozhraní se dají nastavit tzv. "Secret Variables", aby se nemusely api klíče a hesla psát přímo do configu. Příkladem je proměnná `$DEPLOYER_WEBHOOK_KEY`.
* Pokud se necítíte na úplně automatické nasazení, použijte `when: manual`. Tato volba [přidá do GitLabu tlačítko](https://docs.gitlab.com/ce/ci/yaml/#when), kterým nasadíte.

## Vlastní runner

I když se mi podařilo čas buildu stáhnout na slušnou úroveň, na free instancích GitLabu mi to stále přišlo dost dlouho.
Cvičně jsem si tedy na [Digital Ocean](https://www.digitalocean.com/) vytvořil z šablony 2GB VPS s Dockerem. Instalace
runneru je pak na pár minut. Rozdíl v rychlosti je velký a jede to skvěle i při 4 konkurenčních buildech.
Za ty prachy to rozhodně stojí.

Pro ukázku uvedu ještě kompletní `.gitlab-ci.yml`:

```yaml
variables:
    GIT_DEPTH: "1"
    # Configure mysql environment variables (https://hub.docker.com/r/_/mysql/)
    MYSQL_DATABASE: test_db
    MYSQL_ROOT_PASSWORD: xxx

stages:
    - download
    - test
    - deploy

download:
    stage: download
    image: sunfoxcz/docker-php-build:5.6
    script:
        - composer create-project --no-interaction --no-progress --prefer-dist jakub-onderka/php-parallel-lint temp/php-parallel-lint ~0.9
        - composer create-project --no-interaction --no-progress --prefer-dist nette/code-checker temp/code-checker ~2.5.0
    artifacts:
        paths:
            - temp/php-parallel-lint
            - temp/code-checker
        expire_in: 1 hour

.test_template: &test_template
    stage: test
    services:
        - mongo:latest
        - mysql:latest
    before_script:
        - composer install --no-interaction --no-progress --no-suggest --optimize-autoloader
        - cp app/Config/config.test.neon app/Config/config.local.neon
        - "mysql -h mysql $MYSQL_DATABASE -p$MYSQL_ROOT_PASSWORD < sql/000_structure.sql"
        - "mysql -h mysql $MYSQL_DATABASE -p$MYSQL_ROOT_PASSWORD < tests/data/testdata.sql"
        - vendor/bin/phinx migrate -e production
    script:
        - php temp/php-parallel-lint/parallel-lint.php -e php,phpt -j $(nproc) app tests
        - php temp/code-checker/src/code-checker.php --short-arrays -d app
        - php temp/code-checker/src/code-checker.php --short-arrays -d tests
        - vendor/bin/tester -s -p php -c tests/php.ini tests

test:5.6:
    image: sunfoxcz/docker-php-build:5.6
    <<: *test_template

test:7.0:
    image: sunfoxcz/docker-php-build:7.0
    <<: *test_template

test:7.1:
    image: sunfoxcz/docker-php-build:7.1
    <<: *test_template

deploy to production:
    stage: deploy
    environment: production
    image: sunfoxcz/docker-php-build:5.6
    script:
        - curl -s -X POST https://deployer.domain.tld/deploy/$DEPLOYER_WEBHOOK_KEY
    only:
        - master

cache:
    paths:
        - vendor
```

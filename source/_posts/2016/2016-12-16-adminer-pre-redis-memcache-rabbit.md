---
layout: post
title: "Adminer pre Redis, Memcache, RabbitMQ"
perex: "Pred nejakým časom som potreboval vyhľadávať a zoraďovať dáta v Redise. Takže som si k tomu napísal jednoduchý PHP skript. A keď už som ho mal napísaný, chcel som pomôcť aj dalším ľuďom, ktorí by riešili podobný problém ako ja."
author: 3
---

Rozhodol som sa preto, že napíšem rozšírenie do [Adminera](https://www.adminer.org). Iste ho väčšina pozná a používa.

Nevyhovoval mi však úplne štýl, akým je Adminer napísaný. Hlavne spôsob pridávania nových rozšírení v podobe nových typov systémov je až príliš orientovaný na SQL. Napr. pri zmene typu systému sa nezmení ani prihlasovací formulár (vždy sú tam len políčka server, username, password a database). Rozhodol som sa, že skúsim vytvoriť aplikáciu, v ktorej by sa na takéto možnosti rozšírenia myslelo hneď od začiatku (na kopec iných vecí sa určite nemyslelo :) ale tak to chodí).

A tak vznikla aplikácia, ktorej pracovný názov je Adminer next generation (alebo skrátene Adminer NG - poznáte to, vymýšľanie názvov je jedna z najkomplikovanejších vecí v informatike, tak som to zbytočne nekomplikoval a radšej som sa venoval programovaniu).

[Adminer NG](https://github.com/lulco/adminerng) je napísaná ako jednoduchá [Nette aplikácia](https://nette.org) s využitím [Bootstrapu](http://getbootstrap.com). V súčasnosti umožňuje pripojenie k:
- [Redis-u](https://redis.io)
- [Memcache-i](http://php.net/manual/en/book.memcache.php)
- [RabbitMQ](https://www.rabbitmq.com)
- [MySQL](https://www.mysql.com)

Pracuje sa na možnosti pripojenia k [Elasticsearch-u](https://www.elastic.co/products/elasticsearch) a [PostgreSQL](https://www.postgresql.org).

Všetky typy pripojení majú vlastný prihlasovací formulár (obr. 1)
![Adminer NG Login](http://midatech.sk/adminerng/screenshots/login.png)
Obr. 1 Login screen

A vlastné spracovanie požiadaviek. Aktuálna verzia slúži len na vypisovanie, filtrovanie a zoraďovanie dát z jednotlivých systémov (obr. 2 a obr. 3).

![Adminer NG Redis lists](http://midatech.sk/adminerng/screenshots/redis_lists.png)
Obr. 2 Výpis položiek pre Redis databázu

![Adminer NG Redis hash with filter](http://midatech.sk/adminerng/screenshots/redis_hash_filter.png)
Obr. 3 Filtrovanie kľúčov v Redis Hash-i

V Redise si takto môžete prezerať:
- databázy
- kľúče
- hash-e
- set-y.

Pre memcache sú to
- kľúče a hodnoty

V RabbitMQ sú k dispozícii:
- virtual hosty
- fronty
- jednotlivé správy v nich

A pre MySQL:
- databázy
- tabuľky
- view-y
- jednotlivé záznamy

Samozrejme, pracuje sa aj na editácii a vytváraní záznamov / tabuliek / databáz atď.

Adminer NG si môžete [stiahnuť ako jeden súbor](http://midatech.sk/adminerng/download.php) a hneď používať alebo si môžete stiahnuť aktuálnu verziu z [GitHub-u](https://github.com/lulco/adminerng) a spustiť `sh scripts/init.sh`, ktorý za vás spustí `composer install` a vytvorí priečinky `log` a `temp` v roote aplikácie.
Pokiaľ vám niečo bude chýbať (čo je v tejto fáze vývoja viac ako isté), môžete spraviť issue alebo ideálne rovno pull request. Vopred za všetky ďakujem :)

Ak sa chcete čokoľvek opýtať alebo konštruktívne skritizovať, napíšte do diskusie :)

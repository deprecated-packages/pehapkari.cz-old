---
layout: post
title: "Adminer pre Redis, Memcache, RabbitMQ"
perex: "Pred nejakým časom som potreboval vyhľadávať a zoraďovať dáta v Redise. Takže som si k tomu napísal jednoduchý PHP skript. A keď už som ho mal napísaný, chcel som pomôcť aj dalším ľuďom, ktorí by riešili podobný problém ako ja."
author: 3
---

Rozhodol som sa preto, že napíšem rozšírenie do [Adminera](https://www.adminer.org). Iste ho väčšina pozná a používa. Nevyhovoval mi však úplne štýl, akým je Adminer napísaný. Hlavne spôsob pridávania nových rozšírení v podobe nových typov systémov je až príliš orientovaný na SQL. Napr. pri zmene typu systému sa nezmení ani prihlasovací formulár (vždy sú tam len políčka server, username, password a database) a tak som sa rozhodol, že skúsim vytvoriť aplikáciu, v ktorej by sa na takéto možnosti rozšírenia myslelo hneď od začiatku (na kopec iných vecí sa určite nemyslelo :) ale tak to chodí). A tak vznikla aplikácia, ktorej pracovný názov je Adminer next generation (alebo skrátene Adminer NG - poznáte to, vymýšľanie názvov je jedna z najkomplikovanejších vecí v informatike, tak som to zbytočne nekomplikoval a radšej som sa venoval programovaniu).

Adminer NG je napísaná ako jednoduchá [Nette aplikácia](https://nette.org) s využitím [Bootstrapu](http://getbootstrap.com). V súčasnosti umožňuje pripojenie k:
- [Redis-u](https://redis.io)
- [Memcache-i](http://php.net/manual/en/book.memcache.php)
- [RabbitMQ](https://www.rabbitmq.com)
- [MySQL](https://www.mysql.com)

Pracuje sa na možnosti pripojenia k [Elasticsearch-u](https://www.elastic.co/products/elasticsearch) a [PostgreSQL](https://www.postgresql.org).

Všetky typy pripojení majú vlastný prihlasovací formulár a vlastné spracovanie požiadaviek. Aktuálna verzia slúži len na vypisovanie, filtrovanie a zoraďovanie dát z jednotlivých systémov. V Redise si takto môžete prezerať databázy, kľúče, hash-e a set-y. Pre memcache sú to kľúče a ich hodnoty. V RabbitMQ sú k dispozícii virtual hosty, fronty a jednotlivé správy v nich. A pre MySQL databázy, tabuľky, view-y a jednotlivé záznamy. Samozrejme, pracuje sa aj na editácii a vytváraní záznamov / tabuliek / databáz atď.

Adminer NG si môžete [stiahnuť](http://midatech.sk/adminerng/download.php) ako jeden súbor a hneď používať a pokiaľ vám niečo bude chýbať (čo je v tejto fáze vývoja viac ako isté), môžete spraviť issue alebo ideálne rovno pull request na [githube](https://github.com/lulco/adminerng). Vopred za všetky ďakujem :)

Ak sa chcete čokoľvek opýtať alebo konštruktívne skritizovať, napíšte do diskusie :)

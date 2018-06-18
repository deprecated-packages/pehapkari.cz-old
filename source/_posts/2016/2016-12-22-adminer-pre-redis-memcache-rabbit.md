---
id: 7
title: "Adminer pre Redis, Memcache, RabbitMQ"
perex: "Pred nejakým časom som potreboval vyhľadávať a zoraďovať dáta v Redise. Takže som si k tomu napísal jednoduchý PHP skript. A keď už som ho mal napísaný, chcel som pomôcť aj dalším ľuďom, ktorí by riešili podobný problém ako ja."
author: 3
tweet: "Urodilo se na blogu: #Adminer pre #Redis, #Memcache, #RabbitMQ"
---

Rozhodol som sa preto, že napíšem rozšírenie do [Adminera](https://www.adminer.org).

Nevyhovoval mi však úplne štýl, akým je Adminer napísaný. Hlavne **spôsob pridávania nových rozšírení v podobe nových typov systémov je až príliš orientovaný na SQL**. Napr. pri zmene typu systému sa nezmení ani prihlasovací formulár (vždy sú tam len políčka server, username, password a database).

Rozhodol som sa, že skúsim vytvoriť aplikáciu, **v ktorej by sa na takéto možnosti rozšírenia myslelo hneď od začiatku** (na kopec iných vecí sa určite nemyslelo :) ale tak to chodí).

A tak vznikla aplikácia, ktorej názov je UniMan (původně *Adminer Next Generation*, skrátene *Adminer NG*).

## V čom je napísaný a čo podporuje

[UniMan](https://github.com/lulco/uniman) je napísaná ako jednoduchá [Nette aplikácia](https://nette.org) s využitím [Twitter Bootstrapu](http://getbootstrap.com).

### V súčasnosti umožňuje pripojenie k

- [Redis](https://redis.io)
- [Memcache](http://php.net/manual/en/book.memcache.php)
- [RabbitMQ](https://www.rabbitmq.com)
- [MySQL](https://www.mysql.com)

Pracuje sa na možnosti pripojenia k [Elasticsearch-u](https://www.elastic.co/products/elasticsearch) a [PostgreSQL](https://www.postgresql.org).


## Ako vyzerá

Všetky typy pripojení majú vlastný prihlasovací formulár:

<div class="text-center">
    <img src="http://midatech.sk/adminerng/screenshots/login.png" alt="Adminer NG Login">
    <br>
    <em>Login screen</em>
</div>

<br>

A vlastné spracovanie požiadaviek. Aktuálna verzia slúži len na vypisovanie, filtrovanie a zoraďovanie dát z jednotlivých systémov:

<div class="text-center">
    <img src="http://midatech.sk/adminerng/screenshots/redis_lists.png" alt="Adminer NG Redis lists">
    <br>
    <em>Výpis položiek pre Redis databázu</em>
</div>

<br>

<div class="text-center">
    <img src="http://midatech.sk/adminerng/screenshots/redis_hash_filter.png" alt="Adminer NG Redis hash with filter">
    <br>
    <em>Filtrovanie kľúčov v Redis Hash-i</em>
</div>

### V Redise si takto môžete prezerať

- databázy
- kľúče
- hash-e
- set-y

### Pre memcache sú to

- kľúče a hodnoty

### V RabbitMQ sú k dispozícii

- virtual hosty
- fronty
- jednotlivé správy v nich

### A pre MySQL

- databázy
- tabuľky
- view-y
- jednotlivé záznamy

Samozrejme, pracuje sa aj na editácii a vytváraní záznamov / tabuliek / databáz atď.

## Chceš si ho vyskúšať?

Môžete si **stiahnuť aktuálnu verziu** z [GitHubu](https://github.com/lulco/uniman) a spustiť `sh scripts/init.sh`, ktorý za vás spustí `composer install` a vytvorí priečinky `/log` a `/temp` v roote aplikácie.

Pokiaľ vám niečo bude chýbať (čo je v tejto fáze vývoja viac ako isté), môžete [spraviť issue](https://github.com/lulco/uniman/issues) alebo ideálne rovno pull request. Vopred za všetky ďakujem :)

Ak sa chcete čokoľvek opýtať alebo konštruktívne skritizovať, napíšte do diskusie :)

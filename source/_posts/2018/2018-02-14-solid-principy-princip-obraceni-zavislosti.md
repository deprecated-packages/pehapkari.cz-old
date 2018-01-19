---
id: 60
layout: post
title: "SOLID principy: Princip obrácení zavislostí"
perex: '''
Definice říká, že:

```
A. Moduly vyšší úrovně by neměly záviset na modulech nižší úrovně.
Oboje by mělo být  závislé na abstrakci.

B. Abstrakce by neměla záviset na detailech.
Detaily by měly záviset na abstrakci.
```
De facto můžeme říct, že byste téměř vždy měli záviset na abstrakci a nikoli na konkrétní implementaci.
'''
author: 30
related_items: [50,57,58,59]
---

Video (1:29)

[![Video na Youtube](/assets/images/posts/2018/solid-5/youtube.png)](http://www.youtube.com/watch?v=a59M03FZckA)


Na ukázku máme třídu ```CarModel```, která v konstruktoru vyžaduje připojení k databázi.

```php
<?php

class MySQLConnection
{
    public function connect()
    {
        // Připojení k databázi
    }
}

class CarModel
{
    public function __construct(MySQLConnection $connection)
    {

    }
}
```

Tahle architektura se někdy označuje jako „naivní“, protože si myslíte, že přece vždy budete používat MySQL. Navíc je třída ```CarModel``` hůře testovatelná a přenositelná, protože závisí na konkrétní implementaci a ne na abstrakci.

Abychom se takovéto závislosti zbavili, je potřeba například rozhraní ```ConnectionInterface```, které bude třída ```MySQLConnection``` implementovat. Nyní se jen změní závislost v konstruktoru a je hotovo. Pokud budete v budoucnu potřebovat přejít na Oracle, nebude potřeba třídu ```CarModel``` měnit.

```php
<?php

interface ConnectionInterface
{
    public function connect();
}

class MySQLConnection implements ConnectionInterface
{
    public function connect()
    {
        // Připojení k databázi
    }
}

class OracleConnection implements ConnectionInterface
{
    public function connect()
    {
        // Připojení k databázi
    }
}

class CarModel
{
    public function __construct(ConnectionInterface $connection)
    {

    }
}
```

## Zdroje:
http://butunclebob.com/ArticleS.UncleBob.PrinciplesOfOod
https://drive.google.com/file/d/0BwhCYaYDn8EgMjdlMWIzNGUtZTQ0NC00ZjQ5LTkwYzQtZjRhMDRlNTQ3ZGMz/view
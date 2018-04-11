---
id: 68
layout: post
title: "Cyklomatická komplexita"
perex: |
    Cyklomatická komplexita, neboli podmínková složitost je metrika indikující složitost zdrojového kódu.
    
    Jedná se o jednu z hlavním metrik, díky které lze posoudit jeho kvalitu.
    
    Udává počet různých cest skrze zdrojový kód. K čemu je to dobré? Díky tomu lze blíže odpovědět na 3 otázky:
    
    1. Je kód dobře testovatelný?
    2. Je kód snadno čitelný?
    3. Je kód dostatečně spolehlivý?
    
    Pomáhá nám to také určit počet testů, které bychom měli na kódu provádět.
author: 30
tweet: "Urodilo se na blogu: Cyklomatická komplexita #solid"
---

Video (4:45)

[![Video na Youtube](/assets/images/posts/2018/cyklomaticka-komplexita/youtube.png)](http://www.youtube.com/watch?v=heBtNxWki1U)

## Jak se počítá?

Dle vzorce:

```
M = E – N + 2P

M = cyklomatická komplexita
E = hran v grafu
N = uzlů v grafu
P = připojených komponent - ukončení
```

Grafem se myslí CFG (control flow graph). Ten může vypadat následovně:

![Control flow graph](/assets/images/posts/2018/cyklomaticka-komplexita/cfg.png)

Zjednodušeně lze ale říct, že:

```
cyklomatická komplexita = počet rozhodnutí + 1
```

Jako rozhodnutí lze považovat:

```
?
&&
||
or
and
xor
case
catch
elseif
for
foreach
if
while
```

## Příklady

### Příklad 1

```php
function index() {
}
```

Cyklomatická komplexita je: **1**, a to z důvodu, že existuje jediná cesta, kterou lze kódem projít. Žádný rozhodovací faktor zde není.

### Příklad 2

```php
function index($a, $b) {
    if ($a == $b) {
        $value = 1;
    } else {
        $value = 2;
    }

    return $value;
}
```

Cyklomatická komplexita je: **2**, a to z důvodu, že existují 2 cesty, kterými lze kódem projít. Je zde 1 rozhodovací faktor.

### Příklad 3 

```php
function index($a, $b) {
    if ($a == $b && $a > 1) {
        $value = 1;
    } else {
        $value = 2;
    }

    return $value;
}
```

Cyklomatická komplexita je: **3**, a to z důvodu, že existují 3 cesty, kterými lze kódem projít. Jsou zde 2 rozhodovací faktory.

### Příklad 4

```php
function index($a, $b) {
    $value = 1;

    for ($i=$a;$i<$b;$i++) {
        $value++;
    }

    return $value;
}
```

Cyklomatická komplexita je: **2**, a to z důvodu, že je zde 1 rozhodovací faktor. Napříč grafem jsou dvě cesty, kterými lze projít a to je možno vidět na grafu zobrazeném výše, který reprezentuje právě tuto funkci.
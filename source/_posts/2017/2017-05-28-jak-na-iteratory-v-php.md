---
layout: post
title: "Nenechte si podrazit nohy iterátory v PHP"
perex: '''
Iterátory v PHP jsou občas zrádné. Nechovají se vždy intuitivně a velmi špatně se ladí. Zjistěte jak na ně. Vyhnete se tím hodinám zbytečného hledání chyb.
'''
author: 24
---

## Nenechte si podrazit nohy iterátory v PHP

Koukněte na tento kód:

```php
$a = [];
$a[0] = 'first-value';
$a[1] = 'second-value';

$accumulator = [];

// Act
foreach($a as $key1 => $val1) {
	foreach($a as $key2 => $val2) {
		$accumulator[] = [$val1, $val2];
	}
}
```

Kolik prvků bude v `$accumulator`?

Dva vnořené cykly do sebe by měly vytvořit kartézský součin. Tedy 4 řádky. ...a ono se tak opravdu stane! Nic překvapivého.

Nyní nahradím obyčejné pole za [`SplFixedArray`](https://secure.php.net/manual/en/class.splfixedarray.php). Kolik bude prvků v `$accumulator` teď? 

```php
$a = new SplFixedArray(2);
$a[0] = 'first-value';
$a[1] = 'second-value';

$accumulator = [];

// Act
foreach($a as $key1 => $val1) {
	foreach($a as $key2 => $val2) {
		$accumulator[] = [$val1, $val2];
	}
}
```

Kolik byste čekali? Čtyři? Budou tam **dva**! 

Teď si asi říkáte, k čemu je dobré iterovat dvakrát ten samý objekt v sobě. To přece nikdo nepotřebuje. Opak je pravdou, vnořené interování se umí občas pěkně schovat. Koukněme na další příklad:

```php
$object = new class (2) extends SplFixedArray {
	public function __debugInfo()
	{
		$ret = [];
		foreach($this as $key => $val) {
			$ret[(string) $key] = (string) $val;
		}
		return $ret;
	}

};

$object[0] = 'first-value';
$object[1] = 'second-value';

$accumulator = [];

foreach($object as $key1 => $val1) {
	$accumulator[] = $val1;                // (1)
}
```

Takovýto kód napíšete běžně. Pojďme jej spusit... V accumulator budou řádky **dva**.

Nyní si zkuste dát breakpoint na řádek `(1)`. Kolik se vypsalo řádků?

**Jeden?!** Co se stalo? XDebug se pokusil vypsat obsah lokálních proměnných a zavolal metodu `__debugInfo()`. Abychom však rozkryli, v čem je ten zakopaný pes, koukněme na kousek teorie.




## Co je to `foreach`?

```php
$arr = ["one", "two", "three"];
foreach($arr as $key => $value) {
    echo "Key: $key; Value: $value\n";
}
```

je naprosto identické tomuto kódu: ([zdroj](https://secure.php.net/manual/en/control-structures.foreach.php))

```php
$arr = ["one", "two", "three"];
reset($arr);
while(list($key, $value) = each($arr)) {
    echo "Key: $key; Value: $value\n";
}
```

Aha! `foreach` tedy nastaví vždy na začátku pozici iterátoru na začátek a projde iterátorem až do konce. Pokud mám jeden iterator a dva foreach v sobě stane se toto:

1. vnější foreach nastaví ukazatel na začátek
2. vnější foreach přečte první prvek (a posune se na další)
3. vnitřní foreach nastaví ukazatel na začátek
4. vnitřní foreach přečtě první prvek (a posune se na daší)
5. vnitřní foreach přečtě druhý prvek (a posune se na daší)    
6. vnitřní foreach zjistí, že již v iterátoru nic není, končí
7. vnější foreach zjistí, že v iterátoru již nic není, končí

A tak jsme došli ke **dvěma prvkům** místo čtyřem.




## Rychlá oprava

Takže PHP má objekty rozbité a iterování nad datovými strukturami pořádně nepodporuje? Na php.net o tom nic moc nepíší.

Vrátím se tedy k příkladu s `SplFixedArray`. `foreach` mění interní pozici iterátoru a protože dva `foreach`e prochází jen **jeden** iterátor, dostaneme jen dva prvky na výstupu. Potřebujeme tedy uchovávat pozici pro každý `foreach` zvlášť. Co zkusit objekt klonovat?

```php
// Arrange
$a = new SplFixedArray(2);
$a[0] = 'first-value';
$a[1] = 'second-value';

$accumulator = [];

// Act
foreach(clone $a as $key1 => $val1) {
	foreach(clone $a as $key2 => $val2) {
		$accumulator[] = [$val1, $val2];
	}
}
```
Nyní dostaneme prvky **čtyři**. Hurá!

Tímto však kopírujeme celý objekt i s jeho hodnotami, což může být pomalé. Navíc ne všechny objekty počítají s tím, že budou klonovány.

Pokud objekt podporuje `clone`, jako rychlé řešení je tento přístup použitelný. Tento přístup však jen obchází příčinu chyby, neřeší ji.





## Opravdové řešení

Nyní jsem nahradil v původním příkladu s `SplFixedArray` náš objekt za [`ArrayObject`](https://secure.php.net/manual/en/class.arrayobject.php). Kolik bude teď prvků v `$accumulator`?

```php
$a = new ArrayObject();
$a[0] = 'first-value';
$a[1] = 'second-value';

$accumulator = [];

// Act
foreach($a as $key1 => $val1) {
	foreach($a as $key2 => $val2) {
		$accumulator[] = [$val1, $val2];
	}
}
```

Tentokrát budou **čtyři**! Proč? Magie!

Příčina totož neleží v tom, jestli je iterovaný předmět *objekt* nebo *pole*.

- `SplFixedArray` implementuje interface [`Iterator`](https://secure.php.net/manual/en/class.iterator.php)
- `ArrayObject` implementuje [`IteratorAggregate`](https://secure.php.net/manual/en/class.iteratoraggregate.php)

Pojďme se těmto interface kouknout na zoubek.

`Iterator` interface:

```php
interface Iterator extends Traversable {
	function current();
	function key();
	function next(): void;
	function rewind(): void;
	function valid(): bool;
}
```

- mluví vždy o instanci sama sebe
- zná pozici v iterované kolekci
- jeho metody závisející na aktuálním stavu (na aktuální pozici)


`IteratorAggregate` interface:

```php
interface IteratorAggregate extends Traversable {
	function getIterator(): Traversable;
}
```

- `getIterator()` je to továrna
	- **při každém zavolání musí vracet novou instanci `Traversable`**
- nevyžaduje uchovávání žádného stavu související s iterací
	- uchování stavu deleguje do vytvořeného `Traversable`

## Sémantika `Iterator` a `IteratorAggregate`

`Iterator` je pohled na nějaká data. Například [`DirectoryIterator`](TODO). Přes tento iterátor je možné procházet obsah složky.

`IteratorAggregate` říká, obsah objektu, implementující toto rozhraní, je možné procházet pomocí iterátoru, který je dostupný přes metodu `getIterator()`.

Dokonce mi nic nebrání pohled na data upravit. Například mohu data přefiltrovat, třeba takto:

```php
$iterator = new CallbackFilterIterator(
	$collection->getIterator(), 
	function($value, $key) { return rand(0,100) < 50; }
);
foreach($iterator as $key => $value) { /* ... */ }
```

Případně použít úplně jiný pohled na data:
```php
$iterator = new MyAwesomeIterator($collection);
foreach($iterator as $key => $value) { /* ... */ }
```

Všimněte si, že `MyAwesomeIterator` bere jako parametr přímo kolekci, na kterou zprostředkovává pohled.

## A proč je tedy možné `foreach` s `IteratorAggregate` procházet zanořeně?

`foreach` v PHP je chytrý a pokud procházený objekt implementuje rozhraní `IteratorAggregate`, automaticky před začátkem iterace vytáhne "nový pohled".

Když `foreach` přepíšu jako `while`, vypadá to takto:

```php
$collection = /* implementuje IteratorAggregate */;

foreach($collection as $key => $value) { /* ... */ }

// je funkčně stejný jako:

$iterator = $collection->getIterator();
while($iterator->valid()) {
	$key = $iterator->key();
	$value = $iterator->current();

	/* ... */

	$iterator->next();
}

```



## TL;DR Závěr

- Pokud implementujete **kolekci** (objekt, který drží nějaká data), vždy implementujte rozhraní `IteratorAggregate`.
- Pokud implementujete **pohled na data**, implementujte rozhraní `Iterator`.
- pokud chcete, aby se struktura, kterou dostanete na vstupu chovala **stejně jako pole**, vyžadujte rozhraní `IteratorAggreage`
- Ke kolekci může existovat více `Iterator`ů - tedy **více pohledů**, na ta **stejná data**.
- Kolekce by neměla implementovat `I	terator` přímo, protože...
	- tím říká, že na ni v **jednu chvíli** existuje jen **jeden pohled**.
	- má poté **dvě zodpovědnosti** - uchování dat a zprostředkování pohledu na data v ní uložené.
- Dejte si pozor na `SplFixedArray`, `SplObjectStorage` a další kolekce, které implementují iterátor přímo.
- Použijte raději [phpds](https://secure.php.net/manual/en/book.ds.php), kde jsou datové struktury implementovány správně.


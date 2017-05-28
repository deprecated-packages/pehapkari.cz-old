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

Dva vnořené cykly do sebe by měly vytvořit kartézský součin. Tedy 4 řádky. A ono se tak opravdu stane!

Nyní nahradím obyčejné pole za [`SplFixedArray`](https://secure.php.net/manual/en/class.splfixedarray.php). Kolik bude prvků v `$accumulator` teď? 

```php
$a = new \SplFixedArray(2);
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

Budou tam **dva**! 

Teď si asi říkáte, k čemu je dobré iterovat dvakrát ten samý objekt v sobě. To přece nikdo nepotřebuje. Pojďme tedy na další ukázku.

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
	echo $val1 . "\n";                       // (1)
}
```

Tento kód vypadá mnohem reálněji. Pokud jej spustím vypíše obsah pole (= dva řádky).

Nyní si zkuste dát breakpoint na řádek `(1)`.

Kolik se vypsalo řádků? **Jeden?!**



## Co je to foreach?

```php
$arr = array("one", "two", "three");
foreach ($arr as $key => $value) {
    echo "Key: $key; Value: $value<br />\n";
}
```

je naprosto identické tomuto kódu: ([zdroj](https://secure.php.net/manual/en/control-structures.foreach.php))

```php
$arr = array("one", "two", "three");
reset($arr);
while (list($key, $value) = each($arr)) {
    echo "Key: $key; Value: $value<br />\n";
}
```

Aha! `Foreach` tedy nastaví vždy na začátku pozici na začátek a projde pole až do konce. Pokud mám jeden iterator a dva foreach v sobě stane se toto:

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

Vrátím se tedy k příkladu s `SplFixedArray`. Foreach mění iterní pozici iterátoru a protože dva iterátory mění jednu pozici, dostaneme jen dva prvky na výstupu. Potřebujeme tedy uchovávat pozici pro každý foreach zvlášť. Co zkusit objekt klonovat?

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
Nyní dostaneme 4 prvky.

Títo však kopírujeme celý objekt i s jeho hodnotami, což není příliš efektivní řešení, navíc ne všechny objekty počítají s tím, že budou klonovány.

Pokud objekt podporuje `clone`, jako rychlé řešení to není špatné. `Clone` však jen obchází příčinu chyby.





## Opravdové řešení

Nyní jsem nahradil v původním příkladu s `SplFixedArray` náš objekt za [`\ArrayObject`](https://secure.php.net/manual/en/class.arrayobject.php). Kolik bude teď prvků v `$accumulator`?

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

Tentokrát budou **čtyři**! Proč?

To je zvláštní... `\ArrayObject` je přece taky objekt, stejně jako `\SplFixedArray`!

Příčina totož neleží v tom, jestli je iterovaný předmět *objekt* nebo *pole*. `SplFixedArray` implementuje interface [`Iterator`](https://secure.php.net/manual/en/class.iterator.php) a `\ArrayObject` implementuje [`IteratorAggregate`](https://secure.php.net/manual/en/class.iteratoraggregate.php).

Pojďme se těmto interface kouknout na zoubek.

`Iterator` interface:

```php
Iterator extends Traversable {
	abstract public mixed current ( void )
	abstract public mixed key ( void )
	abstract public void next ( void )
	abstract public void rewind ( void )
	abstract public boolean valid ( void )
}
```
- mluví vždy o instanci sama sebe
- zná pozici v iterované kolekci
- jeho metody závisející na aktuálním stavu (na aktuální pozici)


`IteratorAggregate` interface:

```php
IteratorAggregate extends Traversable {
	abstract public Traversable getIterator ( void )
}
```
- je to továrna
- nevyžaduje uchovávání žádného stavu související s iterací (to deleguje)

Pokud tedy metoda `getIterator()` bude **při každém zavolání vracet novou instanci iterátoru**, bude fungovat zanořený foreach, protože PHP si vždy inicializuje novou instanci iterátoru, který si každý uchovává svůj stav iterace.

Kdežto pokud objekt/kolekce implementuje `\Iterator`, existuje jen jeden možný stav iterace, zanořený foreach tedy nikdy fungovat nemůže a je třeba celý objekt klonovat.


## Proč tedy `\Iterator` existuje?

```
iterable
 |- Traversable
   |- Iterator
   \- IteratorAggregate
```

**IteratorAggregate** (kdy `getIterator()` vrací vždy novou instanci) je vhodný pro kolekce. Pro objekty uchovávající data.
**Iterator** je vhodný pro implementaci samotných iterátorů. Něco co implementuje `\Iterator` tedy budeme typicky vracet z metody `getIterator()` z interface `IteratorAggregate`.

Z výše uvedeného tedy plyne, že `iterable`, `Traversable`, `Iterator` a `IteratorAggregate` (který vrací vždy tu stejnou instanci iterátoru) **negarantují, že půjde struktura procházet zanořeně**.

Kolekce by neměla implementuje `\Iterator` přímo, protože koncepčně má potom dvě zodpovědnosti:

1. uchovává svoje data
2. implementuje iterátor sama sebe 

Pokud tyto dvě zodpovědnosti rozdělíme, je možné mít iterátorů více. Každý pak může zprostředkovat jiný pohled na data. (přefiltrovat, seřadit, ...) Navíc takové iterátory poté lze skládat i dohromady. 


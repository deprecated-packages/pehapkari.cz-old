---
id: 8
title: "Testování PHP kódu"
perex: "Testování aplikací není vždy tak snadné, jak se na papíře jeví. Svojí zkušeností jsem dospěl k&nbsp;několika zásadám a&nbsp;postupům, které se mi osvědčily a&nbsp;které se tu pokusím sepsat a&nbsp;částečně i&nbsp;zdůvodnit. Pomáhají mi k&nbsp;psaní čítelnějších a&nbsp;udržovatelnějších testů. Za hlavní přínos pak považuji snadnou rozšiřitelnost testů, jejíž potřeba přichází s&nbsp;rozšiřováním fukcionality projektu."
author: 4
tweet: "Urodilo se na blogu: Testování #PHP kódu #testing"
---

## 2 definice, kterých se držím

### Test je blok kódu

K&nbsp;testu **nepřistupuji jako ke třídě**, k&nbsp;testu **nepřistupuji jako k&nbsp;fukci** K&nbsp;testu přistupuji jako k&nbsp;bloku kódu – **jako ke scriptu**. Následováním tohoto přístupu:

* redukuji objem kódu v&nbsp;jednom testovacím scénáři (jsem veden vyčlenit si helpery mimo samotný test),
* snižuji komplexitu testovacího stacku (jsem veden řešit závislosti správným směrem – jak na to,
   se rozepíši později v článku).

### Test má 3 složky

Neustále si uvědomuji, že test se skládá z

* **definice výchozího stavu**
* **přechodu do jiného stavu**
* následně **validace konečného stavu**

<blockquote class="alert alert-warning">
    <p>
		Psaní testů do tříd <code>TestCase</code> tedy považuji jen za <em>syntactic sugar</em> testovacích frameworků,
		což poskytuje jistý komfort (<code>setUp</code>, <code>tearDown</code>, <code>@dataProvider</code>).
    </p>
</blockquote>

Z&nbsp;těchto základů jsem si pak vyvodil několik zásad.


## Píši `TestCase` třídy bezstavově

Nepíši žádné `$this->someObject` s&nbsp;nějakými daty, mocky nebo testovanými subjekty. Vše předávám přes parametry
metod. Přidává to na přehlednosti a&nbsp;čitelnosti, a&nbsp;tak to usnadňuje pozdější rozšiřování testu.

**Správně**

* Pro rozšíření testu jen přidám `@dataProvider`, extrahuji parametr `5` a očekávanou hodnotu `xyz`.
* Vše co test obsahuje, je na jednom místě. Detaily jsou skryté za voláním metod.

```php
public function testFoo()  : void
{
	$bar = $this->createMockBar(5);
	$service = new Service($bar);

	$result = $service->foo();

	Assert::equals('xyz', $result);
}
```

**Špatně**

* Pro rozšíření testu musím udělat novou třídní proměnnou a zduplikovat kód testu.
* V&nbsp;testu není na první pohled patrné, jak je definován počátečná stav.
* Motivací bývá většinou snaha o znovupoužitelnost objektu (mocku, služby, *value-objectu*), avšak není
  pro ni žádný důvod. V&nbsp;praxi vůbec ničemu nevadí si pro každý běh testu objekty vytvářet.

```php
public function setUp(): void
{
	$this->mockBar = $this->createMockBar(5);
}

public function testFoo(): void
{
	$service = new Service($this->mockBar);

    $result = $service->foo();

    Assert::equals('xyz', $result);
}
```

Do `setUp()` dávám věci, které připravují prostředí pro test, například strukturu databáze. Nedávám tam ale už
insert testovacích dat, která jsou specifická pro daný scénář testu. Skryl bych tím totiž definici výchozího
stavu konkrétního scénáře.

<blockquote class="alert alert-info"><p>
	Z&nbsp;těchto principů také přímo vyplývá, že <code>TestCase</code> třída je <em>immutable</em>.
	Protože není co měnit. ;)
</p></blockquote>


## Pečlivě oděluji části testu

Čím výrazněji jsou od sebe části testu odděleny a&nbsp;čím menší a&nbsp;jednodušší jsou, tím rychleji při čtení
kódu pochopím, co test testuje.

Proto:

* Vyčlením definici výchozího stavu do <a href="https://phpunit.de/manual/current/en/phpunit-book.html#writing-tests-for-phpunit.data-providers">Data Providerů</a>.
* Kód na přípravu stavu rozkouskuji do metod, které případně obalím factory metodou.
* Samotný přechod stavu redukuji ideálně jen na volání jediné metody.
* Asserty oddělím vizuálně od zbytku prázdným řádkem.

```php
/**
 * @dataProvider getDataForFooTest
 */
public function testFoo(string $expectdResult, string $valueForFoo, string $valueForBar): void
{
    $bar = $this->mockBar($valueForBar); // Příprava výchozího stavu
    $foo = $this->mockFoo($valueForBar);
	$service = new Xyz($foo, $bar);

    $result = $service->foo(); // Přechod

    Assert::equals('xyz', $result); // Assertace výsledného stavu
}
```


## Závislosti testovaného kódu a&nbsp;jejich skládání

Když musím kódu, který testuji, dodat nějaké závislosti (často namockované), vždy vytvářím **factory metody**.

Při sestavování závislostí dbám na to, abych praktikoval *Dependency Injection* skrze parametry factory metody
a&nbsp;aby každá factory metoda vytvářela jen jednu věc.

**Správně**

```php
public function testXyz(string $expected, int $valueForBar): void
{
     // Když budu chtít přidat $valueForBar2, upravím jen jedno místo.
     $bar = $this->mockBar($valueForBar);
     // Předávám už hotový objekt – tedy celou závislost. Factory metoda
     // pak z vnějšího pohledu dělá jen jednu věc, vytváří mock Foo
     // a je závislá na tom, aby dostala třídu typu Bar.
     $foo = $this->mockFoo($bar);
     $service = new Xyz($foo);

     $result = $service->xyz();

     Assert::equals($expected, $result);
}

public function mockFoo(Bar $bar): Foo
{
  return Mockery::mock(Foo::class)->shouldRecieve('getBar')->andReturn($bar)->getMock();
}
```

**Špatně**

```php
public function testXyz(string $expected, int $valueForBar)
{
     // Předává se pouze hodnota a factory metoda pak dělá dvě věci,
     // z vnějšího pohledu vytváří mock pro Foo i pro Bar.
     $foo = $this->mockFoo($valueForBar);
     $service = new Xyz($foo);

     $result = $service->xyz();

     Assert::equals('expected', $result);
}

public function mockFoo(int $valueForBar): Foo
{
	// Když budu chtít přidat $valueForBar2, budu muset upravit všechny metody po cestě.
    $bar = $this->mockBar($valueForBar);

    return Mockery::mock(Foo::class)->shouldRecieve('getBar')->andReturn($bar)->getMock();
}
```

<blockquote class="alert alert-info"><p>
	Factory metody nemusí být vůbec definované na <code>TestCase</code> třídě daného testu, ale pokud se jedná o factorky
    určené jen pro konkrétní test, je praktické si je držet na jednom místě. Pokud je ale znovupoužívám,
    extrahuji je do helperů (v PHPUnit do traitů).
</p></blockquote>


## Kdy mockuji a&nbsp;kdy ne

Mockovat je drahé. Je drahé mocky psát a&nbsp;je drahé je pak udržovat. Proto většinou nemockuji:

* value objecty,
* *stateless* služby – jejich metody tudíž vždy vracejí pro konkrétní vstup stejný výstup.

Naopak mockuji:

* služby, které sahají na nějaký stav nebo komunikují mimo aplikaci (disk, databáze, api, …),
* jakékoliv objekty, které mají složitý strom závislostí a&nbsp;je jednodušší je vymockovat, než sestavit jejich závislosti.


## Nedědím od sebe testy

Hlavní zásadu kterou dodržuji je, že testy od sebe nedědím. Mít `DatabaseTestCase`, `ApiTestCase` a&nbsp;podobně,
je zneužití dědičnosti a&nbsp;cesta k obrovské třídě plné kódu, z&nbsp;kterého každý potomek využívá jen nějaký (a vždy jiný)
subset.

Ideální by bylo, kdyby všechny testy dědily přímo od `TestCase`, který je ve frameworku. Avšak v&nbsp;praxi se mi osvědčilo
si pro testovanou aplikaci udělat `abstract MyTestCase` a&nbsp;všechno dědit od něj.

Důvody pro toto porušení jsou:

* Zapsání `Mockery::close()` do `tearDown()` ve společném předkovi jen jednou, aby se neopakoval v&nbsp;každém testu,
  kde se na to navíc snadno zapomene.
* Možnost clearovat globální stav na jednom místě, když pracuji s&nbsp;nějakou *legacy* codebase.
  Například `Legacy_Class_Registry::clearStaticInMemoryCache()` a&nbsp;podobné perličky.

A pak už být nekompromisní, žádná další vrstva dědičnosti. Takže test-třídy píši `final`.


## Pojmenovávám hodnoty v&nbsp;Data Providerech

Zvyšuje čitelnost a&nbsp;zrychluje orientaci v&nbsp;kódu.

**Špatně**

```php
public function getDataForXyzTest(): array
{
     return [
        [true, 7, true],
        [false, 3, false],
     ];
}
```

**Správně**

```php
private const USER_ONLINE = true;
private const USER_OFFLINE = false;

private const USER_ID_KAREL = 7;
private const USER_ID_FERDA = 3;

private const USER_ACTIVE = true;
private const USER_NOT_ACTIVE = false;

public function getDataForXyzTest(): array
{
     return [
        [self::USER_ONLINE, self::USER_ID_KAREL, self::USER_ACTIVE],
        [self::USER_OFFLINE, self::USER_ID_FERDA, self::USER_NOT_ACTIVE],
     ];
}
```


## Dependency Injection Container vždy vytvářím čerstvý pro každý běh scriptu

Když test potřebuje container:

* každý jeden běh testu **musí** mít svou vlastní instanci containeru,
* metoda `createContainer()` musí v&nbsp;testu vždy vrátit nově sestavený container,
* container není nikdy v&nbsp;`$this->container` v&nbsp;`TestCase` třídě.
  Když je náhodou potřeba (ale nemělo by), tak se předává argumentem metody.

## Zjištění aktuálního data, náhody, a&nbsp;podobně vždy předávám jako závislost
V aplikačním kódu nepíši `new DateTime()`, `time()`, `NOW()`, `rand()`.
Získávání nějakého „globálního“ stavu vždy obstarává služba.
Příkladem může být [DateTimeFactory](https://github.com/damejidlo/datetime-factory) nebo:

```php
class RandomProvider
{
    public function rand(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }
}
```

V testech si pak tuto závislost namockuji a&nbsp;předám. V&nbsp;integračních testech upravím službu v&nbsp;DI Containeru:
```php
/**
 * @dataProvider getDataForXyzTest
 */
public function testXyz(..., \DateTimeImmutable $subjectTime): void
{
    $container = $this->createContainer();
    $dateTimeFactory = Mockery::mock(DateTimeFactoryImmutable::class);
    $dateTimeFactory->shouldReceive('getNow')->andReturn($subjectTime);
    $container->removeService('dateTimeFactory');
    $container->addService('dateTimeFactory', $dateTimeFactory);
}
```

Ušetří to pár vrásek, letní-zimní čas a&nbsp;další magické chyby v&nbsp;testech.


## Nepoužívám PHPUnit, když nemusím

[PHPUnit](https://phpunit.de/) má jednu výhodu: super integraci s&nbsp;[PHPStorm](https://www.jetbrains.com/phpstorm/) IDE.
Ale jinak je to bolest.

* `TestCase` třída má asi milión metod, které vůbec mít nemá a&nbsp;ve kterých se nikdo nevyzná.
* Dobrým příkladem jsou asserty, které je zvykem (i&nbsp;když jsou statické) volat na `$this->assertXyz(...)`.
* Mockování:
 * je ukecané,
 * mock-builder se zase volá z&nbsp;kontextu – `$this->getMockBuilder(...)`,
 * mocky defaultně nezakrývají metody, takže když zapomenu metodu nadefinovat, zavolá se původní.
* Samotný framework je hrozně složitý – když potřebuji zdebugovat nějaké divné chování, utápím se v&nbsp;tom.
* Neumí paralelizaci out-of-the-box. Doporučuji podívat se na [tento článek](https://tech.wayfair.com/2015/02/sweet-parallel-phpunit/).

## Když musím používat PHPUnit

* Helpery si píši jako traity. Jsou *context-aware* a&nbsp;je **mnohem** lepší traitit než dědit.
  Doporučuji přečíst si na toto téma <a href="https://qafoo.com/blog/092_using_traits_with_phpunit.html">článek</a>
  od Kora Nordmanna.
* Snažím se alespoň o to, abych mohl používat jiný mockovací framework (osobně fandím [Mockery](http://docs.mockery.io)).


## Separuji testy podle typu a paralelizuji

* **Každý jeden test spouštím ve vlastním procesu**. Legacy kód často obsahuje špinavosti, které ovlivňují
  globální stav aplikace, a&nbsp;zajistit 100% vyčištení kontextu po každém testu v&nbsp;`tearDown` za tu práci nestojí.
* Snažím se **paralelizovat už od prvního testu**. I&nbsp;když v&nbsp;případě legacy kódu to bývá težké.
  Čím víc se paralelizace odloží, tím těžší následně je. Následné hledání, kde na sobě testy závisí, je
  hledání jehly v kupce sena.
* Spouštím **unit testy odděleně od *těch ostatních***, které používají databázi a&nbsp;podobně.
  Když failnou některé z&nbsp;unitových testů, tak ty *ostatní* už ani nespouštím.


## Držím strukturu testů tak, aby kopírovala kód aplikace

Většinou se držím toho, aby:

- `TestCase` třídy kopírovaly třídy v aplikaci (`src/A/B/Service.php` + `tests/A/B/ServiceTest.php`),
- `testXyz` metody kopírovaly metody v testované třídě,
- adresářová struktura ve složce `tests` kopírovala strukturu aplikace,
- stejně tak namespacy, ty však začínají root namespacem `Tests`.


## Používám PHPStorm IDE

- PHPStorm má klávesovou zkraktu `[Ctrl]` + `[Shift]` + `[T]`
  pro: [Navigating Between Test and Test Subject](https://www.jetbrains.com/help/phpstorm/2016.2/navigating-between-test-and-test-subject.html).


## V čem nemám jasno / kacířské myšlenky

- Kdy používat pro assert konečného stavu snapshoty a&nbsp;kdy nikoliv? Jsou snapshoty vůbec dobrý nápad?
- Pohrávám si s myšlenkou, že pro větší monolitické aplikace by každý modul aplikace měl mít svou vlastní
  `tests` složku. V extrémním případě by každá třída měla test třídu hned vedle sebe.


## Závěrem

Napadá vás nějaký dobrý practice, který jsem nezmínil? Napište mi sem do komentářů nebo mi ho [tweetněte](https://twitter.com/Achse_). Díky!

<div class="text-right"><em>Článek vyšel také na <a href="https://petrhejna.org/">blogu</a> autora.</em></div>

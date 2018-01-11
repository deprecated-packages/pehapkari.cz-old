---
id: 55
layout: post
title: "Jak naučit PhpStorm chápat kód"
perex: '''
Fungující napovídání syntaxe vašeho kódu je naprosto základním předpokladem pro dobré fungování pokročilých nástrojů, které vám PhpStorm nabízí. Existuje několik možností, jak PhpStormu pomoci váš kód pochopit. Začneme těmi základními a postupně se dostaneme až k pokročilým.  
'''
author: 6
lang: cs
---

Nástroje jako refaktoring a inspekce kódu jsou plně závislé na tom, jak dobře dokáže PhpStorm váš kód pochopit. Ale protože je PHP dynamicky typovaný jazyk, tak je to mnohem složitější úkol, než třeba ve staticky typované Javě. 

Pokusím se to přiblížit na následujícím kusu kódu. PhpStorm bude mít problém pochopit, co ten kód vrátí a nebude vám schopný dál nic napovídat, ani za vás nic pohlídat. 

```php
<?php
function getUser() {
    return $this->service->getUser();
}
```

Co myslíte, že to vrátí? Instanci `User`? Nějaké ID? Uživatelské jméno? Co když to vrátí `false` pro nepřihlášeného uživatele? *Těžko říct.*

A co takhle? 

```diff
+/**
+ * @return string|bool username of currently logged in user, false if anonymous 
+ */
function getUser() {
    return $this->service->getUser();
}
```

Lepší, co? Takhle tomu PhpStorm porozumí a ví, že se z metody vrací `string` nebo `boolean`. Jak vidíte, tak **lepším popisováním kódu pomůžete nejen PhpStormu, ale i ostatním vývojářům**. 

Ať se nám to líbí nebo ne, tak spousta existujícího PHP kódu vypadá podobně jako ta ukázka nahoře. Ne každý má to štěstí, že může pracovat s kódem napsaným letos pro PHP 7.2 podle [DDD](/blog/2017/12/05/domain-driven-design-language/), naprosto striktně dodržujícím [SRP](/slovnicek/#solid) a používajícím [dependency injection](/blog/2017/01/15/jak-funguje-dependency-injection-v-symfony-a-v-nette/). Velmi pravděpodobně se naopak setkáte s kódem, který by mohl běžet i na PHP 5.3, není moc otestovaný a pochopit ho vám dá dost práce. 

PhpStorm vám může velmi pomoct právě při správě takového legacy kódu. Může ale pracovat jen s tím, co mu dáte. A teď si ukážeme, jak mu dát těch informací co nejvíc. 

## Popisování parametrů volání a návratových hodnot

### Docblocky

Docblocky jsou dokumentační komentáře. Nejsou přímo parsovány při zpracování souboru, ale lze k nim přistoupit z kódu aplikace pomocí reflexe nebo pomocí externích nástrojů (jako třeba IDE). Běžný docblock vypadá nějak takto:  

```php
<?php
/**
 * @param string $name
 * @param int $age
 * @param Address|null $address
 * @return User
 */
public function createUser($name, $age, $address)
{
    return $this->service->createUser($name, $age, $address);
}
```


Blok výše říká IDE, že parametr `$name` je `string`, `$age` je `integer` a `$address` je buď instance `Address` nebo `null`. Také tím říkáme, že metoda vrací instanci `User`. 

Všimněte si, že v případě adresy povolujeme jak `Address` tak `null`, což definujeme pomocí svislítka (`|`). Je důležité popisovat všechny existující možnosti. V tomto případě nás díky tomu PhpStorm upozorní, že máme *zkontrolovat, jestli není adresa `null`*, kdykoli voláme něco jako `$address->getZipCode()`. Přitom stále funguje napovídání metod třídy `Address`. 


Docblocky jsou skvělý nástroj pro starší verze PHP. Pro moderní verze PHP však existuje nástroj ještě lepší. 

### Deklarace typů

Od PHP 7.1 (pokud se obejdete bez nullable, tak již od 7.0) je možné výše zmíněný kus kódu přepsat do následující podoby: 

```diff
+declare(strict_types=1);
// ...
-/**
- * @param string $name
- * @param int $age
- * @param Address|null $address
- * @return User
- */
-public function createUser($name, $age, $address)
+public function createUser(string $name, int $age, ?Address $address): User
{
    return $this->service->createUser($name, $age, $address);
}
```

Zmizel velký komentář a přibylo jen pár znaků. Tato konstrukce má úplně ten samý význam, ale místo komentářů využívá přímo konstrukce jazyka. **Díky tomu jsou typy vynuceny, když funkci použijeme:** 

```php
<?php
$this->createUser('Tom', 'tohle měl být integer');
/*
PHP Warning:  Uncaught TypeError: Argument 2 passed to createUser() must be of the type integer, string given, called in php shell code on line 1 and defined in php shell code:1
Stack trace:
#0 php shell code(1): createUser('tom', 'old')
#1 {main}
  thrown in php shell code on line 1
*/
```

Dejte si ale pozor, že máte v souboru [přidanou  `strict_types` deklaraci](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.strict), která vypne přetypovávání. V opačném případě vám PHP s klidem převede `"11 horses"` na `11` [jako normálně](https://3v4l.org/QlLOV) (pro porovnání chování se [strict types](https://3v4l.org/bUAEr)).

Použití typů místo docblocků si můžete nechat i automaticky zkontrolovat [Symplify](https://github.com/Symplify/CodingStandard#block-comment-should-only-contain-useful-information-about-types-wrench) resp. [Slevomat](https://github.com/slevomat/coding-standard#slevomatcodingstandardtypehintstypehintdeclaration-) coding standardem. 

## Union typy a pole

Union typ je typ, který se skládá z více dalších typů (více ve [článku od Ondry Mirtese](https://medium.com/@ondrejmirtes/union-types-vs-intersection-types-fd44a8eacbb)). Představme si například třídu, která pracuje s datem a v konstruktoru přijímá všechny možné formáty (`string`, unix timestamp nebo instanci `DateTime`)

```php
<?php 
function __construct($date) { /* ... */ }
```
 
V tomhle případě nemůžete uvést jako datový typ proměnné `$date` takhle: 

```php
<?php 
function __construct(DateTimeInterface|string|int $date) { /* ... */ }
```

[Alespoň zatím ne](https://wiki.php.net/rfc/union_types). Je potřeba se vrátit zpět k docblockům:  

```diff
+/**
+ * @param DateTimeInterface|string|int $date
+ */
function __construct($date) { /* ... */ }
```

Dalším případem, kde je třeba návrat k docblockům, jsou generika a pole objektů. Databázový dotaz může například vrátit kolekci uživatelů (třeba `MyApp\Entity\Collection`). Pomocí typové deklarace můžeme napsat

```php
<?php
function getUsers(): Collection {}
```

Ale to nepostihne informaci o tom, že uvnitř kolekce jsou uživatelé. Takže nebude fungovat doplňování pro `$collection->first()->???`. Opět se musíme vrátit k docblockům. 

```diff
+/**
+ * @return User[]|Collection
+ */
function getUsers(): Collection {}
```

Tímto způsobem získáme doplňování jak pro `$collection->count()`, tak pro `$collection->first()->getUsername()`. 


## Co když je návratový typ dynamický?  

Továrny a service lokátory vrací různé typy podle toho, s jakým parametrem je zavoláme. Podívejme se na následující kód:

```php
<?php
// není jasné, jakého bude $logger typu
$logger = $container->get('LoggerInterface');
$logger->???
```

Můžeme však napovědět přímo v kódu dokumentačním komentářem.  

```diff
+/** @var LoggerInterface $logger */
$logger = $container->get('LoggerInterface');
$logger->log(/* code completion */);
```

Tento způsob je široce podporovaný a mnoho nástrojů ho dokáže využívat. Ale nezapomeňte, že se stále jedna o přístup založený na komentářích a je tedy snadno možné, že _se při refaktoringu rozuteče oproti kódu a už ho nikdy nikdo neupraví_. 

## A co magické metody?  

```php
<?php
/**
 * @property-read $username
 * @property $name
 * @property-write $password
 * @method void reset()
 * @method static Config factory()
 */
class Config {
    private $config = [
        'username' => 'john', 
        'name' => 'John Doe', 
        'password' => '123456'
    ];
    
    public function __get($property){
        if ($property === 'password') {
            return null; // you can't read password
        }
        return $this->config[$property];
    }
    
    public function __set($property, $value){
        if ($property === 'username') {
			return null; // you can't set username
		}
		return $this->config[$property];
    }
    
    public function __call($method, $arguments){
        if ($method === 'reset') {
            $this->config = [];
        }
    }
    
    public static function __callStatic($method, $arguments){
        if ($method === 'factory') {
            return new self();
        }
    }
}
```

U třídy výše (přiznávám, je to extrémní hovnokód) můžete vidět jednotlivé možnosti, jak se vypořádat s magickými metodami. 

* `@property-read` - znamená, že existuje veřejný atribut, který lze číst. Napovídání pro `$username = $config->username;` bude fungovat, ale pokud zkusíte do proměnné zapsat, PhpStorm vám to označí jako chybu. 
* `@property-write` - znamená, že existuje veřejný atribut, do kterého lze zapisovat. Napovídání `$config->password = 'dummy';` bude fungovat, ale pokud se pokusíte atribut přečíst, PhpStorm to označí jako chybu. 
* `@property` - kombinuje výše zmíněné (čtení a zápis)
* `@method` - znamená, že existuje veřejná metoda a specifikuje její návratový typ. V tomto případě vám PhpStorm napoví `$config->reset()`. 
* `@method static` - znamená, že existuje veřejná statická metoda. V tomto případě `Config::factory()`. Tím pádem bude následně fungovat například napovídání v případě `Config::factory()->reset()`.  

## PhpStorm meta file

Na konec jsem si nechal takovou specialitku - [soubor `.phpstorm.meta.php`](https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Advanced+Metadata). 

```php

<?php
// in .phpstorm.meta.php\myframework.meta.php
namespace PHPSTORM_META {
  override(\ServiceLocator::get(0),
    map([
      'foo' => \FooInterface::class, // když zavolám get('foo'), dostanu FooInterface
      \ToTownInterface::class => \User::class, // když zavolám get(\ToTownInterface::class), dostanu User
      // když zavolám get('AnythingElse'), dostanu AnythingElse (výchozí chování) 
    ])
  );
  
  override(\IteratorGenerator::get(0),
    map([
      // "@" je nahrazen čímkoli, co pošlete jako parametr  
      '' => '@Iterator|\Iterator' // když zavolám get('User'), dostanu UserIterator|Iterator
    ])
  );
}
```

Bohužel je v současnosti možné takto specifikovat jen první parametr volání. Je to čistě omezení současné implementace v PhpStormu. Nicméně [z definice](https://github.com/JetBrains/phpstorm-stubs/blob/master/meta/.phpstorm.meta.php) je vidět, že samotný formát je na to připraven a implementaci je možné do budoucna rozšířit. 

## Závěr

V článku jsme si ukázali různé možnosti, jak napovídat typ proměnné a návratové typy metod. **Zajímá vás, jak dál využívat toho, že PhpStorm vašemu kódu lépe rozumí? Tak si nenechte ujít [školení 25.1.](https://pehapkari.cz/vzdelavej-se/#ovladni-phpstorm-ndash-od-zakladu-po-profi-tipy), kde se dozvíte další užitečné tipy**. 

Napadá vás ještě nějaký další způsob, jak napovídat typy, nebo jsem na něco zapomněl? Napište mi, nebo rovnou pošlete k článku pullrequest.  

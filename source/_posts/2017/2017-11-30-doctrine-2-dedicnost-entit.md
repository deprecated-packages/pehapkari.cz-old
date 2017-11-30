---
id: 48
layout: post
title: "Doctrine 2 – Dědičnost entit"
perex: "Entity v ORM (objektově relační mapování) zachycují objekty z reálného světa a transformují je do tříd v programovacím jazyku. V reálném světě má vždy skupina objektů stejné vlastnosti a liší v některých detailech. Všichni lidé mají jméno, datum narození, pohlaví, ale liší se v barvě očí, pleti, dovednostech apod. "
author: 11
reviewed_by: [1]
---

Při čistém návrhu entit by se nám tedy hodila dědičnost. Umí ji Doctrine zpracovat, a jak s takovou entitou pracuje?


## Entita a její rodič

Ideální bude, když si problematiku rozebereme na konkrétním příkladu. Představme si, že jsme programátor, který programuje blog. Na blogu budou vycházet článku, ale i krátké novinky. Pro jednoduchost ukázek budeme chtít u článku ukládat pouze název, úvodní text (perex), obsah a autora. U novinky budeme chtít ukládat její název, obsah, štítek a autora. Jak budou vypadat Doctrine entity?

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class Article
{
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 * @var int
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $header;
	
	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $perex;
	
	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $content;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $author;
	
	// getters + setters

}
```

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="news")
 */
class News
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $header;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $content;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $tag;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $author;

	// getters + setters
	
}
```

Jak je vidět na první pohled entita `News` obsahuje stejné vlastnosti jako `Article` a liší se pouze ve štítku. V tento moment se nám může zdát jako zbytečné přemýšlet o nějaké dědičnosti. Možná… 

Blogger, pro kterého blog programuje, se rozhodne, že by chtěl u článků a novinek přidat možnost ukládat datum publikace. Takže si jako programátor otevřeme entitu `Article` a přidáme novou vlastnost `$publishedAt` a implementujeme getter + setter. V tento moment bude následovat copy&paste nových věci i do entity `News` a jakožto zkušení programátoři, kteří píší čistý kód, prostě ten Ctrl+C nezmáčkneme. Rozhodneme se to implementovat pořádně tak, jak se sluší od zkušeného programátora.

## Počáteční refaktoring

Zprvu musíme vydefinovat společné vlastnosti, které přeneseme do společného předka a od něj pak budou entity `Article` a `News` dědit.

Když se na entity podíváme, tak je jasné, že se bude jednat o vlastnosti název, obsah a autor. Entita `Article` má oproti entitě `News` navíc perex a `News` zase štítek. Nyní začíná veškerá sranda. :)

Vytvoříme tedy abstraktní třídu `AbstractText`, do které přesuneme vydefinované proměnné.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;


/**
 * @ORM\Entity
 * @ORM\Table(name="text")
 */
abstract class AbstractText
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $header;

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $content;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $author;

	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $publishedAt;

	// getters + setters

}
```

Následně entitu `Article` podědíme od `AbstractText`

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class Article extends AbstractText
{

	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $perex;

	// getter + setter

}
```

a nakonec podědíme od `AbstractText` i entitu `News`.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="news")
 */
class News extends AbstractText
{

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $tag;

	// getter + setter
	
}
```

## Propojení přes komentářové anotace

V tento moment, ještě Doctrine nedokáže pochopit, že chceme použít dědičnost u entit a je potřeba jí to oznámit přes speciální komentářovou anotaci. Otevřeme si tedy `AbstractText` a přidáme anotaci `InheritanceType`, `DiscriminatorColumn` a `DiscriminatorMap`.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="text")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator", type="text")
 * @ORM\DiscriminatorMap({
 * 		"article" = "Article",
 * 		"news" = "News"
 * })
 */
abstract class AbstractText
{
	// zůstává stejné
}
```

V tento moment již Doctrine zná vše potřebné a můžeme si nechat vygenerovat nové schéma pro databázi.

## Databázové schéma

Pro implementaci dědičnosti v relační databázi využívá Doctrine propojení přes primární klíč. Když si dáme vypsat SQL aktuálního databázového schématu, tak bude vypadat následovně.

```sql
// generováno pro PostgreSQL 9.6

CREATE TABLE text (id SERIAL NOT NULL, header VARCHAR(255) NOT NULL, content TEXT NOT NULL, author VARCHAR(255) NOT NULL, published_At TIMESTAMP NOT NULL, discriminator TEXT NOT NULL, PRIMARY KEY(id));
CREATE TABLE article (id INT NOT NULL, perex TEXT NOT NULL, PRIMARY KEY(id));
CREATE TABLE news (id INT NOT NULL, tag VARCHAR(255) NOT NULL, PRIMARY KEY(id));

ALTER TABLE article ADD CONSTRAINT FK_23A0E66BF396750 FOREIGN KEY (id) REFERENCES text (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE news ADD CONSTRAINT FK_1DD39950BF396750 FOREIGN KEY (id) REFERENCES text (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
```

Jak je vidět, tak tabulky `text`, `article` a `news` sdílí hodnotu primárního klíče. V tabulce text je navíc sloupec `discriminator`, do kterého si Doctrine ukládá informaci o tom, která tabulka se je potomkem rodiče. Doctrine zde dbá na maximální výkon a tak při získávání dat z databáze rovnou propojí potřebné tabulky a načte všechna data v jediném SQL dotazu. Takto bude vypadat dotaz pro načtení všech článků.

```SQL
// generováno pro PostgreSQL 9.6

SELECT t1.id AS id_2, t1.header AS header_3, t1.content AS content_4, t1.author AS author_5, t1.publishedAt as published_at_6, t0.perex AS perex_7, t1.discriminator 
FROM article t0 
INNER JOIN text t1 ON t0.id = t1.id;
```

## Závěr

Doctrine nám umožňuje používat dědičnost i na úrovni databáze bez zbytečných hacků a magie. Díky této vlastnosti můžeme navrhovat čisté entity a nemusíme duplikovat stejné vlastnosti v entitách.

## Chci se dozvědět více!

Zajímá tě dědičnost entit více do hloubky nebo se chceš přiučit jiným cool věcem? Přihlaš se na má [školení Doctrine 2](https://pehapkari.cz/vzdelavej-se/) právě teď! :)

Zde jsou materiály, které ti pomohou pochopit, jak to v Doctrine funguje hlouběji.

 - http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
 - http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#annref-discriminatormap

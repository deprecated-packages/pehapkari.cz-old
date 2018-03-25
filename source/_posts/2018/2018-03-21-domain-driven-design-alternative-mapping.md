---
id: 65
layout: post
title: "Domain-Driven Design, part 7 - Alternative Relational Database Mapping"
perex: '''
 Do you think that multilingual text must always be in a separate database table? Than this article is for you!
 
 We will show that not all arrays have to be mapped as database tables.
 And we will also show the Doctrine implementation.
'''
author: 29
lang: en
related_items: [49, 52, 54, 61, 62, 63, 66]
---

## Product Name Story

We worked on an e-shop and the product had exactly one name.
One day we faced confronted with the reality - the e-shop has to be multilingual.
I imagined a new database table for every language field immediately, and I was disgusted by all the tables that had to be created.
But luckily I asked myself - *What is the domain?*

### Use cases

The domain can be extracted from stories and use cases.
We discussed this in our team and we extracted a couple of use cases:

* Change the product name in the language.
* Show the product name in the language
  * If the name in language is missing, show an empty string

We don’t have a use case to remove a name.
The product could exist without a name anyway, so creating one wasn't a problem.

## LangValue Extraction

Use cases were the same for all names or descriptions in the system that had to be multilingual.
So we decided to extract use cases and responsibility into a common LangValue.

LangeValue is a multilingual text, this is its responsibility.
It has to be able to change the text in the language and read the text in the language.
It doesn't have an identity - LangValue is identified by all its properties, it is a [value object](/blog/2018/01/06/domain-driven-design-simplify-object-model#value-objects).
We decided that language identifier can be any string.

```php
class LangValue {

    /**
     * @var string[] [string => string]
     */
    private $strings = [];

    public function __construct(array $strings = []) {
        foreach ($strings as $lang => $string) {
            $this->strings[$lang] = (string) $string;
        }
    }

    public function cloneWith(string $lang, string $string): self {
        $all = $this->all();
        $all[$lang] = $string;
        return new self($all);
    }

    public function read(string $lang): string {
        return $this->strings[$lang] ?? '';
    }

    public function all(): array {
        return $this->strings;
    }
}
```

The implementation of the multilingual product name is simple.
The class is simplified to be instructive.

```php
class Product {
    /**
     * @var LangValue
     */
    private $name;

    public function __construct()
    {
        $this->name = new LangValue();
    }

    public function changeName(string $language, string $name): void {
        $this->name = $this->name->cloneWith($language, $name);
    }

    public function showName(string $language): string {
        return $this->name->read($language);
    }
}
```

## Relational Database Storage

The LangValue works great in objects but how to save it into a relational database?
Well, we could think that text in language must be in a separate table.
That would be difficult to map, even impossible without changing the domain model.

Let's take a step back.
What are our use cases? We have to be able to change the text in the language and read it in the given language.
This is what our class LangValue does by itself.

Do we have the use case that needs language text in a separated table?
I don't think so.
So what if the LangValue would be serialized in a single field?
Does it cause problems?
It could be difficult to find a product by name in the language, but we do not have such a use case.
So the serialization can be a good option.

> **Disclaimer**
>
> In a real project we have the finding use case.
> We have a dedicated database with a special structure for a quick search.

### Serialization

We decided to use LangValue serialization.

We can serialize it by PHP `serialize` function or extract the internal array and serialize it.
Both styles are similar - they end up with PHP structure encoded in a string that is not readable by people and by databases.

We can use custom serialization method, but that would probably be a horrible choice.

We can also serialize the LangValue into a JSON.
It is readable by people and some databases and can work with JSON data type too.
This seems to be a reasonable choice.

## LangValue Doctrine Mapping

We need to convert the LangValue to the JSON and vice versa.

Doctrine supports custom mapping type for this particular task.
Once the custom type is registered and mapped, all objects are mapped correctly to the database.
The custom type is registered in DBAL layer, so even when we hydrate arrays instead of objects, text is hydrated as LangValue object.
When we hydrate scalars, we get raw data from the database - string JSONs.

If the target database supports JSON data type, the Doctrine works with it.
Otherwise, it uses the text data type.

```php
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LangValueType extends Type {

    const LANG_VALUE = 'langValue';

    public function getName() {
        return self::LANG_VALUE;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform) {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform) {
        return !$platform->hasNativeJsonType();
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if ($value === NULL) {
            return NULL;
        }

        return json_encode($value->all(), JSON_UNESCAPED_UNICODE);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform) {

        if ($value === NULL || $value === '') {
            return new LangValue;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        return new LangValue(json_decode($value, TRUE));
    }
}
```

#### Registration

We have to add code to the Doctrine integration.

```php
\Doctrine\DBAL\Types\Type::addType(LangValueType::LANG_VALUE, LangValueType::class);

/** @var Doctrine\DBAL\Connection */
$connection = ...;
$platform = $connection->getDatabasePlatform();
$platform->registerDoctrineTypeMapping(LangValueType::LANG_VALUE, LangValueType::LANG_VALUE);
```

#### Mapping

```xml
<doctrine-mapping>
    <entity name="Product">
        <field name="name" type="langValue" />
    </entity>
</doctrine-mapping>
```

## Database Search in JSON

A relational database isn't usually a good choice for a full-text search.
But some databases allow us to query the JSON data type anyway.

If we want to search using JSON, we can always write a native query.
Or we can create a custom DQL function that is translated into engine-specific JSON query.

### PostgreSQL

```postgresql
SELECT * FROM Product where name->>'cs' LIKE '%query%';
```

### MySQL

```mysql
SELECT * FROM Product where name->'$.cs' LIKE '%query%';
```

## TL;DR

Think before mapping.
Even if you are experienced, you can find some new and creative mapping styles.
Focus on use cases, do the object model first and think about relations and mapping later.
JSON object serialization is useful.

## References

DOCTRINE TEAM.
*Custom Mapping Types - Doctrine 2 ORM 2 documentation* [online].
2018 [2018-03-14].
Available: [http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/custom-mapping-types.html](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/custom-mapping-types.html)


## Contact

Are you designing architecture and like DDD approach? Hire me, I can help you - [svatasimara.cz](http://svatasimara.cz/)

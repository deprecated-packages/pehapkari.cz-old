---
layout: post
title: "Jak zaregistrovat Nette komponentu jako posluchače událostí"
perex: "Dnes si povíme o tom, jak přimět Nette <strong>komponenty poslouchat na události</strong>, které nám vyvolává aplikace a umožnit jim se podle toho zachovat."
author: 11
tested: true
test_slug: ListeningNetteComponents
---


## Úvod do problému

U složitějších aplikací může dojit k tomu, že v jednom presenteru máte vloženo více komponent, které mají mezi sebou pomyslnou vazbu. Pokud se stane něco v jedné komponentě, tak je potřeba překreslit (ajaxově snippetem) komponentu druhou apod. Typicky u eshopů se jedná o ten use-case, že při přidání položky do košíku potřebuji překreslit ten malý košík s cenou umístěný většinou v pravo nahoře a má mi vyskočit nějaký popup s podobnými produkty.

**Jak elegantně překreslovat komponenty** v závislosti na ajaxových požadavcích? Řešením je použití událostí a jejich posluchačů.


## Náš CategoryPresenter

Mějmě například takovýto presenter:


```php
// Presenter/CategoryPresenter.php

use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Component\AddToBasketControl\AddToBasketControl;
use Component\AddToBasketControl\AddToBasketControlFactoryInterface;
use Component\BasketContentControl\BasketContentControl;
use Component\BasketContentControl\BasketContentControlFactoryInterface;


final class CategoryPresenter extends Presenter
{

    const PRODUCTS = [
        [
            'id' => 1,
            'name' => 'T-Shirt',
            'price' => 100
        ],
        [
            'id' => 2,
            'name' => 'Red socks',
            'price' => 29
        ],
        [
            'id' => 3,
            'name' => 'Green hat',
            'price' => 99
        ]
    ];

    /**
     * @var AddToBasketControlFactoryInterface
     */
    private $addToBasketControlFactory;

    /**
     * @var BasketContentControlFactoryInterface
     */
    private $basketContentControlFactory;


    public function __construct(
        AddToBasketControlFactoryInterface $addToBasketControlFactory,
        BasketContentControlFactoryInterface $basketContentControlFactory
    ) {
        $this->addToBasketControlFactory = $addToBasketControlFactory;
        $this->basketContentControlFactory = $basketContentControlFactory;
    }


    public function renderDefault()
    {
        $this->template->setParameters([
            'products' => self::PRODUCTS
        ]);
    }


    protected function createComponentAddToBasket(): Multiplier
    {
        // Musíme použít Multiplier, protože potřebujeme samostatnou instanci pro každý produkt.
        // Co je to Multiplier? Více informací najdeš ve článku https://pla.nette.org/cs/multiplier.

        return new Multiplier(function($productId) {
            $product = [];
            foreach (self::PRODUCTS as $productData) {
                if ($productData['id'] === (int) $productId) {
                    $product = $productData;
                    break;
                }
            }

            return $this->addToBasketControlFactory->create($product);
        });
    }


    protected function createComponentBasketContent(): BasketContentControl
    {
        return $this->basketContentControlFactory->create();
    }

}
```

V presenteru `CategoryPresenter` máme zaregistrované komponenty `AddToBasketControl` a `BasketContentControl`. Komponenta `AddToBasketControl` bude sloužit pro přidání produktu do košíku a komponenta `BasketContentControl` nám bude vypisovat produkty v košíku. Naším cílem bude po přidání produktu do košíku v komponentě `AddToBasketControl` překreslit komponentu `BasketContentControl` pomocí událostí.


## Nástroje

Budeme potřebovat [Symfony\EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher.html) (základní infromace o něm můžete načerpat z článku [Event Dispatcher from the Scratch](https://pehapkari.cz/blog/2016/12/05/symfony-event-dispatcher/)). To je vše! :)


## Napojíme `Symfony\EventDispatcher` do Nette DI

Vždy jsem si myslel, že **propojit Symfony a Nette** nejde nebo je to velmi složité. No - složité to není, takže s chutí do toho!

Spustíme příkaz `composer require symfony/event-dispatcher` a následně zaregistrujeme `EventDispatcher` do `Nette/DI`.

```yaml
// config.neon

services:
    - Symfony\Component\EventDispatcher\EventDispatcher
```

Propojení Nette a Symfony máme hotové. Tak co jsem říkal, je to složité? :)

V tuto chvíli máme vše co potřebujeme. Máme myšlenku toho co chceme udělat a všechny potřebné nástroje, takže **jdeme na to**!


## Registrace komponenty jako posluchače do EventDispatcheru

Zde se nám hodí znát jaký má Presenter v Nette [životní cyklus](https://doc.nette.org/cs/2.4/presenters#toc-zivotni-cyklus-presenteru). Pro náš počin se výborně hodí metoda `startup()`. Při jejím volání je již presenter nakonfigurován a tak máme přístup ke komponentám. V metodě `startup()` tedy řekneme `EventDispatcher`u, které komponenty si má zaregistrovat jako posluchače.

Do našeho presenteru `CategoryPresenter` tedy přidáme metodu `startup()` a zároveň si přidáme závislost na `EventDispatcher`.

```php
// Presenter/CategoryPresenter.php

...

/**
 * @var EventDispatcherInterface
 */
private $eventDispatcher;


public function __construct(
    AddToBasketControlFactoryInterface $addToBasketControlFactory,
    BasketContentControlFactoryInterface $basketContentControlFactory,
    EventDispatcherInterface $eventDispatcher
) {
    $this->addToBasketControlFactory = $addToBasketControlFactory;
    $this->basketContentControlFactory = $basketContentControlFactory;
    $this->eventDispatcher = $eventDispatcher;
}


public function startup()
{
    parent::startup();

    // Magic goes here!
}

...
```

`EventDispatcher` pro přidání nových posluchačů na události používá metodu [addListener](http://symfony.com/doc/current/components/event_dispatcher.html#connecting-listeners), která má dva parametry. První parametrem je název události, na kterou posluchač poslouchá a druhým parametrem je callback, který se zavolá při vyvolání události. Docela jednoduché API, že? :)

Přes nově nabyté znalosti tedy **vytvoříme událost**, kterou bude vyvolávat komponenta `AddTobasketControl` a zaregistrujeme komponentu `BasketContentControl` do `EventDispatcher`u jako posluchače této události.

```php
// Event/ProductAddedToBasketEvent.php

use Symfony\Component\EventDispatcher\Event;


final class ProductAddedToBasketEvent extends Event
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $price;


    public function __construct(int $id, string $name, int $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }


    public function getId(): int
    {
        return $this->id;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getPrice(): int
    {
        return $this->price;
    }

}
```

```php
// Presenter/CategoryPresenter.php

use Event\ProductAddedToBasketEvent;

...

public function startup()
{
    parent::startup();

    $basketContentControl = $this->getComponent('basketContent');

    $this->eventDispatcher->addListener(
        ProductAddedToBasketEvent::class,
        [$basketContentControl, 'onProductAddedToBasketEvent']
    );
}
...
```

## Vyvolání události

Tak už máme `EventDispatcher` napojený do Nette. Také máme komponentu `BasketContentControl` zaregistrovanou jako posluchače události `ProductAddedToBasketEvent`. Takže je na řadě **samotné vyvolání události**.

To se udělá opět velmi snadno - konkrétně přes metodu [dispatch](http://symfony.com/doc/current/components/event_dispatcher.html#dispatch-the-event), která je nečekaně součástí `EventDispatcher`u. Metoda má opět dva parametry. První parametr je název události, která se bude vyvolávat (na tento název jsou zaregistrováni posluchači). Druhý parametr je samotná instance události, přes kterou můžete předávat data do posluchačů.

Dost teorie - **chci vyvolat svoji událost**!

```php
// Control/AddToBasketControl/AddToBasketControl.php

use Nette\Application\UI\Control;
use Event\ProductAddedToBasketEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


final class AddToBasketControl extends Control
{

    /**
     * @var array
     */
    private $product;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    public function __construct(array $product, EventDispatcherInterface $eventDispatcher)
    {
        $this->product = $product;
        $this->eventDispatcher = $eventDispatcher;
    }


    public function handleAdd()
    {
        // Zde může být nějaká složitější logika
        // např.: $this->basketFacade->addProduct($this->product);

        // vytvoříme instanci události
        $productAddedToBasketEvent = new ProductAddedToBasketEvent(
            $this->product['id'],
            $this->product['name'],
            $this->product['price']
        );
        $this->eventDispatcher->dispatch(ProductAddedToBasketEvent::class, $productAddedToBasketEvent); // vyvoláme událost!
    }


    public function render()
    {
        $this->template->render(__DIR__ . '/templates/default.latte');
    }

}
```

Pokud někdo klikne na link vedoucí do `handleAdd` metody, tak bude vyvolána událost `ProductAddedToBasketEvent`, na kterou čeká a poslouchá naše druhá komponenta `BasketContentControl`. Komponenta `BasketContentControl` může vypadat následovně.

```php
// Control/BasketContentControl/BasketContentControl.php

use Nette\Application\UI\Control;
use Event\ProductAddedToBasketEvent;


final class BasketContentControl extends Control
{

    /**
     * @var array
     */
    private $products = [];


    // Tuto metodu zavolá EventSubscriber, protože je nastavena jako listener callback v CategoryPresenter::startup()
    public function onProductAddedToBasketEvent(ProductAddedToBasketEvent $productAddedToBasketEvent)
    {
        $product = [
            'id' => $productAddedToBasketEvent->getId(),
            'name' => $productAddedToBasketEvent->getName(),
            'price' => $productAddedToBasketEvent->getPrice(),
        ];

        $this->products[] = $product;

        $this->redrawControl('content');
    }


    public function render()
    {
        $this->template->setParameters([
            'products' => $this->products
        ]);

        $this->template->render(__DIR__ . '/templates/default.latte');
    }

}
```

## Oddechneme si u šablon

> Tato sekce přímo nesouvisí s tím, jak registrovat komponentu jako posluchače, ale pro náš příklad je stejně tak důležitá jako kterákoliv předchozí sekce.

V šabloně presenteru si vykreslíme komponentu `BasketContentControl` a vypíšeme seznam produktů.


```html
<!-- /templates/Category/default.latte -->

❴control basketContent❵
<table>
    <tr n:foreach="$products as $product">
        <td>❴$product['id']❵</td>
        <td>❴$product['name']❵</td>
        <td>❴$product['price']❵</td>
        <td>❴control 'addToBasket-' . $product['id']❵</td>
    </tr>
</table>
```

Následuje šablona pro vykreslení odkazu pro přidání produktu do košíku.

```html
<!--Component/AddToBasketControl/templates/default.latte -->

<a n:href="add!" class="ajax">Přidat do košíku</a>
```

 - Pro ajaxovou funkčnost použijeme javascriptovou knihovnu [nette.ajax.js](https://componette.com/vojtech-dobes/nette.ajax.js/).

A do třetice je tu šablona pro vykreslení obsahu košíku.


```html
<!-- Component/BasketContentControl/templates/default.latte -->

❴snippet content❵
    <table>
        <tr n:foreach="$products as $product">
            <td>❴$product['id']❵</td>
            <td>❴$product['name']❵</td>
            <td>❴$product['price']❵</td>
        </tr>
    </table>
❴/snippet❵
```

Nyní máme vše hotové a **můžeme spustit aplikaci**!


### Existuje i jiné řešení bez událostí?

Samozřejmě! Stačí upravit metodu `handleAdd` v `AddToBasketControl` například takto:

```php
public function handleAdd()
{
    $this->presenter->getComponent('basketContent')->onProductAddedToBasket($this->product);
}
```

U tohoto řešení je problém v tom, že **komponenta** `addToBasketControl` **zná implementaci presenteru**, ve kterém je připojena a spoléhá na to, že je v něm zaregistrovaná componenta s názvem `basketContent`. Pokud bych tedy chtěl komponentu `addToBasketControl` použít v jiném presenteru, musel bych v něm zaregistrovat i komponentu `BasketContentControl`, což je nehezké provázání závislostí.

Co pak, když by bylo potřeba, aby na událost `ProductAddedToBasket` poslouchala i jiná komponenta? OK - upravíme metodu na:


```php
public function handleAdd()
{
    $this->presenter->getComponent('basketContent')->onProductAddedToBasket($this->product);
    $this->presenter->getComponent('anotherComponent')->onProductAddedToBasket($this->product);
}
```

a už tu vzniká programming hell a **programátorský dluh do budoucnosti**.

Druhý mnohem složitější problém by nastal v momentě, kdy se událost nevyhazuje v komponentě, ale v nějaké službě. Typicky můžeme mít `BasketFacade`, která před přidáním produktu do košíku musí zvalidovat např. to, zda může být produkt přidán do košíku a pokud ano, tak produkt přidá a vyvolá událost. Pak nám nezbývá  nic jiného než použití `return` pokud bychom chtěli událost přeci jen vyvolávat v komponentě. Problém může být, ale pokud `BasketFacade` deleguje požadavek na přidání produktu jiné službě apod. Pak musíme `return`ovat `return`y z celého řetezce volaných metod a to je pěkný oser. :)


## Shrnutí

Ukázali jsme si jak jednoduše se dá propojit Symfony s Nette a **jak přimět komponenty poslouchat na události**. Zároveň jsme si osvěžili práci se ajaxem, snippety a vysvětlili jsme si, jak fungují **události a posluchači**.

Jaký to dobrý pocit z nově nabytých znalostí! :)


## Chceš znát více?

Zde jsou linky pro zvídavé programátory/ky:
 - https://doc.nette.org/cs/2.4/presenters
 - https://doc.nette.org/cs/2.4/presenters#toc-presenter-a-komponenty
 - http://symfony.com/doc/current/components/event_dispatcher.html
 - https://github.com/nette/di
 - https://doc.nette.org/cs/2.4/ajax

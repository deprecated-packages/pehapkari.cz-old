---
layout: post
title: "Event Dispatcher from the Scratch"
perex: "Dnes se spolu podíváme na EventDispatcher. Jde o komponentu, která dodá tvému kódu flexibilitu. Zároveň je jednou z nejdůležitějších součástek životního cyklu Symfony. Když pochopíš EventDispatcher, budeš zase o kousek blíž k tomu stát se opravdovým mistrem Symfony."
author: 1
series: 1 
tested: true
id: 2
lang: en
---

## Co ti EventDispatcher umožní?

Dostat se na určité místo v kódu bez nutnosti jeho změny
Zvýšit flexibilitu a použitelnost tvé aplikace


## Hlavní pojmy

### Event

…neboli událost. Jde o něco, co může nastat při běhu aplikace. Typickým příkladem je objednávka. Když dojde k odeslání objednávky, tak se zavolá Event. Na Event odeslání objednávky pak může slyšet několik EventSubscriberů.

### EventSubscriber
…může poslat e-mail adminovi, přičíst kredity za úspěšný nákup, nebo poslat informační sms do skladu s pokynem k zabalení tvých vánočních dárků.

### EventDispatcher
…ten se stará o zavolání EventSubscriberů, když nastane určitý Event.




## Jak to aplikovat v kódu?
Symfony\EventDispatcher nainstaluješ pomocí Composeru:

```language-bash
composer require symfony/event-dispatcher
```

Vytvoříš si soubor `index.php`:

```language-php
require_once __DIR__ . '/vendor/autoload.php';

$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
// dispatchneme event někde v kódu na konci objednávky 
$eventDispatcher->dispatch('order.finish');
```

A spustíš:

```language-bash
php index.php
```

Dispatchneš Event, ale nic se nestane. Aby se něco stalo, bude potřeba ještě EventSubscriber – ten bude naslouchat na order.finish.

Přidáš tedy EventSubscriber:

```language-php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendEmailToAdminEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var int
     */
    public $signal = 0;

    public static function getSubscribedEvents()
    {
        // tady budeme poslouchat "order.finish" event
        // a pokud nastane, použijeme metodu sendEmailToAdmin()
        return ['order.finish' => 'sendEmailToAdmin'];
    }

    public function sendEmailToAdmin()
    {
        // náš kód, který pošle e-mail adminovi
        $this->signal = 1;
    }
}
```

Nakonec přidáš `EventSubscriber` do `EventDispatcheru`:

```language-php
$sendEmailToAdminEventSubscriber = new SendEmailToAdminEventSubscriber;

$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
$eventDispatcher->addSubscriber($sendEmailToAdminEventSubscriber);

var_dump($sendEmailToAdminEventSubscriber->signal);

$eventDispatcher->dispatch('order.finish');

var_dump($sendEmailToAdminEventSubscriber->signal);
```

A opět spustíš:

```language-php
$ php index.php
int(0)
int(1)
```

Teď, když se ti dispatchne `order.finish` Event, zavolá se každý EventSubcriber, který se k němu zapsal. V něm se zavolá metoda, která je k němu přiřazena. Dojde tak ke změně `$signal` z `0` na `1`.

Pro tip: Metoda getSubscribedEvents() může naslouchat více Eventům, více metodami. Může také určovat jejich pořadí.

Nyní už rozumíš Symfony komponentě EventDispatcher.


### Event s argumenty

Při volání události obvykle potřebuješ předat i nějaká data. Například číslo objednávky. Taková třída Event je vlastně pouhý Value object – schránka na data.

```language-php
use Symfony\Component\EventDispatcher\Event;

final class OrderEvent extends Event
{
    /**
     * @var int
     */
    private $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId() : int
    {
        return $this->orderId;
    }
}
```

Dispatchneš event i s potřebnými daty.

```language-php
$orderEvent = new OrderEvent(123);
$eventDispatcher->dispatch('order.finish', $orderEvent);
```

Rozšíříš EventSubscriber o OrderEvent:

```language-php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SendEmailToAdminEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var int
     */
    public $signal = 0;

    public static function getSubscribedEvents() : array
    {
        return ['order.finish' => 'sendEmailToAdmin'];
    }

    public function sendEmailToAdmin(OrderEvent $orderEvent)
    {
        $this->signal = $orderEvent->getOrderId();
    }
}
```


A doplníš svůj výsledný kód:

```language-php
$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
$sendEmailToAdminEventSubscriber = new SendEmailToAdminEventSubscriber;
$eventDispatcher->addSubscriber($sendEmailToAdminEventSubscriber);

var_dump($sendEmailToAdminEventSubscriber->signal);

$orderEvent = new OrderEvent(123);
$eventDispatcher->dispatch('order.finish', $orderEvent);

var_dump($sendEmailToAdminEventSubscriber->signal);
```

Výstup pak vypadá takto:

```language-bash
$  php index.php
int(0)
int(123)
``` 

## Jsi zase o krok dál

Teď už:

- rozumíš základním workflow událostí
- znáš pojmy Event, EventSubscriber a EventDispatcher
- víš, k čemu využít vlastní Event objekt
- …a umíš použít EventDispatcher prakticky ve svém kódu
 

### Potřebuješ víc?

Pokud bys potřeboval jít ve vysvětlování do větší hloubky, mrkni na oficiální dokumentaci EventDispatcheru.

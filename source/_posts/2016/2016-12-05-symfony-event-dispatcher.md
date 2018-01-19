---
id: 5
layout: post
title: "Event Dispatcher from the Scratch"
perex: "Today we will look at first Symfony component - Event Dispatcher. Why should you start with it? It gives you flexibility, it is easy to understand and it helps you to write decoupled code."
author: 1
tested: true
test_slug: EventDispatcher
lang: en
related_items: [35]
---


## Main feature of Event Dispatcher

- **Extend application** in some place **without putting any code right there**.

This way you can extend 3rd party packages without rewriting them. And also allow other users to extend your code without even touching it.

Not sure how that looks? You will - in the end of this article.


### Event Dispatcher

**This is the brain**. It stores all subscribers and calls events when you need to.


### Event

**This is name of a place**. When something has happened in application: *order is sent*,
or *user is deleted*.


### Event Subscriber

This is **the action that happens when** we come to some place. When order is sent (= Event), *send me a confirmation sms* (= Event Subscriber). And *check that all the ordered products are on stock*. This means, that **1 event can invoke MORE Event Subscribers**.


## Create First Subscriber in 3 Steps


### 1. Install via Composer

```bash
composer require symfony/event-dispatcher
```


### 2. Create Event Dispatcher

```php
// index.php
require_once __DIR__ . '/vendor/autoload.php';

// 1. create the Dispatcher
$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;

// 2. some event happend, we dispatch it
$eventDispatcher->dispatch('youtube.newVideoPublished'); // oh: event is just a string
```

Try it:

```bash
php index.php
```

Wow! Nothing happened...

That's ok, because there is no Subscriber. So let's...


### 3. Create and Register Subscriber

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NotifyMeOnVideoPublishedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    public $isUserNotified = false;

    public static function getSubscribedEvents() : array
    {
        // in format ['event name' => 'public function name that will be called']
        return ['youtube.newVideoPublished' => 'notifyUserAboutVideo'];
    }

    public function notifyUserAboutVideo()
    {
        // some logic to send notification
        $this->isUserNotified = true;
    }
}
```

Let the Dispatcher know about the Subscriber.

```php
$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
$eventSubscriber = new NotifyMeOnVideoPublishedEventSubscriber;
$eventDispatcher->addSubscriber($eventSubscriber);

// nothing happened, default value
var_dump($eventSubscriber->isUserNotified);

// this calls our Subscriber
$eventDispatcher->dispatch('youtube.newVideoPublished');

// now it's changed
var_dump($eventSubscriber->isUserNotified);
```

Run the code again from command line:

```php
$ php index.php
int(0)
int(1)
```

And now you understand EventDispatcher. At least for ~60 % cases.

---

Still on? Let's get advanced.

What if we need to get the name of the Youtuber into the Subscriber?


## Event Objects to the Rescue!

The Event objects are basically [Value Objects](http://richardmiller.co.uk/2014/11/06/value-objects/). Pass a value in constructor and get it with getter.


### 1. Create an Event Object

```php
use Symfony\Component\EventDispatcher\Event;

final class YoutuberNameEvent extends Event
{
    /**
     * @var string
     */
    private $youtuberName;

    public function __construct(string $youtuberName)
    {
        $this->youtuberName = $youtuberName;
    }

    public function getYoutuberName() : string
    {
        return $this->youtuberName;
    }
}
```


### 2. Use Event Object in Event Subscriber

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NotifyMeOnVideoPublishedEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return ['youtube.newVideoPublished' => 'notifyUserAboutVideo'];
    }

    // Event Object is passed as method argument
    public function notifyUserAboutVideo(YoutuberNameEvent $youtuberNameEvent)
    {
        var_dump($youtuberNameEvent->getYoutuberName());
    }
}
```


### 3. Create an Object and Dispatch With It

```php
$youtuberNameEvent = new YoutuberNameEvent('Jirka Král');
$eventDispatcher->dispatch('youtube.newVideoPublished', $youtuberNameEvent);
```

And here is the result:

```php
$ php index.php
string('Jirka Král')
```


## We Are 1 Step Further Now

You can now:

- understand basic Event workflow
- know what EventDispatcher and EventSubscriber are for
- and know how to pass parameters via Event object

### Where to go next?

Still hungry for knowledge? Go check [Symfony documentation](http://symfony.com/doc/current/components/event_dispatcher.html) then.

But remember: **practise is the best teacher**.

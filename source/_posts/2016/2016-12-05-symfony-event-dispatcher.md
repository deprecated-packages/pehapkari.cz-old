---
layout: post
title: "Event Dispatcher from the Scratch"
perex: "Today we will look at first Symfony component - Event Dispatcher. Why should you start with it? It gives you flexibility, it is easy to understand and it helps you to write decoupled code."
author: 1
series: 1 
tested: true
id: 2
lang: en
---


## Main feature of Event Dispatcher 

- **Extend application** in some place **without putting any code right there**.

This way you can extend 3rd party packages without rewriting them. And also allow other users to extends your code without event touching it.

Not sure how that looks? You will in the end of this article.


### Event Dispatcher

**This is the brain**. It stores all subscribers and calls events when you need to. 


### Event

**This is name of a place**. When something has happened in application: *order is sent*, 
or *user is deleted*.     


### Event Subscriber

This is **the action that happens when** we come to some place. When order is sent (= Event), *send me a confirmation sms* (= Event Subscriber). And *check storage they have all the ordered products we need*. This means, that **1 event can invoke MORE Event Subscribers**.


## Create First Subscriber in 3 Steps 


### 1. Install via Composer

```language-bash
composer require symfony/event-dispatcher
```


### 2. Create Event Dispatcher

```language-php
// index.php
require_once __DIR__ . '/vendor/autoload.php';

// 1. create the Dispatcher
$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;

// 2. some event happend, we dispatch it 
$eventDispatcher->dispatch('youtube.newVideoPublished'); // oh: event is just a string
```

Try it:

```language-bash
php index.php
```

Wow! Nothing happened...

That's ok, because there is no Subscriber. So let's...
 

### 3. Create and Register Subscriber

```language-php
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

```language-php
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

```language-php
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

```language-php
use Symfony\Component\EventDispatcher\Event;

final class YoutuberNameEvent extends Event
{
    /**
     * @var string
     */
    private $youtubeName;

    public function __construct(string $youtubeName)
    {
        $this->youtubeName = $youtubeName;
    }

    public function getYoutubeName() : string
    {
        return $this->youtubeName;
    }
}
```


### 2. Use Event Object in Event Subscriber

```language-php
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
        var_dump($youtuberNameEvent->getYoutubeName());    
    }
}
```


### 3. Create an Object and Dispatch With It

```language-php
$youtuberNameEvent = new YoutuberNameEvent('Jirka Král');
$eventDispatcher->dispatch('youtube.newVideoPublished', $youtuberNameEvent);
```

And Results Like That:

```language-php
$ php index.php
string('Jirka Král')
``` 


## Now Are 1 Step Further

Now you:

- understand basic Event workflow
- know what EventDispatcher and EventSubscriber are for
- and know how to pass parameters via Event object

### Where to go next?

Still hungry for knowledge? Go check [Symfony documentation](http://symfony.com/doc/current/components/event_dispatcher.html) then. 

But remember: **practise is the best teacher**.

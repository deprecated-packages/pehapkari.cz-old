---
layout: post
title: "Event Dispatcher from the Scratch"
perex: "Today we look at first Symfony component - Event Dispatcher. Why start with it? It gives you flexibility, it is easy to understand and it helps you to write decoupled code."
author: 1
series: 1 
tested: true
id: 2
lang: en
---


## 2 main Features of Event Dispatcher 

- **Get to some place** in complex application without putting any code there.
- **Add endpoint to your application**, where others can easily extend it without modification.
 

### Event Dispatcher

**This is the brain**. It stores all subscribers and calls events when you need to. 


### Event

**This is name of a place**. When something has happened in application: *order is sent*, 
or *user is deleted*.     


### Event Subscriber

This **the action that happens** when we come to some place. When order is sent, *send me a confirmation sms*. And check storage they have all the ordered products we need. This means, that 1 event can invoke MANY different Event Subscribers.


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
        // in format ['event.name' => 'public function name that will be called']
        return ['youtube.newVideoPublished' => 'notifyUserAboutVideo'];
    }

    public function notifyUserAboutVideo()
    {
        // some logic to send notification
        $this->isUserNotified = true;
    }
}
```

And add Subscriber to Dispatcher. Without that, he doesn't know about it.

```language-php
$eventDispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
$notifyMeOnVideoPublishedEventSubscriber = new NotifyMeOnVideoPublishedEventSubscriber;
$eventDispatcher->addSubscriber($notifyMeOnVideoPublishedEventSubscriber);

// nothing happened, default value
var_dump($notifyMeOnVideoPublishedEventSubscriber->isUserNotified);

// this calls our Subscriber
$eventDispatcher->dispatch('youtube.newVideoPublished');

// now it's changed
var_dump($notifyMeOnVideoPublishedEventSubscriber->isUserNotified);
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

The Event objects are basically Value Objects. Pass a value in constructor a get with getter.


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


### 3. Create and Object and Dispatch With It

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

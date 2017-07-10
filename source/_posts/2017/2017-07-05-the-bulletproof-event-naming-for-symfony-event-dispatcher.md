---
layout: post
title: "The Bulletproof Event Naming For Symfony\EventDispatcher"
perex: '''
    ...
'''
author: 1
lang: en
---

I wrote intro about [Symfony\EventDispatcher](/blog/2016/12/05/symfony-event-dispatcher/) and how to dispatch events and their Event classes.

When it comes to dispatching events, you can choose from many ways to do it.
Today I will show you all their advantages and disadvantages. Follow me and think of the one you like the best. 

## 3 Ways to Dispatch Event     
    
You can start with simple *string named event*: 

```php
$postEvent = new PostEvent($post);
$this->eventDispatcher('post_added', $postEvent)
```

This is simple for start and easy to use for one place and one event. I started with this and it was nice and easy.

I started to use in in more places like this:     

```php
$postEvent = new PostEvent($post);
$this->eventDispatcher('post_add', $postEvent)
```

All looked good, but the subscriber didn't work. Quite fun to debug subscribers, right?

Over hour have passed and I still could find the issue. Event subscriber was registered as services, tagged, collected by dispatcher... When I was really despreated, I showed it to collegaue of mine. 

"Oh, you've got: "post_add" there, there should be "post_added".

I simply copied the previous subscriber with "post_added" but made a typo in event dispatching.

There must be a cure for this. I looked for way how others do it.


## Group File with Events Names as Constants

Then I got inspired by Symfony [`ConsoleEvents` class](https://github.com/symfony/symfony/blob/d203ee33954f4e0c5b39cdc6224fe4fb96cac0c3/src/Symfony/Component/Console/ConsoleEvents.php) that collects all events from one domain ("Console" here).

### Why you should use it?

- all events in one place
- easy to orientate for new programmer

```php
final class OrderEvents
{
    /**
     * This event is invoked when order start.
     * It is called here @see \App\Order\OrderService::start().
     * And @see \App\Events\OrderStartEvent class is passed.
     *
     * @var string
     */
    public constant ON_ORDER_START = 'order.start';
    
    /**
     * This event is invoked when order is finished.
     * It is called here @see \App\Order\OrderService::end().
     * And @see \App\Events\OrderEndEvent  class is passed.
     */
    public constant ON_ORDER_FINISHED = 'order.finished';
}
```

Also subscriber becase typo-proof:

```php
class TagPostSubscriber implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [PostEvent::class => 'tagPost'];
    }
}
```


### Why not 

- stringly over strongly - I have to type the "order.start"
- Breaks open-closed principle, to add new event I have to put it here as well
    - possiblity of skpping this nad using event directly in with own string
- i have to come up with expalnation over constant and where is sued and link the evnet classes (correctly, e.g. `EventClass` doesn'T work in PHPStorm to click trough, but `@see EventClass` does)
- the more events you have the harder is this to maintain properly, 
    - with 5th event you might end up like this
    
    ```php
    final class OrderEvents
    {
        /**
         * This event is invoked when order is finished.
         * It is called here @see \App\Order\OrderService::end().
         * And @see \App\Events\OrderEndEvent  class is passed.
         */
        public constant ON_ORDER_FINISHED = 'order.finished';
        
        // 3 more clearly annotated events, than this...
      
        public constant ON_ORDER_CHANGED = 'changed';
    }```



I wanted to respect open-closed principle, so global class was a no-go.

Maybe I could put...

## Constant names in Events

```php 
final class PostEvent
{
    /**
     * @var string
     */
    public const NAME = 'post_added';

    /**
     * @var Post
     */
    private $post;
    
    public function __construct(Post $post) 
    {
        $this->post = $post;
    }
    
    // ...
}
```

```php
$postEvent = new PostEvent($post);
$this->eventDispatcher(PostEvent::NAME, $postEvent)
```


### Why you should use it?

- easy to refactor event name
- no more human error in evnet names typos
- native IDE support for constant autocomplete 

### Why not?

- Because you need to keep unique per-class `NAME`
- still at manual writing and putting responsibility on programmer 


**Take a step back**

What is my goal? I look for identifier that is:

- **unique per class**
- **constant**
- **IDE friendly**
- **coupled to Event class** in any way

Can you see it? I think you do :)



### Class based Event Naming

```php
$postEvent = new PostEvent($post);
$this->eventDispatcher(PostEvent::class, $postEvent)
```

It could not be simpler and meets all the conditions

## Why use it

- all 4 reasons above
- it's typo proofs
- it uses PHP native `::class` support
- it's addictively easy
 

## Which Type Do You Like?

This is my story for event naming evolution. What is yours - **which event naming system do you use**? I'm curious and ready to be wrong, so let me know in the comments.
 
I still think there might be better way.

Like this tip by friend of mine @enumag.

```php
$postEvent = new PostEvent($post);
$this->eventDispatcher->dispatch($postEvent)
```

With simple wrapper over `EventDispatcher`:

```php
public function dispatch(Event $event)
{
    $this->eventDispatcher->dispatch(get_class($event), $event);
}
```

Or [eliminating visual debt](http://ocramius.github.io/blog/eliminating-visual-debt/) like this:
 
```php
EventDispatcher::dispatch([$post]);
```

#sarcasm

 

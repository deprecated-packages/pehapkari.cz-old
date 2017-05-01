<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2016\EventDispatcher;

use Pehapkari\Website\Posts\Year2016\EventDispatcher\EventSubscriber\NotifyMeOnVideoPublishedEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EventDispatchingTest extends TestCase
{
    public function test(): void
    {
        $eventDispatcher = new EventDispatcher;
        $notifyMeOnVideoPublishedEventSubscriber = new NotifyMeOnVideoPublishedEventSubscriber;
        $eventDispatcher->addSubscriber($notifyMeOnVideoPublishedEventSubscriber);

        // nothing happened, default value
        $this->assertFalse($notifyMeOnVideoPublishedEventSubscriber->isUserNotified());

        // this calls our Subscriber
        $eventDispatcher->dispatch('youtube.newVideoPublished');

        // now it's changed
        $this->assertTrue($notifyMeOnVideoPublishedEventSubscriber->isUserNotified());
    }
}

<?php

declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2016\Post2;

use Pehapkari\Website\Posts\Year2016\Post2\Event\YoutuberNameEvent;
use Pehapkari\Website\Posts\Year2016\Post2\EventSubscriber\EventAwareNotifyMeOnVideoPublishedEventSubscriber;
use Pehapkari\Website\Posts\Year2016\Post2\EventSubscriber\NotifyMeOnVideoPublishedEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EventDispatchingWithEventTest extends TestCase
{
    public function test()
    {
        $eventDispatcher = new EventDispatcher();
        $eventAwareNotifyMeOnVideoPublishedEventSubscriber = new EventAwareNotifyMeOnVideoPublishedEventSubscriber();
        $eventDispatcher->addSubscriber($eventAwareNotifyMeOnVideoPublishedEventSubscriber);

        $this->assertSame('', $eventAwareNotifyMeOnVideoPublishedEventSubscriber->getYoutuberUserName());

        $youtuberNameEvent = new YoutuberNameEvent('Jirka Král');
        $eventDispatcher->dispatch('youtube.newVideoPublished', $youtuberNameEvent);

        $this->assertSame(
            'Jirka Král',
            $eventAwareNotifyMeOnVideoPublishedEventSubscriber->getYoutuberUserName()
        );
    }
}

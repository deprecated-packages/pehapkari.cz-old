<?php

declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2016\Post2\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NotifyMeOnVideoPublishedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    public $isUserNotified = false;

    public static function getSubscribedEvents() : array
    {
        return ['youtube.newVideoPublished' => 'notifyUserAboutVideo'];
    }

    public function notifyUserAboutVideo()
    {
        // some logic to send notification
        $this->isUserNotified = true;
    }
}

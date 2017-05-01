<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2016\EventDispatcher\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class NotifyMeOnVideoPublishedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $isUserNotified = false;

    public static function getSubscribedEvents(): array
    {
        return ['youtube.newVideoPublished' => 'notifyUserAboutVideo'];
    }

    public function notifyUserAboutVideo(): void
    {
        $this->isUserNotified = true;
    }

    public function isUserNotified(): bool
    {
        return $this->isUserNotified;
    }
}

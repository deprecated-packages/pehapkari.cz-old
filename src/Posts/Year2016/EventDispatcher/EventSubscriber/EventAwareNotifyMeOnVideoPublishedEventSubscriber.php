<?php
declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2016\EventDispatcher\EventSubscriber;

use Pehapkari\Website\Posts\Year2016\EventDispatcher\Event\YoutuberNameEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventAwareNotifyMeOnVideoPublishedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $youtuberUserName = '';

    public static function getSubscribedEvents() : array
    {
        return ['youtube.newVideoPublished' => 'notifyUserAboutVideo'];
    }

    public function notifyUserAboutVideo(YoutuberNameEvent $youtuberNameEvent)
    {
        $this->youtuberUserName = $youtuberNameEvent->getYoutuberName();
    }

    public function getYoutuberUserName() : string
    {
        return $this->youtuberUserName;
    }
}

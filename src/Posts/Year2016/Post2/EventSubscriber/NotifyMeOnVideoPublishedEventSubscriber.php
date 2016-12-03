<?php

declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2016\Post2\Event;

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

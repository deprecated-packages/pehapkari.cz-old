<?php
declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2016\EventDispatcher\Event;

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

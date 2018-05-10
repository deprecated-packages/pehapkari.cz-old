<?php declare(strict_types=1);

namespace Pehapkari\Website\Statie\Generator;

use DateTime;
use DateTimeInterface;
use Nette\Utils\DateTime as NetteDateTime;
use Pehapkari\Website\Statie\Exception\LectureConfigurationException;
use Symplify\Statie\Generator\Renderable\File\AbstractGeneratorFile;

final class LectureFile extends AbstractGeneratorFile
{
    /**
     * @see https://stackoverflow.com/a/40475070/1348344
     * @var string
     */
    private const CALENDAR_TIME_FORMAT = 'Ymd\\THi00';

    public function isInEnglish(): bool
    {
        return isset($this->configuration['lang']) && $this->configuration['lang'] === 'en';
    }

    public function isActive(): bool
    {
        if (! $this->getDateTime()) {
            return false;
        }

        return $this->getDateTime() > new DateTime();
    }

    public function getTitle(): string
    {
        return $this->configuration['title'];
    }

    /**
     * @see https://stackoverflow.com/questions/10488831/link-to-add-to-google-calendar
     */
    public function showAddToCalendarLink(): bool
    {
        if (! isset($this->configuration['start'])) {
            return false;
        }

        if (! isset($this->configuration['end'])) {
            return false;
        }

        return true;
    }

    public function getStartInCalendarFormat(): ?string
    {
        if (! isset($this->configuration['start'])) {
            return null;
        }

        $dateTime = NetteDateTime::from($this->configuration['start']);

        return $dateTime->format(self::CALENDAR_TIME_FORMAT);
    }

    public function getEndInCalendarFormat(): ?string
    {
        if (! isset($this->configuration['end'])) {
            return null;
        }

        $dateTime = NetteDateTime::from($this->configuration['end']);

        return $dateTime->format(self::CALENDAR_TIME_FORMAT);
    }

    public function getImage(): ?string
    {
        return $this->configuration['image'] ?? null;
    }

    public function getFormLink(): ?string
    {
        return $this->configuration['form_link'] ?? null;
    }

    public function getUserId(): int
    {
        return (int) $this->configuration['user'];
    }

    public function getPerex(): ?string
    {
        return $this->configuration['perex'] ?? null;
    }

    public function getDuration(): ?string
    {
        return $this->configuration['duration'] ?? null;
    }

    public function getPrice(): ?int
    {
        return $this->configuration['price'] ?? null;
    }

    public function getCapacity(): ?string
    {
        return $this->configuration['capacity'] ?? null;
    }

    public function getDateTime(): ?DateTimeInterface
    {
        if (isset($this->configuration['start'])) {
            if ($this->configuration['start'] instanceof DateTimeInterface) {
                return $this->configuration['start'];
            }

            return NetteDateTime::from($this->configuration['start']);
        }

        return null;
    }

    public function getHumanDate(): ?string
    {
        if ($this->getDateTime() === null) {
            return null;
        }

        return $this->getDateTime()
            ->format('j. n. Y H:i');
    }

    /**
     * @param mixed[] $lectureReferences
     */
    public function isInReferences(array $lectureReferences): bool
    {
        foreach ($lectureReferences as $lectureReference) {
            if ($this->getId() === $lectureReference['lecture_id']) {
                return true;
            }
        }

        return false;
    }

//    public function getCalendarLocation(): string
//    {
//        $this->ensurePlaceIsSet();
//
//        return 'Node5,+Radlická+180/50,+150+00+Praha+5-Smíchov,+Czechia';
//    }

    public function getPlaceId(): int
    {
        $this->ensurePlaceIsSet();

        return (int) $this->configuration['place_id'];
    }
//
//    public function getLocationLink(): string
//    {
//        $this->ensurePlaceIsSet();
//
//        if (isset($this->configuration['place_id'])) {
//            return $this->configuration['location_link'];
//        }
//
//        if (isset($this->configuration['location_link'])) {
//            return $this->configuration['location_link'];
//        }
//
//        return 'https://www.google.com/maps/place/Node5/@50.0663614,14.4005504,17z/data=!3m1!4b1!4m5!3m4!1s0x470b9450c0dcebfb:0x2fad6c1cd982e330!8m2!3d50.066358!4d14.4027444';
//    }

    private function ensurePlaceIsSet(): void
    {
        if (isset($this->configuration['place_id'])) {
            return;
        }

        throw new LectureConfigurationException(sprintf(
            '%s is missing "place_id" value, add it.',
            $this->getFilePath()
        ));
    }
}

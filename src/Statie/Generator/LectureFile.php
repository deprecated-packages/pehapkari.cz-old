<?php declare(strict_types=1);

namespace Pehapkari\Website\Statie\Generator;

use DateTime;
use Symplify\Statie\Renderable\File\AbstractFile;

final class LectureFile extends AbstractFile
{
    public function isActive(): bool
    {
        if (! isset($this->configuration['date'])) {
            return false;
        }

        $now = new DateTime();
        $courseDate = new DateTime($this->configuration['date']);

        return $courseDate > $now;
    }

    public function getName(): string
    {
        return $this->configuration['name'];
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

    public function getHumanDate(): string
    {
        $courseDate = new DateTime($this->configuration['date']);

        return $courseDate->format('j. n. Y');
    }
}

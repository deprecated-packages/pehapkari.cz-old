<?php declare(strict_types=1);

namespace Pehapkari\Website\Statie\Generator;

use Symplify\Statie\Renderable\File\AbstractFile;

final class LectureFile extends AbstractFile
{
    public function isActive(): bool
    {
        if (! isset($this->configuration['date'])) {
            return false;
        }

        // @todo check future
        return (bool) $this->configuration['date'];
    }

    public function getName(): string
    {
        return $this->configuration['name'];
    }

    public function getImage(): ?string
    {
        return $this->configuration['image'] ?? null;
    }

    public function getUserId(): int
    {
        return (int) $this->configuration['user'];
    }

    public function getDateInString(): string
    {
        return $this->configuration['date'];
    }
}

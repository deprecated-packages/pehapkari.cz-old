<?php declare(strict_types=1);

namespace Pehapkari\Website\Statie\Generator;

use Symplify\Statie\Renderable\File\AbstractFile;

final class LectureFile extends AbstractFile
{
    public function getTitle(): string
    {
        return $this->configuration['title'];
    }
}

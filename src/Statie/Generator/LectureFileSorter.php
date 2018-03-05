<?php declare(strict_types=1);

namespace Pehapkari\Website\Statie\Generator;

use Symplify\Statie\Generator\Contract\ObjectSorterInterface;

final class LectureFileSorter implements ObjectSorterInterface
{
    /**
     * @param LectureFile[] $files
     * @return LectureFile[]
     */
    public function sort(array $files): array
    {
        $activeLectures = [];
        $restOfLectures = [];
        foreach ($files as $file) {
            if ($file->isActive()) {
                $activeLectures[] = $file;
            } else {
                $restOfLectures[] = $file;
            }
        }

        $activeLectures = $this->sortActiveLecturesByDate($activeLectures);
        $restOfLectures = $this->sortRestOfLecturesByName($restOfLectures);

        return array_merge($activeLectures, $restOfLectures);
    }

    /**
     * @param LectureFile[] $activeLectures
     * @return LectureFile[]
     */
    private function sortActiveLecturesByDate(array $activeLectures): array
    {
        usort($activeLectures, function (LectureFile $firstFile, LectureFile $secondFile) {
            return $secondFile->getDateTime() < $firstFile->getDateTime();
        });

        return $activeLectures;
    }

    /**
     * @param LectureFile[] $restOfLectures
     * @return LectureFile[]
     */
    private function sortRestOfLecturesByName(array $restOfLectures): array
    {
        usort($restOfLectures, function (LectureFile $firstFile, LectureFile $secondFile) {
            return strcmp($firstFile->getName(), $secondFile->getName());
        });

        return $restOfLectures;
    }
}

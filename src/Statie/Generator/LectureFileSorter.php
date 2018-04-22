<?php declare(strict_types=1);

namespace Pehapkari\Website\Statie\Generator;

use Pehapkari\Website\Exception\MissingLectureIdException;
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

        $allLectures = array_merge($activeLectures, $restOfLectures);

        return $this->useIdsAsArrayKeys($allLectures);
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
            return strcmp($firstFile->getTitle(), $secondFile->getTitle());
        });

        return $restOfLectures;
    }

    /**
     * @param LectureFile[] $allLectures
     * @return LectureFile[]
     */
    private function useIdsAsArrayKeys(array $allLectures): array
    {
        $arrayWithIdAsKey = [];

        foreach ($allLectures as $lecture) {
            $this->ensureIdIsSet($lecture);

            $arrayWithIdAsKey[$lecture->getId()] = $lecture;
        }

        return $arrayWithIdAsKey;
    }

    private function ensureIdIsSet(LectureFile $lectureFile): void
    {
        if ($lectureFile->getId()) {
            return;
        }

        throw new MissingLectureIdException(sprintf(
            'Lecture "%s" is missing "id:" in its configuration. Complete it.',
            $lectureFile->getTitle()
        ));
    }
}

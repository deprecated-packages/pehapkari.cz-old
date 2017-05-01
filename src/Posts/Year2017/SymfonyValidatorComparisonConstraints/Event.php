<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorComparisonConstraints;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

final class Event
{
    /**
     * @var \DateTime
     * @Assert\Type("DateTime")
     */
    protected $startDate;

    /**
     * @var \DateTime
     * @Assert\Type("DateTime")
     * @Assert\Expression("value >= this.getStartDate()")
     */
    protected $endDate;

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
}

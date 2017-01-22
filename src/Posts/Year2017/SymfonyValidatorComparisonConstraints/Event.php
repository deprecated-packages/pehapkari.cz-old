<?php

declare(strict_types = 1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorComparisonConstraints;

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

    /**
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }
}

<?php

declare(strict_types = 1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints;

use Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints\Constraints\ZipCode;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class Address
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Country()
     */
    protected $country;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $zipcode;

    /**
     * @Assert\Callback()
     */
    public function validateZipcode(ExecutionContextInterface $context)
    {
        $constraint = new ZipCode(['country' => $this->country]);
        $context
            ->getValidator()
            ->inContext($context)
            ->atPath('zipcode')
            ->validate($this->zipcode, $constraint);
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    public function getZipcode() : string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;
    }
}

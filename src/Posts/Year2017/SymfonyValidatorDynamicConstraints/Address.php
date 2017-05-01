<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints;

use Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints\Constraints\ZipCodeConstraint;
use Symfony\Component\Validator\Constraint;
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
     * @Assert\Callback(groups = "zipcode")
     */
    public function validateZipcode(ExecutionContextInterface $context): void
    {
        $constraint = new ZipCodeConstraint(['country' => $this->country]);
        $context
            ->getValidator()
            ->inContext($context)
            ->atPath('zipcode')
            ->validate($this->zipcode, $constraint, [Constraint::DEFAULT_GROUP]);
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }
}

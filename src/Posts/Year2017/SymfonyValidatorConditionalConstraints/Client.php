<?php

declare(strict_types = 1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorConditionalConstraints;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @Assert\GroupSequenceProvider()
 */
final class Client implements GroupSequenceProviderInterface
{
    const TYPE_COMPANY = 1;
    const TYPE_PERSON = 2;

    /**
     * @var int
     * @Assert\NotNull()
     * @Assert\Choice({Client::TYPE_COMPANY, CLIENT::TYPE_PERSON})
     */
    private $type;

    /**
     * @var string
     * @Assert\NotBlank(groups = {"company"})
     */
    private $company;

    /**
     * @var string
     * @Assert\NotBlank(groups = {"person"})
     */
    private $firstname;

    /**
     * @var string
     * @Assert\NotBlank(groups = {"person"})
     */
    private $lastname;

    /**
     * @return array
     */
    public function getGroupSequence()
    {
        return [
            [
                'Client',
                $this->type === self::TYPE_PERSON ? 'person' : 'company',
            ],
        ];
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname)
    {
        $this->lastname = $lastname;
    }
}

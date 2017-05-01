<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyValidatorConditionalConstraints;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * @Assert\GroupSequenceProvider()
 */
final class Client implements GroupSequenceProviderInterface
{
    /**
     * @var int
     */
    public const TYPE_COMPANY = 1;

    /**
     * @var int
     */
    public const TYPE_PERSON = 2;

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
     * {@inheritdoc}
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }
}

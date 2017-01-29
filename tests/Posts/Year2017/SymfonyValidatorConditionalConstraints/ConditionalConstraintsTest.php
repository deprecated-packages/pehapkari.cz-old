<?php

declare(strict_types = 1);

namespace Pehapkari\Website\Tests\Posts\Year2017\SymfonyValidatorConditionalConstraints;

use Pehapkari\Website\Posts\Year2017\SymfonyValidatorConditionalConstraints\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

final class ConditionalConstraintsTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    protected function setUp()
    {
        $builder = new ValidatorBuilder();
        $builder->enableAnnotationMapping();
        $this->validator = $builder->getValidator();
    }

    public function testViolationsFromDefaultAndCustomGroup()
    {
        $client = new Client();

        $this->assertViolations(
            [
                'type' => 'This value should not be null.',
                'company' => 'This value should not be blank.',
            ],
            $this->validator->validate($client)
        );
    }

    public function testViolationsFromCompanyGroup()
    {
        $client = new Client();
        $client->setType(Client::TYPE_COMPANY);

        $this->assertViolations(
            [
                'company' => 'This value should not be blank.',
            ],
            $this->validator->validate($client)
        );
    }

    public function testViolationsFromPersonGroup()
    {
        $client = new Client();
        $client->setType(Client::TYPE_PERSON);

        $this->assertViolations(
            [
                'firstname' => 'This value should not be blank.',
                'lastname' => 'This value should not be blank.',
            ],
            $this->validator->validate($client)
        );
    }

    private function assertViolations(array $expected, ConstraintViolationListInterface $violationList)
    {
        $violations = [];
        foreach ($violationList as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $violations[$violation->getPropertyPath()] = $violation->getMessage();
        }

        $this->assertSame($expected, $violations);
    }
}

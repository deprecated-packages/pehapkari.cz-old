<?php declare(strict_types = 1);

namespace Pehapkari\Website\Tests\Posts\Year2017\SymfonyValidatorDynamicConstraints;

use Pehapkari\Website\Posts\Year2017\SymfonyValidatorDynamicConstraints\Address;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

final class DynamicConstraintsTest extends TestCase
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

    public function testInvalidZipcode()
    {
        $address = new Address();
        $address->setCountry('US');
        $address->setZipcode('123456');

        $this->assertViolations(
            [
                'zipcode' => 'This value is not a valid ZIP code.',
            ],
            $this->validator->validate($address)
        );
    }

    public function testValidZipcode()
    {
        $address = new Address();
        $address->setCountry('US');
        $address->setZipcode('12345-6789');

        $this->assertViolations(
            [],
            $this->validator->validate($address)
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

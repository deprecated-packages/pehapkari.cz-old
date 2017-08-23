<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\SymfonyValidatorComparisonConstraints;

use DateTime;
use Pehapkari\Website\Posts\Year2017\SymfonyValidatorComparisonConstraints\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

final class ComparisonConstraintsTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    protected function setUp(): void
    {
        $builder = new ValidatorBuilder;
        $builder->enableAnnotationMapping();
        $this->validator = $builder->getValidator();
    }

    public function testExpressionViolation(): void
    {
        $event = new Event;
        $event->setStartDate(new DateTime('today'));
        $event->setEndDate(new DateTime('yesterday'));

        $this->assertViolations(
            [
                'endDate' => 'This value is not valid.',
            ],
            $this->validator->validate($event)
        );
    }

    /**
     * @param string[] $expected
     */
    private function assertViolations(array $expected, ConstraintViolationListInterface $violationList): void
    {
        $violations = [];
        foreach ($violationList as $violation) {
            // @var ConstraintViolationInterface $violation
            $violations[$violation->getPropertyPath()] = $violation->getMessage();
        }

        $this->assertSame($expected, $violations);
    }
}

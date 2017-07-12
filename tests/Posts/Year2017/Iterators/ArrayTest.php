<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\Iterators;

use PHPUnit\Framework\TestCase;

final class ArrayTest extends TestCase
{
    public function test(): void
    {
        // Arrange
        $object = [];
        $object[0] = 'first-value';
        $object[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($object as $key1 => $val1) {
            foreach ($object as $key2 => $val2) {
                $accumulator[] = [$val1, $val2];
            }
        }

        // Assert
        $this->assertCount(2 * 2, $accumulator); // cartesian product
        $this->assertSame([
            ['first-value', 'first-value'],
            ['first-value', 'second-value'],
            ['second-value', 'first-value'],
            ['second-value', 'second-value'],
        ], $accumulator);
    }
}

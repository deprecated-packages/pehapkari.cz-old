<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\Iterators;

use Pehapkari\Website\Posts\Year2017\Iterators\FixedArray;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

final class FixedArrayTest extends TestCase
{
    public function testSplFixedArrayWtf(): void
    {
        // Arrange
        $object = new SplFixedArray(2);
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
        $this->assertCount(2, $accumulator); // cartesian product
        $this->assertSame([['first-value', 'first-value'], ['first-value', 'second-value']], $accumulator);
    }

    public function testFixedArrayWtf(): void
    {
        // Arrange
        $object = new FixedArray(2);
        $object[0] = 'first-value';
        $object[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($object as $key1 => $val1) {
            $accumulator[] = [$val1];
        }

        // Assert
        $this->assertCount(2, $accumulator); // cartesian product
        $this->assertSame([['first-value'], ['second-value']], $accumulator);
    }

    public function testBreakPoint(): void
    {
        // Arrange
        $object = new FixedArray(2);
        $object[0] = 'first-value';
        $object[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($object as $key1 => $val1) {
            $object->__debugInfo(); // simulate what happens when you stop on breakpoint on this line
            // same as `var_dump($object)`
            $accumulator[] = [$val1];
        }

        // Assert
        $this->assertCount(1, $accumulator); // cartesian product
        $this->assertSame([['first-value']], $accumulator);
    }

    public function testQuickFixUsingClone(): void
    {
        // Arrange
        $object = new SplFixedArray(2);
        $object[0] = 'first-value';
        $object[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach (clone $object as $key1 => $val1) {
            foreach (clone $object as $key2 => $val2) {
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

    public function test09BonusInfiniteLoop(): void
    {
        // Arrange
        $object = new SplFixedArray(2);
        $object[0] = 'first-value';
        $object[1] = 'second-value';

        $i = 0;

        // Act
        foreach ($object as $key1 => $val1) {
            foreach ($object as $key2 => $val2) {
                if ($i >= 1000) {
                    continue;
                } // prevent looping to infinity
                ++$i;

                // this is how you make this loop infinite:
                // Task: rewrite this loops as a while loops (see above) and get the idea what is happening
                break;
            }
        }

        // Assert
        $this->assertSame(1000, $i);
    }
}

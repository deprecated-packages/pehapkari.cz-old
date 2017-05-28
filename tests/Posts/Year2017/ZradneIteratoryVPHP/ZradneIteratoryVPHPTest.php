<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\ZradneIteratoryVPHP;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use SplFixedArray;

final class ZradneIteratoryVPHPTest extends TestCase
{
    public function test01SimpleArray(): void
    {
        // Arrange
        $a = [];
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($a as $key1 => $val1) {
            foreach ($a as $key2 => $val2) {
                $accumulator[] = [$val1, $val2];
            }
        }

        // Assert
        $this->assertCount(2 * 2, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value', 'first-value'],
            ['first-value', 'second-value'],
            ['second-value', 'first-value'],
            ['second-value', 'second-value'],
        ], $accumulator);
    }


    public function test02SplFixedArrayWTF(): void
    {
        // Arrange
        $a = new SplFixedArray(2);
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($a as $key1 => $val1) {
            foreach ($a as $key2 => $val2) {
                $accumulator[] = [$val1, $val2];
            }
        }

        // Assert
        $this->assertCount(2, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value', 'first-value'],
            ['first-value', 'second-value'],
        ], $accumulator);
    }


    public function test03SplFixedArrayWTF(): void
    {
        // Arrange
        $a = new class (2) extends SplFixedArray
        {
            public function __debugInfo()
            {
                $ret = [];
                /** @noinspection ForeachSourceInspection */
                foreach ($this as $key => $val) {
                    $ret[(string) $key] = (string) $val;
                }
                return $ret;
            }
        };
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($a as $key1 => $val1) {
            $accumulator[] = [$val1];
        }

        // Assert
        $this->assertCount(2, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value'],
            ['second-value'],
        ], $accumulator);
    }


    public function test04SplFixedArrayWTF(): void
    {
        // Arrange
        $a = new class (2) extends SplFixedArray
        {
            public function __debugInfo()
            {
                $ret = [];
                /** @noinspection ForeachSourceInspection */
                foreach ($this as $key => $val) {
                    $ret[(string) $key] = (string) $val;
                }
                return $ret;
            }
        };
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($a as $key1 => $val1) {
            $a->__debugInfo(); // simulate what happens when you stop on breakpoint on this line
            // same as `var_dump($a)`
            $accumulator[] = [$val1];
        }

        // Assert
        $this->assertCount(1, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value'],
        ], $accumulator);
    }


    public function test05ForeachWrittenAsWhile(): void
    {
        // Arrange
        $a = new SplFixedArray(2);
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        reset($a); // PHPStorm's static analysis is crying here; it is intentional
        while (list($key1, $val1) = each($a)) {
            reset($a);
            while (list($key2, $val2) = each($a)) {
                $accumulator[] = [$val1, $val2];
            }
        }

        // Assert
        $this->assertCount(2, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value', 'first-value'],
            ['first-value', 'second-value'],
        ], $accumulator);
    }


    public function test06QuickFixUsingClone(): void
    {
        // Arrange
        $a = new SplFixedArray(2);
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach (clone $a as $key1 => $val1) {
            foreach (clone $a as $key2 => $val2) {
                $accumulator[] = [$val1, $val2];
            }
        }

        // Assert
        $this->assertCount(2 * 2, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value', 'first-value'],
            ['first-value', 'second-value'],
            ['second-value', 'first-value'],
            ['second-value', 'second-value'],
        ], $accumulator);
    }


    public function test07ArrayObject(): void
    {
        // Arrange
        $a = new ArrayObject();
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $accumulator = [];

        // Act
        foreach ($a as $key1 => $val1) {
            foreach ($a as $key2 => $val2) {
                $accumulator[] = [$val1, $val2];
            }
        }

        // Assert
        $this->assertCount(2 * 2, $accumulator); // cartesian product
        $this->assertEquals([
            ['first-value', 'first-value'],
            ['first-value', 'second-value'],
            ['second-value', 'first-value'],
            ['second-value', 'second-value'],
        ], $accumulator);
    }


    public function test08NewIteratorIsReturnedEveryTime(): void
    {
        // Arrange
        $a = new ArrayObject();

        // Act
        $iterator1 = $a->getIterator();
        $iterator2 = $a->getIterator();

        // Assert
        $this->assertNotSame($iterator1, $iterator2);
    }


    public function test09Bonus_InfiniteLoop(): void
    {
        // Arrange
        $a = new SplFixedArray(2);
        $a[0] = 'first-value';
        $a[1] = 'second-value';

        $i = 0;

        // Act
        foreach ($a as $key1 => $val1) {
            foreach ($a as $key2 => $val2) {
                if ($i >= 1000) {
                    continue;
                } // prevent looping to infinity
                $i++;

                // this is how you make this loop infinite:
                // Task: rewrite this loops as a while loops (see above) and get the idea what is happening
                break;
            }
        }

        // Assert
        $this->assertEquals(1000, $i);
    }
}

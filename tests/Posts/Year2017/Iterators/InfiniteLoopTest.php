<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\Iterators;

use PHPUnit\Framework\TestCase;
use SplFixedArray;

final class InfiniteLoopTest extends TestCase
{
    public function test(): void
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

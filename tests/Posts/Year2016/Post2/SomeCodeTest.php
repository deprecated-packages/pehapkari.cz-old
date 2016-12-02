<?php

declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2016\Post2;

use Pehapkari\Website\Posts\Year2016\Post2\SomeCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers SomeCode
 */
final class SomeCodeTest extends TestCase
{
    public function test()
    {
        $someCode = new SomeCode();
        $this->assertInstanceOf(SomeCode::class, $someCode);
    }
}

<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2018\Cart\Infrastructure;

use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartRepository;
use Pehapkari\Website\Posts\Year2018\Cart\Infrastructure\MemoryCartRepository;

final class MemoryCartRepositoryTest extends CartRepositoryTest
{
    protected function createRepository(): CartRepository
    {
        return new MemoryCartRepository();
    }
}

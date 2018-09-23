<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2018\Cart\Domain;

use Pehapkari\Website\Posts\Year2018\Cart\Domain\AmountMustBePositiveException;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\Item;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\ItemDetail;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\Price;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    public function testToDetail(): void
    {
        $item = new Item('x', new Price(5.0), 2);

        $expected = new ItemDetail('x', new Price(5.0), 2);

        Assert::assertEquals($expected, $item->toDetail());
    }

    public function testAdd(): void
    {
        $item = new Item('x', new Price(5.0), 2);
        $item->add(5);

        $expected = new ItemDetail('x', new Price(5.0), 7);

        Assert::assertEquals($expected, $item->toDetail());
    }

    public function testChangeAmount(): void
    {
        $item = new Item('x', new Price(5.0), 2);
        $item->changeAmount(1);

        $expected = new ItemDetail('x', new Price(5.0), 1);

        Assert::assertEquals($expected, $item->toDetail());
    }

    public function testInitialAmountCannotBeNegative(): void
    {
        $this->expectException(AmountMustBePositiveException::class);
        new Item('x', new Price(5.0), -1);
    }

    public function testInitialAmountCannotBeZero(): void
    {
        $this->expectException(AmountMustBePositiveException::class);
        new Item('x', new Price(5.0), 0);
    }

    public function testAddNegativeThrowsException(): void
    {
        $this->expectException(AmountMustBePositiveException::class);
        $item = new Item('x', new Price(5.0), 1);
        $item->add(-1);
    }

    public function testAddZeroThrowsException(): void
    {
        $this->expectException(AmountMustBePositiveException::class);
        $item = new Item('x', new Price(5.0), 1);
        $item->add(0);
    }

    public function testChangeToNegativeThrowsException(): void
    {
        $this->expectException(AmountMustBePositiveException::class);
        $item = new Item('x', new Price(5.0), 1);
        $item->changeAmount(-1);
    }

    public function testChangeToZeroThrowsException(): void
    {
        $this->expectException(AmountMustBePositiveException::class);
        $item = new Item('x', new Price(5.0), 1);
        $item->changeAmount(0);
    }

    public function testCalculateTotalPrice(): void
    {
        $item = new Item('x', new Price(5.0), 3);
        $price = $item->calculatePrice();

        $expected = new Price(15.0);
        Assert::assertEquals($expected, $price);
    }
}

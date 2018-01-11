<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2018\Cart\Domain;

use Pehapkari\Website\Posts\Year2018\Cart\Domain\Cart;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartDetail;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\ItemDetail;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\Price;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\ProductNotInCartException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testCalculateEmptyCart(): void
    {
        $cart = new Cart;

        $expected = new CartDetail([], new Price(0.0));

        Assert::assertEquals($expected, $cart->calculate());
    }

    public function testAddSingleProductToEmpty(): void
    {
        $cart = new Cart();
        $cart->add('a', new Price(10.0));

        $expectedItem = new ItemDetail('a', new Price(10.0), 1);
        $expected = new CartDetail([$expectedItem], new Price(10.0));

        Assert::assertEquals($expected, $cart->calculate());
    }

    public function testAddTwoDifferentProducts(): void
    {
        $cart = new Cart();
        $cart->add('a', new Price(10.0));
        $cart->add('b', new Price(20.0), 2);

        $expectedItems = [
            new ItemDetail('a', new Price(10.0), 1),
            new ItemDetail('b', new Price(20.0), 2),
        ];
        $expected = new CartDetail($expectedItems, new Price(50.0));

        Assert::assertEquals($expected, $cart->calculate());
    }

    public function testAddSameProductIncrementAmountOnly(): void
    {
        $cart = new Cart();
        $cart->add('a', new Price(10.0));
        $cart->add('a', new Price(0.0));

        $expectedItem = new ItemDetail('a', new Price(10.0), 2);
        $expected = new CartDetail([$expectedItem], new Price(20.0));

        Assert::assertEquals($expected, $cart->calculate());
    }

    public function testRemoveNotExistingProductFromEmptyCart(): void
    {
        $this->expectException(ProductNotInCartException::class);

        $cart = new Cart();
        $cart->remove('x');
    }

    public function testRemoveNotExistingProduct(): void
    {
        $this->expectException(ProductNotInCartException::class);

        $cart = new Cart();
        $cart->add('a', new Price(10.0));
        $cart->remove('x');
    }

    public function testRemoveProduct(): void
    {
        $cart = new Cart();
        $cart->add('a', new Price(10.0));
        $cart->add('b', new Price(20.0), 2);

        $cart->remove('a');

        $expectedItems = [
            new ItemDetail('b', new Price(20.0), 2),
        ];
        $expected = new CartDetail($expectedItems, new Price(40.0));

        Assert::assertEquals($expected, $cart->calculate());
    }

    public function testChangeAmountOfNotExisting(): void
    {
        $this->expectException(ProductNotInCartException::class);

        $cart = new Cart();
        $cart->add('a', new Price(10.0));

        $cart->changeAmount('x', 5);
    }

    public function testChangeAmount(): void
    {
        $cart = new Cart();
        $cart->add('a', new Price(10.0));
        $cart->changeAmount('a', 5);

        $expectedItem = new ItemDetail('a', new Price(10.0), 5);
        $expected = new CartDetail([$expectedItem], new Price(50.0));

        Assert::assertEquals($expected, $cart->calculate());
    }
}

<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2018\Cart\Infrastructure;

use Pehapkari\Website\Posts\Year2018\Cart\Domain\Cart;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartDetail;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartNotFoundException;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartRepository;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\ItemDetail;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\Price;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

abstract class CartRepositoryTest extends TestCase
{
    /**
     * @var CartRepository
     */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createRepository();
    }

    public function testAddAndGetSuccessfully(): void
    {
        $cart = $this->createCartWithItem('1');
        $this->repository->add($cart);
        $this->flush();

        $foundCart = $this->repository->get('1');
        Assert::assertEquals($this->getCartDetailWithItem(), $foundCart->calculate());
    }

    public function testAddAndRemoveSuccessfully(): void
    {
        $cart = $this->createCartWithItem('1');
        $this->repository->add($cart);
        $this->flush();

        $this->repository->remove('1');
        $this->flush();

        $this->expectException(CartNotFoundException::class);
        $this->repository->get('1');
    }

    public function testAddedIsTheSameObject(): void
    {
        $empty = $this->createEmptyCart('1');
        $this->repository->add($empty);
        $empty->add('1', new Price(10.0));
        $this->flush();

        $found = $this->repository->get('1');
        Assert::assertEquals($this->getCartDetailWithItem(), $found->calculate());
    }

    public function testFlushChangedPersists(): void
    {
        $empty = $this->createEmptyCart('1');
        $this->repository->add($empty);
        $this->flush();

        $foundEmpty = $this->repository->get('1');
        $foundEmpty->add('1', new Price(10.0));
        $this->flush();

        $found = $this->repository->get('1');
        Assert::assertEquals($this->getCartDetailWithItem(), $found->calculate());
    }

    public function testGetNotExistingCauseException(): void
    {
        $this->expectException(CartNotFoundException::class);

        $this->repository->get('1');
    }

    public function testRemoveNotExistingCauseException(): void
    {
        $this->expectException(CartNotFoundException::class);

        $this->repository->remove('1');
    }

    public function testAddTwoAndGetTwoSuccessfully(): void
    {
        $withItem = $this->createCartWithItem('1');
        $this->repository->add($withItem);
        $empty = $this->createEmptyCart('2');
        $this->repository->add($empty);
        $this->flush();

        $foundEmpty = $this->repository->get('1');
        Assert::assertEquals($this->getCartDetailWithItem(), $foundEmpty->calculate());

        $foundEmpty = $this->repository->get('2');
        Assert::assertEquals($this->getEmptyCartDetail(), $foundEmpty->calculate());
    }

    protected function flush(): void
    {
    }

    abstract protected function createRepository(): CartRepository;

    private function createCartWithItem(string $id): Cart
    {
        $cart = new Cart($id);
        $cart->add('1', new Price(10), 1);

        return $cart;
    }

    private function getCartDetailWithItem(): CartDetail
    {
        $item = new ItemDetail('1', new Price(10), 1);

        return new CartDetail([$item], new Price(10));
    }

    private function createEmptyCart(string $id): Cart
    {
        return new Cart($id);
    }

    private function getEmptyCartDetail(): CartDetail
    {
        return new CartDetail([], new Price(0));
    }
}

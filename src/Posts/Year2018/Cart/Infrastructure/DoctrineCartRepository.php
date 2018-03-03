<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2018\Cart\Infrastructure;

use Doctrine\ORM\EntityManager;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\Cart;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartNotFoundException;
use Pehapkari\Website\Posts\Year2018\Cart\Domain\CartRepository;
use TypeError;

final class DoctrineCartRepository implements CartRepository
{
    /**
     * @var EntityManager
     */
    private $entityManger;

    public function __construct(EntityManager $entityManger)
    {
        $this->entityManger = $entityManger;
    }

    public function add(Cart $cart): void
    {
        $this->entityManger->persist($cart);
    }

    public function get(string $id): Cart
    {
        return $this->getThrowingException($id);
    }

    public function remove(string $id): void
    {
        $cart = $this->getThrowingException($id);
        $this->entityManger->remove($cart);
    }

    private function find(string $id): ?Cart
    {
        return $this->entityManger->find(Cart::class, $id);
    }

    private function getThrowingException(string $id): Cart
    {
        try {
            return $this->find($id);
        } catch (TypeError $e) {
            throw new CartNotFoundException();
        }
    }
}

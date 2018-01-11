<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2018\Cart\Domain;

class CartDetail
{
    /**
     * @var ItemDetail[]
     */
    private $items = [];

    /**
     * @var Price
     */
    private $totalPrice;

    /**
     * @param ItemDetail[] $items
     */
    public function __construct(array $items, Price $totalPrice)
    {
        $this->items = $items;
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return ItemDetail[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalPrice(): Price
    {
        return $this->totalPrice;
    }
}

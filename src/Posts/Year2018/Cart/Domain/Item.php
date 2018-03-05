<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2018\Cart\Domain;

class Item
{
    /**
     * @var int
     */
    protected $generatedId;

    /**
     * @var string
     */
    private $productId;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var Price
     */
    private $unitPrice;

    /**
     * @throws AmountMustBePositiveException
     */
    public function __construct(string $productId, Price $unitPrice, int $amount)
    {
        $this->checkAmount($amount);
        $this->productId = $productId;
        $this->amount = $amount;
        $this->unitPrice = $unitPrice;
    }

    public function toDetail(): ItemDetail
    {
        return new ItemDetail($this->productId, $this->unitPrice, $this->amount);
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @throws AmountMustBePositiveException
     */
    public function add(int $amount): void
    {
        $this->checkAmount($amount);
        $this->amount = $this->amount + $amount;
    }

    /**
     * @throws AmountMustBePositiveException
     */
    public function changeAmount(int $amount): void
    {
        $this->checkAmount($amount);
        $this->amount = $amount;
    }

    public function calculatePrice(): Price
    {
        return $this->unitPrice->multiply($this->amount);
    }

    /**
     * @throws AmountMustBePositiveException
     */
    private function checkAmount(int $amount): void
    {
        if ($amount <= 0) {
            throw new AmountMustBePositiveException();
        }
    }
}

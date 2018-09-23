<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2018\Cart\Domain;

final class Price
{
    /**
     * @var float
     */
    private $withVat;

    public function __construct(float $withVat)
    {
        $this->withVat = $withVat;
    }

    /**
     * @param Price[] $prices
     */
    public static function sum(array $prices): self
    {
        return array_reduce($prices, function (self $carry, self $price) {
            return $carry->add($price);
        }, new self(0.0));
    }

    public function getWithVat(): float
    {
        return $this->withVat;
    }

    public function add(self $adder): self
    {
        $withVat = $this->withVat + $adder->withVat;

        return new self($withVat);
    }

    public function multiply(int $multiplier): self
    {
        $withVat = $this->withVat * $multiplier;

        return new self($withVat);
    }
}

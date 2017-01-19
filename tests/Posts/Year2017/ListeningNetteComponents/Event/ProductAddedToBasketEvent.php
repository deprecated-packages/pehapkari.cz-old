<?php

declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Event;

use Symfony\Component\EventDispatcher\Event;

final class ProductAddedToBasketEvent extends Event
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $price;


    public function __construct(int $id, string $name, int $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }


    public function getId(): int
    {
        return $this->id;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getPrice(): int
    {
        return $this->price;
    }
}

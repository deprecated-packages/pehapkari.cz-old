<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Component\AddToBasketControl;

use Nette\Application\UI\Control;
use Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Event\ProductAddedToBasketEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class AddToBasketControl extends Control
{
    /**
     * @var int[]|string[]
     */
    private $product = [];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param int[]|string[] $product
     */
    public function __construct(array $product, EventDispatcherInterface $eventDispatcher)
    {
        $this->product = $product;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handleAdd(): void
    {
        // Zde může být nějaká složitější logika
        // např.: $this->basketFacade->addProduct($this->product);

        // vytvoříme instanci události
        $productAddedToBasketEvent = new ProductAddedToBasketEvent(
            (int) $this->product['id'],
            (string) $this->product['name'],
            (int) $this->product['price']
        );
        $this->eventDispatcher->dispatch(
            ProductAddedToBasketEvent::class,
            $productAddedToBasketEvent
        ); // vyvoláme událost!
    }

    public function render(): void
    {
        $this->template->render(__DIR__ . '/templates/default.latte');
    }
}

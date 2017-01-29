<?php
declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Component\BasketContentControl;

use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;
use Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Event\ProductAddedToBasketEvent;

/**
 * @method Template getTemplate()
 */
final class BasketContentControl extends Control
{

    /**
     * @var array
     */
    private $products = [];


    // Tuto metodu zavolá EventSubscriber, protože je nastavena jako listener callback v CategoryPresenter::startup()
    public function onProductAddedToBasketEvent(ProductAddedToBasketEvent $productAddedToBasketEvent)
    {
        $product = [
            'id' => $productAddedToBasketEvent->getId(),
            'name' => $productAddedToBasketEvent->getName(),
            'price' => $productAddedToBasketEvent->getPrice(),
        ];

        $this->products[] = $product;

        $this->redrawControl('content');
    }


    public function render()
    {
        $this->getTemplate()->render(
            __DIR__ . '/templates/default.latte',
            [
                'products' => $this->products
            ]
        );
    }
}

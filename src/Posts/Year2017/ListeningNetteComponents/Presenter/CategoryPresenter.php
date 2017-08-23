<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Presenter;

use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Component\AddToBasketControl\AddToBasketControlFactoryInterface;
use Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Component\BasketContentControl\BasketContentControl;
use Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Component\BasketContentControl\BasketContentControlFactoryInterface;
use Pehapkari\Website\Posts\Year2017\ListeningNetteComponents\Event\ProductAddedToBasketEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @method Template getTemplate()
 */
final class CategoryPresenter extends Presenter
{
    /**
     * @var array
     */
    private const PRODUCTS = [
        [
            'id' => 1,
            'name' => 'T-Shirt',
            'price' => 100,
        ],
        [
            'id' => 2,
            'name' => 'Red socks',
            'price' => 29,
        ],
        [
            'id' => 3,
            'name' => 'Green hat',
            'price' => 99,
        ],
    ];

    /**
     * @var AddToBasketControlFactoryInterface
     */
    private $addToBasketControlFactory;

    /**
     * @var BasketContentControlFactoryInterface
     */
    private $basketContentControlFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        AddToBasketControlFactoryInterface $addToBasketControlFactory,
        BasketContentControlFactoryInterface $basketContentControlFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->addToBasketControlFactory = $addToBasketControlFactory;
        $this->basketContentControlFactory = $basketContentControlFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function startup(): void
    {
        parent::startup();

        $basketContentControl = $this->getComponent('basketContent');

        $this->eventDispatcher->addListener(
            ProductAddedToBasketEvent::class,
            [$basketContentControl, 'onProductAddedToBasketEvent']
        );
    }

    public function renderDefault(): void
    {
        $this->getTemplate()->setParameters([
            'products' => self::PRODUCTS,
        ]);
    }

    protected function createComponentAddToBasket(): Multiplier
    {
        // Musíme použít Multiplier, protože potřebujeme samostatnou instanci pro každý produkt.
        // Co je to Multiplier? Více informací najdeš ve článku https://pla.nette.org/cs/multiplier.

        return new Multiplier(function ($productId) {
            $product = [];
            foreach (self::PRODUCTS as $productData) {
                if ($productData['id'] === (int) $productId) {
                    $product = $productData;
                    break;
                }
            }

            return $this->addToBasketControlFactory->create($product);
        });
    }

    protected function createComponentBasketContent(): BasketContentControl
    {
        return $this->basketContentControlFactory->create();
    }
}

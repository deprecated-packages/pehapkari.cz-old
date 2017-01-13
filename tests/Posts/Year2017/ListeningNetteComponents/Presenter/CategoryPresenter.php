<?php

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Presenter;

use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Component\AddToBasketControl\AddToBasketControl;
use Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Component\AddToBasketControl\AddToBasketControlFactoryInterface;
use Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Component\BasketContentControl\BasketContentControl;
use Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Component\BasketContentControl\BasketContentControlFactoryInterface;
use Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Event\ProductAddedToBasketEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


final class CategoryPresenter extends Presenter
{

	const PRODUCTS = [
		[
			'id' => 1,
			'name' => 'T-Shirt',
			'price' => 100
		],
		[
			'id' => 2,
			'name' => 'Red socks',
			'price' => 29
		],
		[
			'id' => 3,
			'name' => 'Green hat',
			'price' => 99
		]
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


	public function startup()
	{
		parent::startup();

		$basketContentControl = $this->getComponent('basketContent');

		$this->eventDispatcher->addListener(
			ProductAddedToBasketEvent::class,
			[$basketContentControl, 'onProductAddedToBasketEvent']
		);
	}


	public function renderDefault()
	{
		$this->template->setParameters([
			'products' => self::PRODUCTS
		]);
	}


	/**
	 * @return AddToBasketControl
	 */
	protected function createComponentAddToBasket()
	{
		// We must use Multiplier because we need separate instance for every product
		// What is Multiplier? Read article at https://pla.nette.org/cs/multiplier.

		return new Multiplier(function($productId) {
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


	/**
	 * @return BasketContentControl
	 */
	protected function createComponentBasketContent()
	{
		return $this->basketContentControlFactory->create();
	}

}

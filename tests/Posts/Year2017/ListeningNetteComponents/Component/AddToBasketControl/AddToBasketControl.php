<?php

declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Component\AddToBasketControl;

use Nette\Application\UI\Control;
use Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Event\ProductAddedToBasketEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


final class AddToBasketControl extends Control
{

	/**
	 * @var array
	 */
	private $product;

	/**
	 * @var EventDispatcherInterface
	 */
	private $eventDispatcher;


	public function __construct(array $product, EventDispatcherInterface $eventDispatcher)
	{
		$this->product = $product;
		$this->eventDispatcher = $eventDispatcher;
	}


	public function handleAdd()
	{
		// There will be some logic with basket.
		// e.g.: $this->basketFacade->addProduct($this->product);

		// construct event object
		$productAddedToBasketEvent = new ProductAddedToBasketEvent(
			$this->product['id'],
			$this->product['name'],
			$this->product['price']
		);
		$this->eventDispatcher->dispatch(ProductAddedToBasketEvent::class, $productAddedToBasketEvent); // dispatch it!
	}


	public function render()
	{
		$this->template->render(__DIR__ . '/templates/default.latte');
	}

}

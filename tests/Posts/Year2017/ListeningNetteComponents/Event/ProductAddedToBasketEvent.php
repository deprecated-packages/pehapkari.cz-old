<?php

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Event;

use Symfony\Component\EventDispatcher\Event;


final class ProductAddedToBasketEvent extends Event
{

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $price;


	/**
	 * @param string $id
	 * @param string $name
	 * @param string $price
	 */
	public function __construct($id, $name, $price)
	{
		$this->id = $id;
		$this->name = $name;
		$this->price = $price;
	}


	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getPrice()
	{
		return $this->price;
	}

}

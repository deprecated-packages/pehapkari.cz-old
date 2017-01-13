<?php

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\DI;

use Nette\DI\CompilerExtension;
use Symfony\Component\EventDispatcher\EventDispatcher;


final class ListeningNetteComponentsExtension extends CompilerExtension
{

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('eventDispatcher'))
			->setClass(EventDispatcher::class);
	}
}

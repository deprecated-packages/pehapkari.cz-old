<?php

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Tests;

use Nette\Configurator;
use Nette\DI\Container;
use Nette\Utils\FileSystem;


final class ContainerFactory
{

	const TEMP_DIR = __DIR__ . '/temp';


	/**
	 * @return Container
	 */
	public function create()
	{
		FileSystem::delete(self::TEMP_DIR);
		mkdir(self::TEMP_DIR, 0777);

		$configurator = new Configurator;
		$configurator->setDebugMode(FALSE);
		$configurator->setTempDirectory(self::TEMP_DIR);
		$configurator->addConfig(__DIR__ . '/config/config.neon');

		return $configurator->createContainer();
	}

}

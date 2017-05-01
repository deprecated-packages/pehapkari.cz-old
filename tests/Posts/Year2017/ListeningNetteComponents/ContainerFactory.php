<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents;

use Nette\Configurator;
use Nette\DI\Container;
use Nette\Utils\FileSystem;

final class ContainerFactory
{
    public function create(): Container
    {
        $configurator = new Configurator;
        $configurator->setTempDirectory($this->createAndReturnTempDir());
        $configurator->addConfig(__DIR__ . '/config/config.neon');

        return $configurator->createContainer();
    }

    private function createAndReturnTempDir(): string
    {
        $tempDir = sys_get_temp_dir() . '/pehapkari-listening-nette-components';
        FileSystem::delete($tempDir);
        FileSystem::createDir($tempDir);

        return $tempDir;
    }
}

<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2018\Cart\Utils;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Tools\SchemaTool;

final class EntityManagerFactory
{
    /**
     * @param string[] $schemaClassNames
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createEntityManager(Connection $connection, array $schemaClassNames): EntityManager
    {
        $config = new Configuration();

        $cartMappingDirectory = __DIR__ . '/../../../../../src/Posts/Year2018/Cart/Infrastructure/DoctrineMapping';
        $namespaces = [
            $cartMappingDirectory => 'Pehapkari\\Website\\Posts\\Year2018\\Cart\\Domain',
        ];
        $xmlDrive = new SimplifiedXmlDriver($namespaces, '.xml');

        $config->setMetadataDriverImpl($xmlDrive);

        $config->setProxyDir(__DIR__);
        $config->setProxyNamespace('Doctrine\Tests\Proxies');
        $config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_NEVER);

        $entityManager = EntityManager::create($connection, $config);

        (new SchemaTool($entityManager))
            ->createSchema(array_map([$entityManager, 'getClassMetadata'], $schemaClassNames));

        return $entityManager;
    }
}

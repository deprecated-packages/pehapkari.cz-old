<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\NetteConfigObjects\Config;

use Nette\DI\Container;
use Pehapkari\Website\Posts\Year2017\NetteConfigObjects\Config\InvoicingConfig;
use Pehapkari\Website\Tests\Posts\Year2017\NetteConfigObjects\ContainerFactory;
use PHPUnit\Framework\TestCase;

final class InvoicingConfigTest extends TestCase
{
    public const PDF_PATH = 'tests/Posts/Year2017/NetteConfigObjects/../invoices';

    /**
     * @var Container
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = (new ContainerFactory)->create();
    }

    public function testBasicRequest(): void
    {
        /** @var InvoicingConfig $config */
        $config = $this->container->getByType(InvoicingConfig::class);

        $this->assertSame(7, $config->defaultMaturity);
        $this->assertContains(self::PDF_PATH, $config->pdfDirectory);
        $this->assertContains(self::PDF_PATH . '/2017001.pdf', $config->getPdfPath(2017001));
    }
}

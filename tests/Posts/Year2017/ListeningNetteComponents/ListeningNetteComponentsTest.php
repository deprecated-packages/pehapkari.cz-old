<?php declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents;

use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use PHPUnit\Framework\TestCase;

final class ListeningNetteComponentsTest extends TestCase
{
    /**
     * @var string
     */
    const PRESENTER_NAME = 'Category';

    /**
     * @var IPresenterFactory
     */
    private $presenterFactory;

    protected function setUp()
    {
        $container = (new ContainerFactory)->create();
        $this->presenterFactory = $container->getByType(IPresenterFactory::class);
    }

    public function testBasicRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET');
        $presenter = $this->createPresenter();
        /** @var TextResponse $response */
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            $this->loadFileWithUnixLineEndings(__DIR__ . '/responses/success/basic.request.txt'),
            (string) $response->getSource()
        );
    }

    public function testAddToBasketFirstProductRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET', ['do' => 'addToBasket-1-add']);
        $presenter = $this->createPresenter();
        /** @var TextResponse $response */
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            $this->loadFileWithUnixLineEndings(__DIR__ . '/responses/success/add-to-basket-first-product.request.txt'),
            (string) $response->getSource()
        );
    }

    public function testAddToBasketSecondProductRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET', ['do' => 'addToBasket-2-add']);
        $presenter = $this->createPresenter();
        /** @var TextResponse $response */
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            $this->loadFileWithUnixLineEndings(__DIR__ . '/responses/success/add-to-basket-second-product.request.txt'),
            (string) $response->getSource()
        );
    }

    public function testAddToBasketThirdProductRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET', ['do' => 'addToBasket-3-add']);
        $presenter = $this->createPresenter();
        /** @var TextResponse $response */
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            $this->loadFileWithUnixLineEndings(__DIR__ . '/responses/success/add-to-basket-third-product.request.txt'),
            (string) $response->getSource()
        );
    }

    private function createPresenter() : IPresenter
    {
        /** @var Presenter $categoryPresenter */
        $categoryPresenter = $this->presenterFactory->createPresenter(self::PRESENTER_NAME);
        $categoryPresenter->autoCanonicalize = false;

        return $categoryPresenter;
    }

    private function loadFileWithUnixLineEndings(string $file) : string
    {
        return str_replace("\r\n", "\n", file_get_contents($file));
    }
}

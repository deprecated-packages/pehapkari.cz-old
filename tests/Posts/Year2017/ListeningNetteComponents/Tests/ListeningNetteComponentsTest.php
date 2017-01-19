<?php

declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2017\ListeningNetteComponents\Tests;

use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase;

final class ListeningNetteComponentsTest extends TestCase
{

    const PRESENTER_NAME = 'Category';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var IPresenterFactory
     */
    private $presenterFactory;


    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->container = (new ContainerFactory)->create();
        $this->presenterFactory = $this->container->getByType(IPresenterFactory::class);
    }


    public function testBasicRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET');
        $presenter = $this->createPresenter();
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            file_get_contents(__DIR__ . '/responses/success/basic.request.txt'),
            (string) $response->getSource()
        );
    }


    public function testAddToBasketFirstProductRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET', ['do' => 'addToBasket-1-add']);
        $presenter = $this->createPresenter();
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            file_get_contents(__DIR__ . '/responses/success/add-to-basket-first-product.request.txt'),
            (string) $response->getSource()
        );
    }


    public function testAddToBasketSecondProductRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET', ['do' => 'addToBasket-2-add']);
        $presenter = $this->createPresenter();
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            file_get_contents(__DIR__ . '/responses/success/add-to-basket-second-product.request.txt'),
            (string) $response->getSource()
        );
    }


    public function testAddToBasketThirdProductRequest()
    {
        $request = new Request(self::PRESENTER_NAME, 'GET', ['do' => 'addToBasket-3-add']);
        $presenter = $this->createPresenter();
        $response = $presenter->run($request);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertInstanceOf(Template::class, $response->getSource());
        $this->assertSame(
            file_get_contents(__DIR__ . '/responses/success/add-to-basket-third-product.request.txt'),
            (string) $response->getSource()
        );
    }


    protected function createPresenter(): IPresenter
    {
        $categoryPresenter = $this->presenterFactory->createPresenter(self::PRESENTER_NAME);
        $categoryPresenter->autoCanonicalize = false;

        return $categoryPresenter;
    }
}

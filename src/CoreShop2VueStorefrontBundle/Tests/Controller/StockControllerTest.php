<?php declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Tests\Controller;

use CoreShop\Component\Core\Model\Product;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Controller\StockController;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class StockControllerTest extends TestCase
{
    private StockController $controller;

    /** @var Request|ObjectProphecy */
    private $mockRequest;

    /** @var ProductRepositoryInterface|ObjectProphecy */
    private $mockRepository;

    /** @var ContainerInterface|ObjectProphecy */
    private $mockContainer;


    protected function setUp(): void
    {
        $this->mockRequest = $this->prophesize(Request::class);
        $this->mockRepository = $this->prophesize(ProductRepositoryInterface::class);

        $this->mockContainer = $this->prophesize(ContainerInterface::class);
        $this->mockContainer->has('serializer')->willReturn(false);

        $this->controller = new StockController();
        $this->controller->setContainer($this->mockContainer->reveal());
    }

    /**
     * @test
     */
    public function checkStockForSku(): void
    {
        $this->mockRequest->get('sku')->willReturn('1234');

        $mockProduct = $this->prophesize(Product::class);
        $mockProduct->getId()->willReturn('5678');
        $mockProduct->getOnHand()->willReturn(true);

        $this->mockRepository->findOneBy(['sku' => '1234'])->willReturn($mockProduct->reveal());

        $response = $this->controller->checkStockForSku($this->mockRequest->reveal(), $this->mockRepository->reveal());

        self::assertSame(<<<JSON_RESPONSE
                        {"code":200,"result":{"item_id":"5678","product_id":"5678","stock_id":1,"qty":true,"is_in_stock":true,"manage_stock":true}}
                        JSON_RESPONSE
            , $response->getContent()
        );
    }
}

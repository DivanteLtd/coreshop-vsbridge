<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge\DocumentMapper;

use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Mockery as m;
use function CoreShop2VueStorefrontBundle\Tests\helpers\imageFactoryMock;

abstract class BaseProductMapperTest extends MockeryTestCase
{
    /**
     * @return m\MockInterface|CoreShopProduct
     */
    protected function mockProduct()
    {
        $product = m::mock(ProductInterface::class);

        $product->shouldReceive('getName')->once()->andReturn('simple-product');
        $product->shouldReceive('getId')->times(4)->andReturn(9);
        $product->shouldReceive('getCreationDate')->once()->andReturn('1536754067');
        $product->shouldReceive('getModificationDate')->once()->andReturn('1536754067');
        $product->shouldReceive('getEan')->once()->andReturn('EAN12345');
        $product->shouldReceive('getDescription')->once()->andReturn('Description');
        $product->shouldReceive('getShortDescription')->once()->andReturn('Short description');
        $product->shouldReceive('getWeight')->once()->andReturn(200);
        $product->shouldReceive('getSku')->once()->andReturn('SKU-Simple-product');
        $product->shouldReceive('getOnHand')->times(2)->andReturn(10);

        $product->shouldReceive('getImages')->once()->andReturn(
            [
                imageFactoryMock('foo.jpg'),
                imageFactoryMock('fozz.jpg')
            ]
        );

        $product->shouldReceive('getImage')->once()->andReturn('/simple.jpg');
        $product->shouldReceive('getCategories')->once()->andReturn([]);

        return $product;
    }
}

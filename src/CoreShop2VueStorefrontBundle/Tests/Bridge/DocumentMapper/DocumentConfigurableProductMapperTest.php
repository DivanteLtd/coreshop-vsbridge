<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapper\DocumentConfigurableProductMapper;
use CoreShop2VueStorefrontBundle\Bridge\Helper\PriceHelper;
use CoreShop2VueStorefrontBundle\Document\Product;
use CoreShop2VueStorefrontBundle\Repository\AttributeRepository;
use CoreShop2VueStorefrontBundle\Repository\ProductRepository;
use Mockery as m;

class DocumentConfigurableProductMapperTest extends BaseProductMapperTest
{
    /** @test */
    public function itShouldBuildDocumentForConfigurableProduct()
    {
        $this->productRepository->shouldReceive('getOrCreate')->once()->andReturn(new Product());

        $product = $this->mockProduct();

        $this->slugify->shouldReceive('slugify')->atLeast(1)->andReturn('simple-product');

        $variant = mock(\Pimcore\Model\DataObject\Product::class);
        $variant->shouldReceive('getId')->once()->andReturn(10);
        $variant->shouldReceive('getName')->atLeast(1)->andReturn('simple-name');
        $variant->shouldReceive('getSku')->once()->andReturn('SKU123');
        $variant->shouldReceive('getColor')->once()->andReturn('yellow');
        $variant->shouldReceive('getSize')->once()->andReturn('XS');
        $variant->shouldReceive('getStorePrice')->once()->andReturn([1 => 999]);

        $this->productRepository->shouldReceive('getVariants')->once()->with($product)->andReturn([$variant]);

        $esDocument = $this->documentConfigurableProductMapper->mapToDocument($product);

        $this->assertSame('configurable', $esDocument->getTypeId());
    }

    public function setUp()
    {
        $this->productRepository = mock(ProductRepository::class);

        $this->slugify = mock(SlugifyInterface::class);
        $this->priceHelper = mock(PriceHelper::class);

        $this->priceHelper->shouldReceive('getItemPrice')->once()->andReturn(20);

        $this->attributeRepo = mock(AttributeRepository::class);

        $this->documentConfigurableProductMapper = new DocumentConfigurableProductMapper(
            $this->slugify,
            $this->productRepository,
            $this->attributeRepo,
            $this->priceHelper
        );
    }

    /** @var m\Mock $productRepository */
    private $attributeRepo;
    /** @var m\Mock $productRepository */
    private $productRepository;
    /** @var m\Mock */
    private $priceHelper;
    /** @var m\Mock $slugify */
    private $slugify;
    /** @var DocumentConfigurableProductMapper $documentConfigurableProductMapper */
    private $documentConfigurableProductMapper;
}

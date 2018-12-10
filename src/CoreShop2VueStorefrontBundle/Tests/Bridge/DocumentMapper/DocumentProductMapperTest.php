<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapper\DocumentProductMapper;
use CoreShop2VueStorefrontBundle\Bridge\Helper\PriceHelper;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use CoreShop2VueStorefrontBundle\Document\Product;
use CoreShop2VueStorefrontBundle\Document\ProductCategory;
use CoreShop2VueStorefrontBundle\Document\Stock;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;

class DocumentProductMapperTest extends BaseProductMapperTest
{
    /** @test */
    public function itShouldBuildDocumentForSimpleProduct()
    {
        $this->documentFactory->shouldReceive('getOrCreate')->once()->andReturn(new Product());

        $product = $this->mockProduct();

        $this->slugify->shouldReceive('slugify')->once()->andReturn('simple-product');

        $esSimpleProduct = $this->documentProductMapper->mapToDocument($product);

        $this->assertEquals(9, $esSimpleProduct->getId());
        $this->assertEquals(9, $esSimpleProduct->getEsId());
        $this->assertEquals(11, $esSimpleProduct->getAttributeSetId());
        $this->assertEquals(20, $esSimpleProduct->getPrice());
        $this->assertEquals(1, $esSimpleProduct->getStatus());
        $this->assertEquals(4, $esSimpleProduct->getVisibility());
        $this->assertSame('simple', $esSimpleProduct->getTypeId());
        $this->assertSame('simple-product', $esSimpleProduct->getName());
        $this->assertEquals(1, $esSimpleProduct->getAvailability());
        $this->assertEquals("Enabled", $esSimpleProduct->getOptionTextStatus());
        $this->assertEquals(2, $esSimpleProduct->getTaxClassId());
        $this->assertEquals("Taxable Goods", $esSimpleProduct->getOptionTextTaxClassId());
        $this->assertEquals("Description", $esSimpleProduct->getDescription());
        $this->assertEquals("Short description", $esSimpleProduct->getShortDescription());
        $this->assertEquals(200, $esSimpleProduct->getWeight());
        $this->assertEquals("SKU-Simple-product", $esSimpleProduct->getSku());
        $this->assertEquals("simple-product", $esSimpleProduct->getUrlKey());
        $this->assertEquals("/simple.jpg", $esSimpleProduct->getImage());
        $this->assertEquals(0, $esSimpleProduct->getHasOptions());
        $this->assertEquals(0, $esSimpleProduct->getRequiredOptions());
        $this->assertEquals([], $esSimpleProduct->getProductLinks());

        $categories = $esSimpleProduct->getCategories();
        $this->assertTrue($categories[0] instanceof ProductCategory);
        $this->assertEquals("Default category", $categories[0]->getName());
        $this->assertEquals(2, $categories[0]->getCategoryId());

        $this->assertMediaGallery($esSimpleProduct->getMediaGallery());

        /** @var Stock $stock */
        $stock = $esSimpleProduct->getStock();
        $this->assertEquals(9, $stock->productId);
        $this->assertEquals(9, $stock->itemId);
        $this->assertEquals(true, $stock->isInStock);
        $this->assertEquals(10, $stock->qty);
    }

    private function assertMediaGallery(ArrayCollection $mediaGallery)
    {
        $this->assertSame('foo.jpg', $mediaGallery[0]->image);
        $this->assertSame(1, $mediaGallery[0]->pos);
        $this->assertSame('image', $mediaGallery[0]->typ);

        $this->assertSame('fozz.jpg', $mediaGallery[1]->image);
        $this->assertSame(2, $mediaGallery[1]->pos);
        $this->assertSame('image', $mediaGallery[1]->typ);
    }

    public function setUp()
    {
        $this->productRepository = m::mock(ProductRepositoryInterface::class);
        $this->productRepository->shouldReceive('getVariants');

        $this->slugify = m::mock(SlugifyInterface::class);
        $this->priceHelper = m::mock(PriceHelper::class);
        $this->documentFactory = m::mock(DocumentFactory::class);

        $this->priceHelper->shouldReceive('getItemPrice')->once()->andReturn(20);

        $this->documentProductMapper = new DocumentProductMapper(
            $this->slugify,
            $this->productRepository,
            $this->priceHelper,
            $this->documentFactory
        );
    }
    /** @var m\Mock $productRepository */
    private $productRepository;
    /** @var m\Mock $priceHelper */
    private $priceHelper;
    /** @var m\Mock $slugify */
    private $slugify;
    /** @var DocumentProductMapper $documentProductMapper */
    private $documentProductMapper;
}

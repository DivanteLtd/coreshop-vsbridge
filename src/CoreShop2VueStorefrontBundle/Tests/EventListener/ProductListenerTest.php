<?php

namespace CoreShop2VueStorefrontBundle\Tests\EventListener;

use CoreShop2VueStorefrontBundle\Bridge\EnginePersister;
use CoreShop2VueStorefrontBundle\EventListener\ProductListener;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Mockery\Mock;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product;
use Psr\Log\LoggerInterface;

class ProductListenerTest extends MockeryTestCase
{
    /**
     * @test
     * @dataProvider objectToSync
     *
     * @param Mock $possibleObjectToSync
     */
    public function itShouldSynchronizeCategory($possibleObjectToSync)
    {
        $possibleObjectToSync->shouldReceive('isPublished')->once()->andReturnTrue();
        $possibleObjectToSync->shouldReceive('getType')->once()->andReturn(AbstractObject::OBJECT_TYPE_OBJECT);
        $this->enginePersisterMock->shouldReceive('persist')->once()->with($possibleObjectToSync);

        $this->invokeListener($possibleObjectToSync);
    }

    /** @test */
    public function itShouldNotSynchronizeVariants()
    {
        $product = \mock(Product::class);
        $product->shouldReceive('isPublished')->once()->andReturnTrue();
        $product->shouldReceive('getType')->once()->andReturn(AbstractObject::OBJECT_TYPE_VARIANT);

        $actual = $this->invokeListener($product, true);
        $this->assertFalse($actual);
    }

    /** @test */
    public function itShouldNotSynchronizeNotPublishedProduct()
    {
        $product = \mock(Product::class);
        $product->shouldReceive('isPublished')->once()->andReturnFalse();

        $actual = $this->invokeListener($product, true);
        $this->assertFalse($actual);
    }

    /**
     * @param $possibleObjectToSync
     * @param bool $customAssert
     * @return bool
     */
    private function invokeListener($possibleObjectToSync, bool $customAssert = false)
    {
        $event = new DataObjectEvent($possibleObjectToSync);
        $result = $this->listener->postSave($event);

        if (false === $customAssert) {
            $this->assertTrue(true);
        }

        return $result;
    }

    public function objectToSync()
    {
        return [
            [\mock(Category::class)],
            [\mock(Product::class)]
        ];
    }

    public function setUp()
    {
        $this->enginePersisterMock = mock(EnginePersister::class);
        $this->logger = mock(LoggerInterface::class);
        $this->listener = new ProductListener($this->enginePersisterMock, $this->logger);
    }

    /** @var ProductListener */
    private $listener;
    /** @var Mock */
    private $logger;
    /** @var Mock $enginePersisterMock */
    private $enginePersisterMock;
}

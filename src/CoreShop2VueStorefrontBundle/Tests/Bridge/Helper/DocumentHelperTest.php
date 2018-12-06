<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge\Helper;

use CoreShop\Component\Product\Model\Category;
use CoreShop\Component\Product\Model\CategoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\DocumentHelper;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Mockery as m;

class DocumentHelperTest extends MockeryTestCase
{
    /** @test **/
    public function itShouldBuildChildrenIds()
    {
        $categoryOne = new Category();
        $categoryOne->setId(10);
        
        $categoryTwo = new Category();
        $categoryTwo->setId(12);

        $childs = [$categoryOne, $categoryTwo];

        $ids = $this->builderHelper->buildChildrenIds($childs);

        $this->assertSame("10,12", $ids);
    }

    /** @test */
    public function itShouldBuildPath()
    {
        $cat = m::mock(CategoryInterface::class);
        $cat1 = clone $cat;
        $cat1->shouldReceive('getParent')->andReturnNull();

        $cat->shouldReceive('getParent')->andReturn($cat1);
        $cat->shouldReceive('getId')->andReturn(10);

        $path = $this->builderHelper->buildPath($cat);

        $this->assertSame('1/2/10', $path);
    }

    /** @test */
    public function itShouldBuildChildrenCount()
    {
        $categoryIds = "1,2,3,4";
        $count = $this->builderHelper->buildChildrenCount($categoryIds);
        $this->assertSame(4, $count);
    }

    /** @test */
    public function itShouldBuildUrlPath()
    {
        $objectPath = "coreshop/categories/Hardware/Notebooks-HP";
        $urlPath = $this->builderHelper->buildUrlPath($objectPath);

        $expected = $this->builderHelper->buildUrlPath($urlPath);
        $this->assertSame("hardware/notebooks-hp", $expected);
    }

    public function setUp()
    {
        parent::setUp();
        $this->builderHelper = new DocumentHelper();
    }

    /** @var DocumentHelper $builderHelper */
    private $builderHelper;
}

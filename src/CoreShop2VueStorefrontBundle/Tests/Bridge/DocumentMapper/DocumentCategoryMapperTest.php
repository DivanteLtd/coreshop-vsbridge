<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Core\Model\CategoryInterface as BaseCategoryInterface;
use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapper\DocumentCategoryMapper;
use CoreShop2VueStorefrontBundle\Bridge\Helper\DocumentHelper;
use CoreShop2VueStorefrontBundle\Bridge\Model\CategoryInterface;
use CoreShop2VueStorefrontBundle\Document\Category;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Mockery as m;

class DocumentCategoryMapperTest extends MockeryTestCase
{
    /** @test **/
    public function itShouldBuildCategoryElasticSearchDocument()
    {
        $category = m::mock(BaseCategoryInterface::class, CategoryInterface::class);
        $category->shouldReceive('getId')->atLeast(1)->andReturn(2);
        $category->shouldReceive('getName')->atLeast(1)->andReturn('Test');
        $category->shouldReceive('getParent');

        $createdAt = $updatedAt = time();
        $category->shouldReceive('getCreationDate')->once()->andReturn($createdAt);
        $category->shouldReceive('getModificationDate')->once()->andReturn($updatedAt);
        $category->shouldReceive('getFullPath')->once()->andReturn('/Notebooks/Laptops');
        $category->shouldReceive('getIncludeInMenu')->times(2)->andReturnTrue();
        $category->shouldReceive('getIsActive')->once()->andReturnTrue();

        $child = new Category();
        $child->setId(10);
        $child->setName('bar');

        $category->shouldReceive('getChildCategories')->atLeast(1)->andReturn([$child]);

        $this->documentFactory->shouldReceive('getOrCreate')->once()->andReturn(new Category());

        $this->slugify->shouldReceive('slugify')->once()->andReturn('test');

        /** @var Category $esDocument */
        $esDocument = $this->documentCategoryMapper->mapToDocument($category);

        $this->assertSame(2, $esDocument->id);
        $this->assertSame('Test', $esDocument->name);
    }

    public function setUp()
    {
        $this->categoryRepository = m::mock(CategoryRepositoryInterface::class);
        $this->slugify = m::mock(SlugifyInterface::class);
        $this->documentFactory = m::mock(DocumentFactory::class);
        $this->documentCategoryMapper = new DocumentCategoryMapper(
            $this->categoryRepository,
            $this->slugify,
            new DocumentHelper(),
            $this->documentFactory
        );
    }

    /** @var m\Mock $categoryRepository */
    private $categoryRepository;

    /** @var m\Mock $slugify */
    private $slugify;

    /** @var m\Mock */
    private $documentFactory;

    /** @var DocumentCategoryMapper $documentCategoryMapper */
    private $documentCategoryMapper;
}

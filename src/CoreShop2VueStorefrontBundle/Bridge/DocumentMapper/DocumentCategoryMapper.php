<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\DocumentHelper;
use CoreShop2VueStorefrontBundle\Document\Category;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;

class DocumentCategoryMapper extends AbstractMapper implements DocumentMapperInterface
{
    const CATEGORY_DEFAULT_PARENT_ID = 2;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;
    /** @var SlugifyInterface */
    private $slugify;
    /** @var DocumentHelper */
    private $documentHelper;
    /** @var DocumentFactory */
    private $documentFactory;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SlugifyInterface            $slugify
     * @param DocumentHelper              $documentHelper
     * @param DocumentFactory             $documentFactory
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SlugifyInterface $slugify,
        DocumentHelper $documentHelper,
        DocumentFactory $documentFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->slugify = $slugify;
        $this->documentHelper = $documentHelper;
        $this->documentFactory = $documentFactory;
    }

    /**
     * @param \CoreShop\Component\Product\Model\CategoryInterface $category
     * @param int $level
     * @param int $position
     * @param int $parentId
     *
     * @return Category
     */
    public function mapToDocument(
        $category,
        $level = self::CATEGORY_DEFAULT_LEVEL,
        $position = self::CATEGORY_DEFAULT_POSITION,
        $parentId = self::CATEGORY_DEFAULT_PARENT_ID
    ): Category {
        $esCategory = $this->documentFactory->getOrCreate(Category::class, $category->getId());

        $categoryName = $category->getName() ?: $category->getKey();

        $esCategory->setId($category->getId());
        $esCategory->setParentId($parentId);
        $esCategory->setName($categoryName);
        $esCategory->setLevel($level);
        $esCategory->setCreatedAt($this->getDateTime($category->getCreationDate()));
        $esCategory->setUpdatedAt($this->getDateTime($category->getModificationDate()));
        $esCategory->setPath($this->documentHelper->buildPath($category));
        $esCategory->setDisplayMode(self::CATEGORY_DEFAULT_DISPLAY_MODE);
        $esCategory->setPageLayout(self::CATEGORY_DEFAULT_PAGE_LAYOUT);
        $esCategory->setChildrenData($this->buildChildrenData($category->getChildCategories(), ++$level));
        $esCategory->setChildren($this->documentHelper->buildChildrenIds($category->getChildCategories()));
        $esCategory->setChildrenCount($this->documentHelper->buildChildrenCount($esCategory->children));
        $esCategory->setIsAnchor("0");
        $esCategory->setPosition($position);
        $esCategory->setUrlKey($this->slugify->slugify($categoryName));
        $esCategory->setUrlPath($this->documentHelper->buildUrlPath($category->getFullPath()));
        if ($category instanceof \CoreShop2VueStorefrontBundle\Bridge\Model\CategoryInterface) {
            $esCategory->setIncludeInMenu($category->getIncludeInMenu()); //@FIXME
            $esCategory->setIsActive($category->getIsActive());
            $esCategory->setProductCount($category->getIncludeInMenu() ? 1 : 0); //@todo add count for products per category
        } else {
            $esCategory->setIncludeInMenu($category->getPublished());
            $esCategory->setIsActive($category->getPublished());
            $esCategory->setProductCount($category->getPublished() ? 1 : 0); //@todo add count for products per category
        }

        return $esCategory;
    }

    /**
     * @param array $childCategories
     * @param int $level
     * @param int $position
     *
     * @return array
     */
    private function buildChildrenData(array $childCategories, int $level, int $position = 1): array
    {
        $children = [];
        foreach ($childCategories as $category) {
            if ($category instanceof CategoryInterface) {
                $children[] = $this->mapToDocument(
                    $category,
                    $level,
                    $position++,
                    $category->getParentId()
                );
            }
        }

        return $children;
    }
}

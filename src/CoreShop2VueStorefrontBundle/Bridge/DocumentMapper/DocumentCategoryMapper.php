<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperInterface;
use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\DocumentHelper;
use CoreShop2VueStorefrontBundle\Document\Category;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use CoreShop\Component\Store\Model\StoreInterface;

class DocumentCategoryMapper extends AbstractMapper implements DocumentMapperInterface
{
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

    public function supports($object): bool
    {
        return $object instanceof CategoryInterface;
    }

    /**
     * @param \CoreShop\Component\Product\Model\CategoryInterface $category
     * @param StoreInterface|null $store
     * @param string|null $language
     *
     * @return Category
     */
    public function mapToDocument($category, ?StoreInterface $store = null, ?string $language = null): Category
    {
        $esCategory = $this->documentFactory->getOrCreate(Category::class, $category->getId());

        $categoryName = $category->getName($language) ?: $category->getKey();
        $parentCategory = $category->getParentCategory();

        $level = $this->documentHelper->countSeparator($category->getFullPath(), '/') + 1;

        $esCategory->setId($category->getId());
        $esCategory->setParentId($parentCategory ? $parentCategory->getId() : 0);
        $esCategory->setName($categoryName);
        $esCategory->setCreatedAt($this->getDateTime($category->getCreationDate()));
        $esCategory->setUpdatedAt($this->getDateTime($category->getModificationDate()));
        $esCategory->setPath($this->documentHelper->buildPath($category));
        $esCategory->setDisplayMode(self::CATEGORY_DEFAULT_DISPLAY_MODE);
        $esCategory->setPageLayout(self::CATEGORY_DEFAULT_PAGE_LAYOUT);
        $esCategory->setChildrenData($this->buildChildrenData($category->getChildCategories(), $store, $language, $level));
        $esCategory->setChildren($this->documentHelper->buildChildrenIds($category->getChildCategories()));
        $esCategory->setChildrenCount($this->documentHelper->countSeparator($esCategory->children, ','));
        $esCategory->setIsAnchor("0");

        $esCategory->setLevel($level);
        $esCategory->setPosition($category->getIndex());

        if ($category instanceof \CoreShop2VueStorefrontBundle\Bridge\Model\UrlInterface) {
            $esCategory->setSlug($category->getSlug());
            $esCategory->setUrlKey($category->getUrlKey());
            $esCategory->setUrlPath($category->getUrlPath());
        } else {
            $slug = $this->slugify->slugify($categoryName);
            $esCategory->setSlug($slug);
            $esCategory->setUrlKey($slug);
            $esCategory->setUrlPath($this->documentHelper->buildUrlPath($category, $language));
        }

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
     * @param array<\CoreShop\Component\Product\Model\CategoryInterface> $categories
     * @param StoreInterface|null $store
     * @param string|null $language
     *
     * @return array<CoreShop2VueStorefrontBundle\Document\Category>
     */
    private function buildChildrenData(array $categories, ?StoreInterface $store = null, ?string $language = null): array
    {
        $children = [];
        foreach ($categories as $category) {
            if ($category instanceof CategoryInterface) {
                $children[] = $this->mapToDocument($category, $store, $language);
            }
        }

        return $children;
    }
}

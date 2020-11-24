<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperInterface;
use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\DocumentHelper;
use CoreShop2VueStorefrontBundle\Document\Category;
use ONGR\ElasticsearchBundle\Service\IndexService;

class DocumentCategoryMapper extends AbstractMapper implements DocumentMapperInterface
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;
    /** @var SlugifyInterface */
    private $slugify;
    /** @var DocumentHelper */
    private $documentHelper;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SlugifyInterface            $slugify
     * @param DocumentHelper              $documentHelper
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SlugifyInterface $slugify,
        DocumentHelper $documentHelper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->slugify = $slugify;
        $this->documentHelper = $documentHelper;
    }

    public function supports($objectOrClass): bool
    {
        return is_a($object, CategoryInterface::class);
    }

    /**
     * @param \CoreShop\Component\Product\Model\CategoryInterface $category
     */
    public function mapToDocument(IndexService $service, $category, ?string $language = null): Category
    {
        $esCategory = $this->find($service, $category);

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
        $esCategory->setChildrenData($this->buildChildrenData($service, $category->getChildCategories(), $level));
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

    public function getDocumentClass(): string
    {
        return Category::class;
    }

    /**
     * @param array<\CoreShop\Component\Product\Model\CategoryInterface> $categories
     *
     * @return array<CoreShop2VueStorefrontBundle\Document\Category>
     */
    private function buildChildrenData(IndexService $service, array $categories): array
    {
        $children = [];
        foreach ($categories as $category) {
            if ($category instanceof CategoryInterface) {
                $children[] = $this->mapToDocument($service, $category);
            }
        }

        return $children;
    }
}

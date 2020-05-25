<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Helper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Product\Model\CategoryInterface;

class DocumentHelper
{
    const CATEGORY_DEFAULT_PATH = "1/2";

    /**
     * @var SlugifyInterface
     */
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function buildChildrenIds(array $childCategories): string
    {
        return implode(",", array_map(function ($category) {
            if ($category instanceof CategoryInterface) {
                return $category->getId();
            }
        }, $childCategories));
    }

    public function buildParents(CategoryInterface $current): array
    {
        $parents = [];
        do {
            $parents[] = $current;
            $current = $current->getParentCategory();
        } while ($current !== null);

        return array_reverse($parents);
    }

    public function buildPath(CategoryInterface $category): string
    {
        $chunks = array_map(function (CategoryInterface $category): string {
            return $category->getId();
        }, $this->buildParents($category));

        return sprintf("%s/%s", self::CATEGORY_DEFAULT_PATH, implode("/", $chunks));
    }

    public function countSeparator(string $haystack, string $needle): int
    {
        return substr_count(trim($haystack, $needle), $needle);
    }

    public function buildUrlPath(CategoryInterface $category): string
    {
        $chunks = array_map(function (CategoryInterface $category): string {
            return $this->slugify->slugify($category->getName());
        }, $this->buildParents($category));

        return implode('/', $chunks);
    }
}

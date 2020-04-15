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

    public function buildPath(CategoryInterface $category): string
    {
        $chunks = [
            $category->getId()
        ];
        while ($category->getParentCategory() !== null) {
            $chunks[] = $category->getId();

            $category = $category->getParent();
        }

        return sprintf("%s/%s", self::CATEGORY_DEFAULT_PATH, implode("/", array_reverse($chunks)));
    }

    public function countSeparator(string $haystack, string $needle): int
    {
        return substr_count(trim($haystack, $needle), $needle);
    }

    public function buildUrlPath(CategoryInterface $category): string
    {
        $chunks = [
            $this->slugify->slugify($category->getName())
        ];
        while ($category->getParentCategory() !== null) {
            $category = $category->getParentCategory();

            if ($category !== null) {
                $chunks[] = $this->slugify->slugify($category->getName());
            }
        }

        return implode('/', array_reverse($chunks));
    }
}

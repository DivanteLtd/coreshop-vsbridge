<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Helper;

use CoreShop\Component\Product\Model\CategoryInterface;

class DocumentHelper
{
    const CATEGORY_DEFAULT_PATH = "1/2";

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
        $chunks = [];
        while ($category->getParent() != null) {
            if ($category instanceof CategoryInterface) {
                $chunks[] = $category->getId();
            }
            $category = $category->getParent();
        }

        $path = empty($chunks)
            ? $category->getId()
            : implode("/", array_reverse($chunks));

        return sprintf("%s/%s", self::CATEGORY_DEFAULT_PATH, $path);
    }

    public function buildChildrenCount(string $children): int
    {
        return count(explode(',', $children));
    }

    public function buildUrlPath(string $path): string
    {
        return str_replace("coreshop/categories/", "", mb_strtolower(ltrim($path, '/')));
    }
}

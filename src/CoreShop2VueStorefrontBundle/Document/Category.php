<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\Document()
 */
class Category
{
    // @codingStandardsIgnoreStart
    /** @ES\Id() */
    public $_id;
    // @codingStandardsIgnoreEnd

    /** @ES\Property(type="integer") */
    public $id;

    /** @ES\Property(type="integer", name="parent_id") */
    public $parent_id;

    /** @ES\Property(type="text") */
    public $name;

    /** @ES\Property(type="boolean", name="is_active") */
    public $is_active;

    /** @ES\Property(type="integer") */
    public $position;

    /** @ES\Property(type="integer") */
    public $level;

    /** @ES\Property(type="integer", name="product_count") */
    public $product_count;

    /** @ES\Property() */
    public $children_data;

    /** @ES\Property(type="text") */
    public $children;

    /** @ES\Property(type="date", name="created_at") */
    public $created_at;

    /** @ES\Property(type="date", name="updated_at") */
    public $updated_at;

    /** @ES\Property(type="text") */
    public $path;

    /** @ES\Property(name="available_sort_by") */
    public $available_sort_by = [];

    /** @ES\Property(type="boolean", name="include_in_menu") */
    public $include_in_menu;

    /** @ES\Property(type="text", name="display_mode") */
    public $display_mode;

    /** @ES\Property(type="text", name="is_anchor") */
    public $is_anchor;

    /** @ES\Property(type="text", name="page_layout") */
    public $page_layout;

    /** @ES\Property(type="text", name="children_count") */
    public $children_count;

    /** @ES\Property(type="text", name="url_key") */
    public $url_key;

    /** @ES\Property(type="text", name="url_path") */
    public $url_path;

    public function setId(int $id)
    {
        $this->setEsId($id);
        $this->id = $id;
    }

    public function setEsId(int $id)
    {
        $this->_id = $id;
    }

    /**
     * @param mixed $parentId
     */
    public function setParentId(int $parentId): void
    {
        $this->parent_id = $parentId;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive(?bool $isActive = true): void
    {
        $this->is_active = $isActive;
    }

    /**
     * @param mixed $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @param mixed $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @param mixed $productCount
     */
    public function setProductCount(int $productCount): void
    {
        $this->product_count = $productCount;
    }

    /**
     * @param mixed $childrenData
     */
    public function setChildrenData(array $childrenData): void
    {
        $this->children_data = $childrenData;
    }

    /**
     * @param mixed $children
     */
    public function setChildren(string $children): void
    {
        $this->children = $children;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->created_at = $createdAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * @param mixed $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param mixed $availableSortBy
     */
    public function setAvailableSortBy(string $availableSortBy): void
    {
        $this->available_sort_by = $availableSortBy;
    }

    /**
     * @param mixed $includeInMenu
     */
    public function setIncludeInMenu(?bool $includeInMenu = true): void
    {
        $this->include_in_menu = $includeInMenu;
    }

    /**
     * @param mixed $displayMode
     */
    public function setDisplayMode(string $displayMode): void
    {
        $this->display_mode = $displayMode;
    }

    /**
     * @param mixed $isAnchor
     */
    public function setIsAnchor(string $isAnchor): void
    {
        $this->is_anchor = $isAnchor;
    }

    /**
     * @param mixed $pageLayout
     */
    public function setPageLayout(string $pageLayout): void
    {
        $this->page_layout = $pageLayout;
    }

    /**
     * @param mixed $childrenCount
     */
    public function setChildrenCount(int $childrenCount): void
    {
        $this->children_count = $childrenCount;
    }

    /**
     * @param mixed $urlKey
     */
    public function setUrlKey(string $urlKey): void
    {
        $this->url_key = $urlKey;
    }

    /**
     * @param mixed $urlPath
     */
    public function setUrlPath(string $urlPath): void
    {
        $this->url_path = $urlPath;
    }
}

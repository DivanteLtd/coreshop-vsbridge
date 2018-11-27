<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType()
 */
class ProductCategory
{
    /** @ES\Property(type="integer") */
    public $categoryId;

    /** @ES\Property(type="boolean") */
    public $isParent = true;

    /** @ES\Property(type="string") */
    public $name;

    public function __construct(string $name = null, int $categoryId = null)
    {
        $this->name = $name;
        $this->categoryId = $categoryId;
    }

    /** @return mixed */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /** @param mixed $categoryId */
    public function setCategoryId($categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    /** @return mixed */
    public function getName()
    {
        return $this->name;
    }

    /** @param mixed $name */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /** @return mixed */
    public function getIsParent()
    {
        return $this->isParent;
    }

    /** @param mixed $isParent */
    public function setIsParent($isParent): void
    {
        $this->isParent = $isParent;
    }
}

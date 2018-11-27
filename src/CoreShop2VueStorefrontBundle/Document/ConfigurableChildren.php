<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType()
 */
class ConfigurableChildren
{
    /** @ES\Property(type="integer") */
    public $id;

    /** @ES\Property(type="integer") */
    public $status = 1;

    /** @ES\Property(type="text") */
    public $sku;

    /** @ES\Property(type="text") */
    public $urlKey;

    /** @ES\Property(type="text") */
    public $name;

    /** @ES\Property(type="float") */
    public $price;

    /** @ES\Property(type="string") */
    public $color;

    /** @ES\Property(type="string") */
    public $size;

    /** @ES\Property(type="float") */
    public $finalPrice;

    /** @ES\Property(type="float") */
    public $maxPrice;

    /** @ES\Property(type="float") */
    public $maxRegularPrice;

    /** @ES\Property(type="float") */
    public $minimalPrice;

    /** @ES\Property(type="float") */
    public $minimalRegularPrice;

    /** @ES\Property(type="text") */
    public $image;

    /** @ES\Property(type="string") */
    public $taxClassId = "2";

    /** @ES\Property() */
    public $categoryIds = [];

    /** @ES\Property(type="string") */
    public $hasOptions = "0";

    /** @ES\Property(type="string") */
    public $requiredOptions = "0";

    /** @ES\Property(type="string") */
    public $msrpDisplayActualPriceType = "0";

    public function setSku(string $sku)
    {
        $this->sku = $sku;
    }

    public function setColor(?string $color)
    {
        $this->color = $color;
    }

    public function setUrlKey(string $urlKey)
    {
        $this->urlKey = $urlKey;
    }

    public function setPrice(int $standardPrice)
    {
        $this->price = $standardPrice;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    public function setSize(?string $size)
    {
        $this->size = $size;
    }
}

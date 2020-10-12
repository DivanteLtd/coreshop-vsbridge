<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType()
 */
class ConfigurableOption
{
    /** @ES\Property(type="integer") */
    public $id;

    /** @ES\Property(type="integer") */
    public $attributeId;

    /** @ES\Property(type="text") */
    public $label;

    /** @ES\Property(type="integer") */
    public $position;

    public $values = [];

    /** @ES\Property(type="integer") */
    public $productId;

    /** @ES\Property(type="text") */
    public $attributeCode;

    /** @param mixed $id */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /** @param mixed $attributeId */
    public function setAttributeId($attributeId): void
    {
        $this->attributeId = $attributeId;
    }

    /** @param mixed $label */
    public function setLabel($label): void
    {
        $this->label = $label;
    }

    /** @param mixed $position */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /** @param mixed $values */
    public function setValues($values): void
    {
        $this->values = $values;
    }

    /** @param mixed $productId */
    public function setProductId($productId): void
    {
        $this->productId = $productId;
    }

    /** @param mixed $attributeCode */
    public function setAttributeCode($attributeCode): void
    {
        $this->attributeCode = $attributeCode;
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Attribute;

use CoreShop2VueStorefrontBundle\Repository\AttributeRepository;
use Exception;

class AttributeResolver
{
    /** @var AttributeRepository */
    private $attributeRepository;

    /**
     * AttributeResolver constructor.
     *
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array $attributes
     *
     * @return array
     * @throws Exception
     */
    public function resolve(array $attributes): array
    {
        $ret = [];
        $attributes = $attributes['extension_attributes']['configurable_item_options']
            ?? $attributes['extensions_attributes']['configurable_item_options']
            ?? [];
        foreach ($attributes as $k => $attribute) {
            $attributeObj = $this->attributeRepository->findOneById($attribute['option_id']);
            if (!$attributeObj) {
                throw new Exception(sprintf('Unknown attribute with id %d', $attribute['option_id']));
            }
            $ret[$attributeObj->getAttributeCode()] = $attribute['attribute_value'];
        }
        return $ret;
    }
}

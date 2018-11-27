<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Attribute;

class AttributeIdGenerator
{
    public function getId(string $className, string $fieldName): int
    {
        return crc32(sprintf("%s%s", $className, $fieldName));
    }
}

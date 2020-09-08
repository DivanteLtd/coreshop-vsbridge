<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;

interface DocumentMapperInterface
{
    /**
     * @param AbstractObject|Data $object
     */
    public function supports($object): bool;

    /**
     * @param AbstractObject|Data $object
     */
    public function mapToDocument($object, ?string $language = null);
}

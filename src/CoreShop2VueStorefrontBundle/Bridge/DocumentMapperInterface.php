<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use CoreShop\Component\Store\Model\StoreInterface;

interface DocumentMapperInterface
{
    /**
     * @param AbstractObject|Data $object
     */
    public function supports($object): bool;

    /**
     * @param AbstractObject|Data $object
     * @param StoreInterface|null $store
     * @param string|null $language
     */
    public function mapToDocument($object, ?StoreInterface $store = null, ?string $language = null);
}

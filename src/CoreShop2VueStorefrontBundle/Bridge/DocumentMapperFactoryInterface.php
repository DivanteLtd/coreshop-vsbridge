<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;

interface DocumentMapperFactoryInterface
{
    /**
     * @param AbstractObject|Data $object
     */
    public function factory(object $object): DocumentMapperInterface;

    public function getDocumentClass(string $objectClass): string;
}

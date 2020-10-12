<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use ONGR\ElasticsearchBundle\Service\IndexService;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;

interface DocumentMapperInterface
{
    /**
     * @param AbstractObject|Data|string $objectOrClass
     */
    public function supports($objectOrClass): bool;

    /**
     * @param AbstractObject|Data $object
     */
    public function mapToDocument(IndexService $service, object $object, ?string $language = null);

    public function getDocumentClass(): string;
}

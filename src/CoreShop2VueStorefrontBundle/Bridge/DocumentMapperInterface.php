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
    public function mapToDocument(object $object, object $document, ?string $language = null);

    /**
     * @param AbstractObject|Data $object
     */
    public function find(IndexService $service, object $object): object;

    public function getDocumentClass(): string;
}

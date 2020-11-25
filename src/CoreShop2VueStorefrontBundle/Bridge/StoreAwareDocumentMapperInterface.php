<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Core\Model\StoreInterface;
use ONGR\ElasticsearchBundle\Service\IndexService;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition\Data;

interface StoreAwareDocumentMapperInterface extends DocumentMapperInterface
{
    /**
     * @param AbstractObject|Data $object
     */
    public function mapToDocument(IndexService $service, object $object, ?string $language = null, ?StoreInterface $store = null);
}

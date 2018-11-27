<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use Pimcore\Model\DataObject\AbstractObject;

class DocumentMapperFactory
{
    /** @var DocumentProductMapper */
    private $documentProductMapper;
    /** @var DocumentCategoryMapper */
    private $documentCategoryMapper;
    /** @var DocumentConfigurableProductMapper */
    private $configurableProductMapper;

    public function __construct(
        DocumentProductMapper $documentProductMapper,
        DocumentCategoryMapper $documentCategoryMapper,
        DocumentConfigurableProductMapper $configurableProductMapper
    ) {
        $this->documentProductMapper = $documentProductMapper;
        $this->documentCategoryMapper = $documentCategoryMapper;
        $this->configurableProductMapper = $configurableProductMapper;
    }
    
    public function factory(AbstractObject $object)
    {
        $variants = $object->getChildren([AbstractObject::OBJECT_TYPE_VARIANT], true);
        switch ($object) {
            case $object instanceof CategoryInterface:
                return $this->documentCategoryMapper;
            case $object instanceof ProductInterface && true === empty($variants):
                return $this->documentProductMapper;
            case $object instanceof ProductInterface && false === empty($variants):
                return $this->configurableProductMapper;
            default:
                throw new \InvalidArgumentException();
        }
    }
}

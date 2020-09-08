<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use Pimcore\Model\DataObject\AbstractObject;

class DocumentMapperFactory implements DocumentMapperFactoryInterface
{
    /**
     * @var iterable<DocumentMapperInterface>
     */
    private $documentMappers;

    public function __construct(iterable $documentMappers)
    {
        $this->documentMappers = $documentMappers;
    }

    public function factory($object): DocumentMapperInterface
    {
        foreach ($this->documentMappers as $documentMapper) {
            if ($documentMapper->supports($object)) {
                return $documentMapper;
            }
        }

        throw new \LogicException('No mapper available');
    }
}

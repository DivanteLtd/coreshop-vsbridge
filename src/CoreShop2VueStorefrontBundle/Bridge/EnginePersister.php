<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Product\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapper\DocumentMapperFactory;
use ONGR\ElasticsearchBundle\Service\Manager;

class EnginePersister
{
    /** @var Manager */
    private $manager;
    /** @var DocumentMapperFactory */
    private $documentMapperFactory;

    public function __construct(Manager $manager, DocumentMapperFactory $documentMapperFactory)
    {
        $this->manager = $manager;
        $this->documentMapperFactory = $documentMapperFactory;
    }

    /**
     * @param ProductInterface $object
     * @param string $lang
     * @throws \ONGR\ElasticsearchBundle\Exception\BulkWithErrorsException
     */
    public function persist($object, string $lang = 'pl'): void
    {
        $documentMapper = $this->documentMapperFactory->factory($object);
        $esDocument = $documentMapper->mapToDocument($object);
        $this->manager->persist($esDocument);
        $this->manager->commit();
    }
}

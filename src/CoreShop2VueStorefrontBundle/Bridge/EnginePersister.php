<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperFactory;
use ONGR\ElasticsearchBundle\Exception\BulkWithErrorsException;
use ONGR\ElasticsearchBundle\Service\Manager;
use CoreShop\Component\Store\Model\StoreInterface;

class EnginePersister
{
    /** @var Manager */
    private $manager;
    /** @var DocumentMapperFactory */
    private $documentMapperFactory;
    /** @var string|null */
    private $language;
    /** @var StoreInterface|null */
    private $store;

    /** @var null|bool */
    private $indexExists;

    public function __construct(Manager $manager, DocumentMapperFactory $documentMapperFactory, ?StoreInterface $store = null, ?string $language = null)
    {
        $this->manager = $manager;
        $this->documentMapperFactory = $documentMapperFactory;
        $this->language = $language;
        $this->store = $store;
    }

    /**
     * @param ProductInterface $object
     * @throws BulkWithErrorsException
     */
    public function persist($object): void
    {
        if ($this->indexExists !== true) {
            if (!$this->manager->indexExists()) {
                $this->manager->createIndex();
            }
            $this->indexExists = true;
        }

        $documentMapper = $this->documentMapperFactory->factory($object);
        $esDocument = $documentMapper->mapToDocument($object, $this->store, $this->language);
        $this->manager->persist($esDocument);
        $this->manager->commit();
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperFactory;
use CoreShop2VueStorefrontBundle\Document\Attribute;
use CoreShop2VueStorefrontBundle\Document\Category;
use CoreShop2VueStorefrontBundle\Document\Product;
use ONGR\ElasticsearchBundle\Exception\BulkWithErrorsException;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchBundle\Service\Manager;

class EnginePersister
{
    /** @var Manager */
    private $indexService;
    /** @var DocumentMapperFactory */
    private $documentMapperFactory;
    /** @var string|null */
    private $language;

    /** @var null|bool */
    private $indexExists;

    public function __construct(IndexService $indexService, DocumentMapperFactory $documentMapperFactory, ?string $language = null)
    {
        $this->indexService = $indexService;
        $this->documentMapperFactory = $documentMapperFactory;
        $this->language = $language;
    }

    /**
     * @param ProductInterface $object
     * @throws BulkWithErrorsException
     */
    public function persist($object): void
    {
        if ($this->indexExists !== true) {
            if (!$this->indexService->indexExists()) {
                $this->indexService->createIndex();
            }
            $this->indexExists = true;
        }

        $documentMapper = $this->documentMapperFactory->factory($object);
        $document = $documentMapper->mapToDocument($this->indexService, $object, $this->language);
        $this->indexService->persist($document);
        $this->indexService->commit();
        $this->indexService->flush();
    }
}

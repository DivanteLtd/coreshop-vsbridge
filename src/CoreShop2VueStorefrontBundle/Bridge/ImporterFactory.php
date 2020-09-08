<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperFactoryInterface;

class ImporterFactory
{
    /**
     * @var PersisterFactory
     */
    private $persisterFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    public function __construct(PersisterFactory $persisterFactory, ProductRepositoryInterface $productRepository, CategoryRepositoryInterface $categoryRepository)
    {
        $this->persisterFactory = $persisterFactory;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return array<ImporterFactory>
     */
    public function create(?string $store = null, ?string $language = null, ?string $type = null): array
    {
        $persisters = $this->persisterFactory->create($store, $language, $type);
        $importers = [];
        foreach ($persisters as $config) {
            switch ($config['type']) {
                case PersisterFactory::TYPE_CATEGORY:
                    $repository = $this->categoryRepository;
                    break;
                case PersisterFactory::TYPE_PRODUCT:
                    $repository = $this->productRepository;
                    break;
            }

            $importers[] = new ElasticsearchImporter($repository, $config['persister'], $config['store'], $config['language'], $config['type']);
        }

        return $importers;
    }
}

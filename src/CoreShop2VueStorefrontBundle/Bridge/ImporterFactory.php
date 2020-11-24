<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperFactoryInterface;

class ImporterFactory
{
    /**
     * @var PersisterFactory
     */
    private $persisterFactory;

    /**
     * @var OptionsResolver
     */
    private $resolver;

    public function __construct(PersisterFactory $persisterFactory)
    {
        $this->persisterFactory = $persisterFactory;
    }

    /**
     * @return array<ImporterFactory>
     */
    public function create(?string $store = null, ?string $type = null, ?string $language = null, ?string $currency = null): array
    {
        $persisters = $this->persisterFactory->create($store, $type, $language, $currency);
        $importers = [];
        foreach ($persisters as $config) {
            $importers[] = new ElasticsearchImporter($config['repository'], $config['persister'], $config['store'], $config['type'], $config['language'], $config['currency'], $config['concreteStore']);
        }

        return $importers;
    }
}

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
    public function create(?string $site = null, ?string $type = null, ?string $language = null, ?string $store = null): array
    {
        $persisters = $this->persisterFactory->create($site, $type, $language, $store);
        $importers = [];
        foreach ($persisters as $config) {
            $importers[] = new ElasticsearchImporter($config['repository'], $config['persister'], $config['site'], $config['type'], $config['language'], $config['store']);
        }

        return $importers;
    }
}

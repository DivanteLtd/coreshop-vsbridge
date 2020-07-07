<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;

class ElasticsearchImporter implements ImporterInterface
{
    private $list;
    private $store;
    private $language;
    private $type;

    public function __construct(PimcoreRepositoryInterface $repository, EnginePersister $persister, string $store, string $language, string $type)
    {
        $this->list = $repository->getList();
        $this->persister = $persister;
        $this->store = $store;
        $this->language = $language;
        $this->type = $type;
    }

    public function describe(): string
    {
        return sprintf('%1$s: %2$s (%3$s)', $this->store, $this->type, $this->language);
    }

    public function count(): int
    {
        return $this->list->count();
    }

    public function import(callable $callback): void
    {
        $listing = new BatchListing($this->list, 100);
        foreach ($listing as $object) {
            $this->persister->persist($object);

            $callback($object);
        }
    }
}

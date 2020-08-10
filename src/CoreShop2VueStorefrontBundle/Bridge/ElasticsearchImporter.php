<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use Pimcore\Model\Listing\AbstractListing;

class ElasticsearchImporter implements ImporterInterface
{
    private $repository;
    private $list;
    private $store;
    private $language;
    private $type;

    public function __construct(PimcoreRepositoryInterface $repository, EnginePersister $persister, string $store, string $language, string $type)
    {
        $this->repository = $repository;
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
        return $this->getList()->count();
    }

    public function import(callable $callback): void
    {
        $listing = new BatchListing($this->getList(), 100);
        foreach ($listing as $object) {
            $this->persister->persist($object);

            $callback($object);
        }
    }

    private function getList(): AbstractListing
    {
        if (null === $this->list) {
            $this->list = $this->repository->getList();
        }

        return $this->list;
    }
}

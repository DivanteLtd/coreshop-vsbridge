<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use CoreShop\Component\Store\Model\StoreInterface;
use CoreShop2VueStorefrontBundle\Repository\StoreAwareRepositoryInterface;
use Pimcore\Model\Listing\AbstractListing;

class ElasticsearchImporter implements ImporterInterface
{
    private $repository;
    private $list;
    private $store;
    private $language;
    private $type;
    private $persister;
    /** @var StoreInterface|null */
    private $concreteStore;

    public function __construct(RepositoryInterface $repository, EnginePersister $persister, string $store, string $language, string $type, ?StoreInterface $concreteStore = null)
    {
        $this->repository = $repository;
        $this->persister = $persister;
        $this->store = $store;
        $this->language = $language;
        $this->type = $type;
        $this->concreteStore = $concreteStore;
    }

    public function describe(): string
    {
        return sprintf('%1$s: %2$s (%3$s)', $this->store, $this->type, $this->language);
    }

    public function count(): int
    {
        // TODO: with Pimcore 6.8 can just
        // return count($this->getList());
        $list = $this->getList();
        if ($list instanceof AbstractListing) {
            return $this->getList()->count();
        }

        return \count($list);
    }

    public function import(callable $callback): void
    {
        $list = $this->getList();
        if ($list instanceof AbstractListing) {
            $listing = new BatchListing($list, 100);
        } elseif (is_iterable($list)) {
            $listing = $list;
        }

        foreach ($listing as $object) {
            $this->persister->persist($object);

            $callback($object);
        }
    }

    private function getList(): iterable
    {
        if (null === $this->list) {
            if ($this->repository instanceof PimcoreRepositoryInterface) {
                $this->list = $this->repository->getList();

                if ($this->repository instanceof StoreAwareRepositoryInterface && $this->concreteStore instanceof StoreInterface) {
                    $this->repository->addStoreCondition($this->list, $this->concreteStore);
                }
            } else {
                $this->list = $this->repository->findAll();
            }
        }

        return $this->list;
    }
}

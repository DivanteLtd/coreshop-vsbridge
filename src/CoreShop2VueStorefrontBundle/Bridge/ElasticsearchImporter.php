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
    private $site;
    private $type;
    private $language;
    private $persister;
    /** @var StoreInterface */
    private $store;
    /** @var null|\DateTimeInterface */
    private $since;

    public function __construct(RepositoryInterface $repository, EnginePersister $persister, string $site, string $type, string $language, StoreInterface $store, ?\DateTimeInterface $since = null)
    {
        $this->repository = $repository;
        $this->persister = $persister;
        $this->site = $site;
        $this->type = $type;
        $this->language = $language;
        $this->store = $store;
        $this->since = $since;
    }

    public function describe(): string
    {
        return sprintf('%1$s: %2$s (%3$s, %4$s)', $this->site, $this->type, $this->language, $this->store->getCurrency()->getIsoCode());
    }

    public function getTarget(): string
    {
        return $this->persister->getIndexName();
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
            $callback($object);

            $this->persister->persist($object);
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

                if ($this->since !== null) {
                    $this->list->addConditionParam('o_modificationDate >= ?', $this->since->getTimestamp());
                }
            } else {
                // TODO: how to do since here?
                $this->list = $this->repository->findAll();
            }
        }

        return $this->list;
    }
}

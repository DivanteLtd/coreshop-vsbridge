<?php


namespace CoreShop2VueStorefrontBundle\Repository;

use CoreShop\Component\Store\Model\StoreInterface;
use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use Pimcore\Model\Listing\AbstractListing;

interface StoreAwareRepositoryInterface extends PimcoreRepositoryInterface
{

    public function addStoreCondition(AbstractListing $listing, StoreInterface $concreteStore);

}

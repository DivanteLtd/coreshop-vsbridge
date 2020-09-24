<?php


namespace CoreShop2VueStorefrontBundle\Repository;


use CoreShop\Bundle\CoreBundle\Pimcore\Repository\ProductRepository as BaseProductRepository;
use CoreShop\Component\Store\Model\StoreInterface;
use Pimcore\Model\Listing\AbstractListing;

class ProductRepository extends BaseProductRepository implements StoreAwareRepositoryInterface
{

    public function addStoreCondition(AbstractListing $listing, StoreInterface $store)
    {
        $listing->addConditionParam('stores LIKE ?', '%,' . $store->getId() . ',%');
    }
}

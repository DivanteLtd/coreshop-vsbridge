<?php


namespace CoreShop2VueStorefrontBundle\Repository;


use Pimcore\Model\Listing\AbstractListing;
use CoreShop\Component\Store\Model\StoreInterface;
use CoreShop\Bundle\CoreBundle\Pimcore\Repository\CategoryRepository as BaseCategoryRepository;

class CategoryRepository extends BaseCategoryRepository implements StoreAwareRepositoryInterface
{

    public function addStoreCondition(AbstractListing $listing, StoreInterface $store)
    {
        $listing->addConditionParam('stores LIKE ?', '%,' . $store->getId() . ',%');
    }
}

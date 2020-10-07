<?php


namespace CoreShop2VueStorefrontBundle\Repository;

use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;
use Pimcore\Model\Listing\AbstractListing;

interface LanguageAwareRepositoryInterface extends PimcoreRepositoryInterface
{

    public function addLanguageCondition(AbstractListing $listing, string $language);

}
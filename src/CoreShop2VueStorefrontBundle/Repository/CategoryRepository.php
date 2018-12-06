<?php

namespace CoreShop2VueStorefrontBundle\Repository;

use Pimcore\Model\DataObject\Category\Listing;

class CategoryRepository extends BaseRepository implements RepositoryInterface
{
    public function fetchAll()
    {
        $categories = new Listing();
        return $categories->load();
    }
}

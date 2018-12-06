<?php

namespace CoreShop2VueStorefrontBundle\Repository;

use CoreShop2VueStorefrontBundle\Document\Attribute;
use CoreShop2VueStorefrontBundle\Document\Category;
use CoreShop2VueStorefrontBundle\Document\Product;
use ONGR\ElasticsearchBundle\Service\Manager;

class BaseRepository
{
    /** @var Manager */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $className
     * @param int $objectId
     * @return Attribute|Product|Category $object
     */
    public function getOrCreate(string $className, $objectId)
    {
        $object = $this->manager->find($className, $objectId);
        if (!$object && class_exists($className, false)) {
            $object = new $className();
        }

        return $object;
    }
}

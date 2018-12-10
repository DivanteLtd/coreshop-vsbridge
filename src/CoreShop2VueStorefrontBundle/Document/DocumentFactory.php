<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Service\Manager;

class DocumentFactory
{
    /** @var Manager */
    protected $manager;

    /**
     * DocumentFactory constructor.
     *
     * @param Manager $manager
     */
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

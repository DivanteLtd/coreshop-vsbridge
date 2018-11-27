<?php

namespace CoreShop2VueStorefrontBundle\Repository;

use CoreShop\Component\Product\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\Attribute\AttributeIdGenerator;
use CoreShop2VueStorefrontBundle\Document\Attribute;
use ONGR\ElasticsearchBundle\Service\Manager;

class AttributeRepository extends BaseRepository
{
    /** @var AttributeIdGenerator */
    private $attributeIdGenerator;

    /**
     * @param Manager $manager
     * @param AttributeIdGenerator $attributeIdGenerator
     */
    public function __construct(Manager $manager, AttributeIdGenerator $attributeIdGenerator)
    {
        parent::__construct($manager);
        $this->attributeIdGenerator = $attributeIdGenerator;
    }

    /**
     * @param Attribute|null $attribute
     *
     * @return array
     */
    public function getOptions(Attribute $attribute = null): array
    {
        return $attribute ? array_map(function ($item) {
            return $item['value'];
        }, $attribute->getOptions()) : [];
    }

    /**
     * @param ProductInterface $product
     * @param string $fieldName
     *
     * @return Attribute|null
     */
    public function findOneOrNull(ProductInterface $product, string $fieldName):? Attribute
    {
        $id = $this->attributeIdGenerator->getId($product->getClassName(), $fieldName);
        return $this->manager->find(Attribute::class, $id);
    }

    /**
     * @param int $id
     *
     * @return Attribute|null
     */
    public function findOneById(int $id):? Attribute
    {
        return $this->manager->find(Attribute::class, $id);
    }
}

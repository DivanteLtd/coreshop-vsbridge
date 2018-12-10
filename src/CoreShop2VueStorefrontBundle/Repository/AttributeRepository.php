<?php

namespace CoreShop2VueStorefrontBundle\Repository;

use CoreShop\Component\Product\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\Attribute\AttributeIdGenerator;
use CoreShop2VueStorefrontBundle\Document\Attribute;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use ONGR\ElasticsearchBundle\Service\Manager;

class AttributeRepository
{
    /** @var AttributeIdGenerator */
    private $attributeIdGenerator;
    /** @var Manager */
    private $manager;

    /**
     * @param Manager $manager
     * @param AttributeIdGenerator $attributeIdGenerator
     */
    public function __construct(
        Manager $manager,
        AttributeIdGenerator $attributeIdGenerator
    )
    {
        $this->manager = $manager;
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
        try {
            $id = $this->attributeIdGenerator->getId((new \ReflectionClass($product))->getShortName(), $fieldName);
        } catch (\ReflectionException $e) {
            return null;
        }
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

<?php

namespace CoreShop2VueStorefrontBundle\Repository;

use CoreShop\Component\Product\Model\ProductInterface;
use Exception;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Product\Listing;

class ProductRepository extends DocumentFactory implements RepositoryInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * @required
     * @param AttributeRepository $attributeRepository
     */
    public function setAttributeRepository(AttributeRepository $attributeRepository): void
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getVariants(ProductInterface $product)
    {
        return $product->getChildren([AbstractObject::OBJECT_TYPE_VARIANT], true);
    }

    /**
     * @return Listing
     */
    public function fetchAll()
    {
        $products = new Listing();
        $products->load();

        return $products;
    }

    /**
     * @param string $sku
     * @return ProductInterface
     *
     * @throws Exception
     */
    public function findOneBySku(string $sku)
    {
        $list = new Listing();

        $list->setObjectTypes([
            AbstractObject::OBJECT_TYPE_VARIANT,
            AbstractObject::OBJECT_TYPE_OBJECT
        ]);

        $list->setCondition('sku = :sku', [':sku' => $sku]);
        $list->setLimit(1);
        
        if ($list = $list->getObjects()) {
            return $list[0];
        }
    }

    /**
     * @param string $sku
     * @param array  $attributes
     *
     * @return ProductInterface
     * @throws Exception
     */
    public function findOneBySkuAndAttributes(string $sku, array $attributes)
    {
        $conditions = [];
        $params = [];
        $attributes = $attributes['extension_attributes']['configurable_item_options']
            ?? $attributes['extensions_attributes']['configurable_item_options']
            ?? [];
        foreach ($attributes as $k => $attribute) {
            $attributeObj = $this->attributeRepository->findOneById($attribute['option_id']);
            if (!$attributeObj) {
                throw new \Exception(sprintf('Unknown attribute with id %d', $attribute['option_id']));
            }
            $conditions[] = $attributeObj->getAttributeCode() . ' = :' . $attributeObj->getAttributeCode();
            $params[':' . $attributeObj->getAttributeCode()] = $attribute['option_value'];
        }
        $list = new Listing();

        $list->setObjectTypes([
            AbstractObject::OBJECT_TYPE_VARIANT,
            AbstractObject::OBJECT_TYPE_OBJECT
        ]);

        if (!isset($params[':sku'])) {
            $conditions[]   = 'sku LIKE :sku';
            $params[':sku'] = $sku . '%';
        }
        $list->setCondition(implode(' AND ', $conditions), $params);
        $list->setLimit(1);

        if ($list = $list->getObjects()) {
            return $list[0];
        }
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\PriceHelper;
use CoreShop2VueStorefrontBundle\Document\Attribute;
use CoreShop2VueStorefrontBundle\Document\ConfigurableChildren;
use CoreShop2VueStorefrontBundle\Document\ConfigurableOption;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use CoreShop2VueStorefrontBundle\Document\Product;
use CoreShop2VueStorefrontBundle\Document\ProductCategory;
use CoreShop2VueStorefrontBundle\Repository\AttributeRepository;
use Pimcore\Model\DataObject\AbstractObject;

class DocumentConfigurableProductMapper extends DocumentProductMapper implements DocumentMapperInterface
{
    const CONFIGURABLE_OPTIONS = [
        'size' => 'setSizeOptions',
        'color' => 'setColorOptions',
        'gender' => 'setGenderOptions'
    ];

    /** @var AttributeRepository */
    private $attributeRepository;

    /**
     * @param SlugifyInterface $slugify
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeRepository $attributeRepository
     * @param DocumentFactory $documentFactory
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        SlugifyInterface $slugify,
        ProductRepositoryInterface $productRepository,
        AttributeRepository $attributeRepository,
        DocumentFactory $documentFactory,
        PriceHelper $priceHelper
    ) {
        parent::__construct($slugify, $productRepository, $priceHelper, $documentFactory);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ProductInterface $product
     * @return Product
     */
    public function mapToDocument($product): Product
    {
        $esProduct = parent::mapToDocument($product);
        $esProduct->setTypeId(self::PRODUCT_TYPE_CONFIGURABLE);

        $this->setConfigurable($product, $esProduct);
        $this->setConfigurableChildren($esProduct, $product);

        return $esProduct;
    }

    /**
     * @param Product $esProduct
     * @param ProductInterface $product
     */
    public function setConfigurableChildren(Product $esProduct, ProductInterface $product): void
    {
        if ($product instanceof \CoreShop2VueStorefrontBundle\Bridge\Model\ProductInterface) {
            return;
        }
        $variants = $product->getChildren([AbstractObject::OBJECT_TYPE_VARIANT], true);

        $categoryIds = array_map(function (ProductCategory $category) {
            return $category->getCategoryId();
        }, $esProduct->getCategories()->toArray());

        /** @var ProductInterface $variant */
        foreach ($variants as $variant) {
            $esProduct->addConfigurableChildren(
                $this->createConfigurableChildren($variant, $categoryIds)
            );
        }
    }

    /**
     * @param ProductInterface $variant
     * @param array $catIds
     * @return ConfigurableChildren
     */
    private function createConfigurableChildren(ProductInterface $variant, array $catIds = []): ConfigurableChildren
    {
        $configurableChildren = new ConfigurableChildren();
        $configurableChildren->setId($variant->getId());
        $configurableChildren->setName($variant->getName() ?: $variant->getKey());
        $configurableChildren->setSku($variant->getSku());
        if ($variant instanceof \CoreShop2VueStorefrontBundle\Bridge\Model\ProductInterface) {
            $configurableChildren->setColor($variant->getColor());
            $configurableChildren->setSize($variant->getSize());
            //@todo consider more flexible way to add additional attributes
        }

        $configurableChildren->setCategoryIds($catIds);

        $configurableChildren->setUrlKey(
            $this->slugify->slugify($variant->getName())
                ?: $variant->getKey()
        );

        $standardPrice = $variant->getStorePrice()[1] ?? 0;
        $configurableChildren->setPrice(abs($standardPrice / 100));

        return $configurableChildren;
    }

    /**
     * @param Product $esProduct
     * @param ProductInterface $product
     * @param Attribute $attribute
     * @param array $options
     */
    public function setConfigurableOptions(
        Product $esProduct,
        ProductInterface $product,
        Attribute $attribute,
        array $options = []
    ): void {
        $configurableOption = new ConfigurableOption();
        $configurableOption->setId($attribute->getId());
        $configurableOption->setAttributeId($attribute->getId());
        $configurableOption->setLabel($attribute->getFrontedLabel());
        $configurableOption->setPosition(1);

        $mappedOptions = array_map(function ($val) {
            return ['value_index' => $val];
        }, $options);

        $configurableOption->setValues($mappedOptions);
        $configurableOption->setProductId($product->getId());
        $configurableOption->setAttributeCode($attribute->getAttributeCode());

        $esProduct->addConfigurableOption($configurableOption);
    }

    /**
     * @param $product
     * @param $esProduct
     */
    private function setConfigurable($product, $esProduct): void
    {
        foreach (self::CONFIGURABLE_OPTIONS as $configurableName => $methodName) {
            $getter = 'get' . $configurableName;
            if (method_exists($product, $getter) /*&& null !== $variant->{$getter}()*/) {
                $attribute = $this->attributeRepository->findOneOrNull($product, $configurableName);
                $options = $this->attributeRepository->getOptions($attribute);
                if ($attribute) {
                    $this->setConfigurableOptions($esProduct, $product, $attribute, $options);
                }
                if (method_exists($esProduct, $methodName)) {
                    $esProduct->$methodName($options);
                }
            }
        }
    }
}

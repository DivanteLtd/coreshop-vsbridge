<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\DocumentHelper;
use CoreShop2VueStorefrontBundle\Bridge\Helper\PriceHelper;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use CoreShop2VueStorefrontBundle\Document\MediaGallery;
use CoreShop2VueStorefrontBundle\Document\Product;
use CoreShop2VueStorefrontBundle\Document\ProductCategory;
use CoreShop2VueStorefrontBundle\Document\Stock;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\AbstractObject;
use CoreShop\Component\Store\Model\StoreInterface;

class DocumentProductMapper extends AbstractMapper implements DocumentMapperInterface
{
    /** @var SlugifyInterface */
    protected $slugify;
    /** @var ProductRepositoryInterface */
    protected $productRepository;
    /** @var PriceHelper */
    private $priceHelper;
    /** @var DocumentFactory */
    private $documentFactory;
    /** @var DocumentHelper */
    private $documentHelper;

    /**
     * @param SlugifyInterface           $slugify
     * @param ProductRepositoryInterface $productRepository
     * @param PriceHelper                $priceHelper
     * @param DocumentFactory            $documentFactory
     */
    public function __construct(
        SlugifyInterface $slugify,
        ProductRepositoryInterface $productRepository,
        PriceHelper $priceHelper,
        DocumentFactory $documentFactory,
        DocumentHelper $documentHelper
    ) {
        $this->slugify = $slugify;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
        $this->documentFactory = $documentFactory;
        $this->documentHelper = $documentHelper;
    }

    public function supports($object): bool
    {
        return $object instanceof ProductInterface && [] === $object->getChildren([AbstractObject::OBJECT_TYPE_VARIANT], true);
    }

    /**
     * @param ProductInterface $product
     * @param StoreInterface|null $store
     * @param string|null $language
     *
     * @return Product
     */
    public function mapToDocument($product, ?StoreInterface $store = null, ?string $language = null): Product
    {
        $esProduct = $this->documentFactory->getOrCreate(Product::class, $product->getId());

        $productName = $product->getName($language) ?: $product->getKey();

        $esProduct->setId($product->getId());
        $esProduct->setAttributeSetId(self::PRODUCT_DEFAULT_ATTRIBUTE_SET_ID);
        $esProduct->setPrice($this->priceHelper->getItemPrice($product));
        $esProduct->setFinalPrice($this->priceHelper->getItemPrice($product));
        $esProduct->setStatus(self::PRODUCT_DEFAULT_STATUS);
        $esProduct->setVisibility(self::PRODUCT_DEFAULT_VISIBILITY);
        $esProduct->setTypeId(self::PRODUCT_SIMPLE_TYPE);
        $esProduct->setName($productName);
        $esProduct->setCreatedAt($this->getDateTime($product->getCreationDate()));
        $esProduct->setUpdatedAt($this->getDateTime($product->getModificationDate()));
        $esProduct->setStock($this->createStock($product));
        $esProduct->setEan($product->getEan());
        $esProduct->setAvailability(self::PRODUCT_DEFAULT_AVAILABILITY);
        $esProduct->setOptionTextStatus(self::PRODUCT_DEFAULT_OPTION_STATUS);
        $esProduct->setTaxClassId(self::PRODUCT_DEFAULT_TAX_CLASS_ID);
        $esProduct->setOptionTextTaxClassId(self::PRODUCT_DEFAULT_OPTION_CLASS_ID);
        $esProduct->setDescription($product->getDescription($language));
        $esProduct->setShortDescription($product->getShortDescription($language));
        $esProduct->setWeight($product->getWeight());
        $esProduct->setSku($product->getSku());
        $esProduct->setUrlKey($this->slugify->slugify($productName));
        $esProduct->setImage($product->getImage());

        $this->setMediaGallery($esProduct, $product->getImages());
        $this->setCategories($esProduct, $product, $language);

        return $esProduct;
    }

    /**
     * @param Product $esProduct
     * @param ProductInterface $product
     */
    private function setCategories(Product $esProduct, ProductInterface $product, ?string $language = null): void
    {
        $esProduct->getCategories()->clear();

        $defaultCat = new ProductCategory(self::PRODUCT_DEFAULT_CATEGORY, self::PRODUCT_DEFAULT_CATEGORY_ID);
        $esProduct->addCategory($defaultCat);

        // fetch all categories and their children
        $assignedCategories = [];
        $categories = $product->getCategories();
        foreach ($categories as $category) {
            $assignedCategories[] = $this->documentHelper->buildParents($category);
        }
        /** @var \CoreShop\Component\Core\Model\CategoryInterface $assignedCategories */
        $assignedCategories = array_merge([], ...$assignedCategories);

        // deduplicate and assign
        $categoryIds = [];
        foreach ($assignedCategories as $assignedCategory) {
            $id = $assignedCategory->getId();

            if (!in_array($id, $categoryIds, true)) {
                $categoryIds[] = $id;
                $esProduct->addCategory(new ProductCategory($assignedCategory->getName($language), $id));
            }
        }
        $esProduct->setCategoryIds($categoryIds);
    }

    /**
     * @param Product $product
     * @param array $images
     */
    private function setMediaGallery(Product $product, array $images): void
    {
        $product->getMediaGallery()->clear();
        $position = 1;

        /** @var Image $image */
        foreach ($images as $image) {
            if ($image->getRealFullPath()) {
                $product->addMediaGallery(
                    new MediaGallery($image->getRealFullPath(), $position++)
                );
            }
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @return Stock
     */
    private function createStock(ProductInterface $product): Stock
    {
        $stock = new Stock();
        $stock->productId = $product->getId();
        $stock->itemId = $product->getId();
        $stock->isInStock = $product->getOnHand() > 0 ? true : false;
        $stock->qty = $product->getOnHand() ?: 0;
        $stock->isQtyDecimal = false;
        $stock->stockId = 1;

        if (null !== $minSaleQty = $product->getMinimumQuantityToOrder()) {
            $stock->useConfigMinSaleQty = true;
            $stock->minSaleQty = $minSaleQty;
        }
        if (null !== $maxSaleQty = $product->getMaximumQuantityToOrder()) {
            $stock->useConfigMaxSaleQty = true;
            $stock->maxSaleQty = $maxSaleQty;
        }

        return $stock;
    }
}

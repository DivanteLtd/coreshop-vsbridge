<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Cocur\Slugify\SlugifyInterface;
use CoreShop\Component\Product\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\PriceHelper;
use CoreShop2VueStorefrontBundle\Document\MediaGallery;
use CoreShop2VueStorefrontBundle\Document\Product;
use CoreShop2VueStorefrontBundle\Document\ProductCategory;
use CoreShop2VueStorefrontBundle\Document\Stock;
use CoreShop2VueStorefrontBundle\Repository\ProductRepository;
use Pimcore\Model\Asset\Image;

class DocumentProductMapper extends AbstractMapper implements DocumentMapperInterface
{
    /** @var SlugifyInterface */
    protected $slugify;
    /** @var ProductRepository */
    protected $productRepository;
    /** @var PriceHelper */
    private $priceHelper;

    /**
     * @param SlugifyInterface $slugify
     * @param ProductRepository $productRepository
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        SlugifyInterface $slugify,
        ProductRepository $productRepository,
        PriceHelper $priceHelper
    ) {
        $this->slugify = $slugify;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param ProductInterface $product
     *
     * @return Product
     */
    public function mapToDocument($product): Product
    {
        $esProduct = $this->productRepository->getOrCreate(Product::class, $product->getId());

        $productName = $product->getName() ?: $product->getKey();

        $esProduct->setId($product->getId());
        $esProduct->setAttributeSetId(self::PRODUCT_DEFAULT_ATTRIBUTE_SET_ID);
        $esProduct->setPrice($this->priceHelper->getItemPrice($product));
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
        $esProduct->setDescription($product->getDescription());
        $esProduct->setShortDescription($product->getShortDescription());
        $esProduct->setWeight($product->getWeight());
        $esProduct->setSku($product->getSku());
        $esProduct->setUrlKey($this->slugify->slugify($productName));
        $esProduct->setImage($product->getImage());

        $this->setMediaGallery($esProduct, $product->getImages());
        $this->setCategories($esProduct, $product);

        return $esProduct;
    }

    /**
     * @param Product $esProduct
     * @param ProductInterface $product
     */
    private function setCategories(Product $esProduct, ProductInterface $product): void
    {
        $esProduct->getCategories()->clear();

        $defaultCat = new ProductCategory(self::PRODUCT_DEFAULT_CATEGORY, self::PRODUCT_DEFAULT_CATEGORY_ID);
        $esProduct->addCategory($defaultCat);

        $categories = $product->getCategories() ?: [];
        foreach ($categories as $category) {
            $before = $esProduct->getCategoryIds() ?: [];
            $esProduct->setCategoryIds($before + [$category->getId()]);

            $esProduct->addCategory(new ProductCategory($category->getName(), $category->getId()));
        }
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
     * @param ProductInterface|\CoreShop2VueStorefrontBundle\Model\Product $product
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

        return $stock;
    }
}

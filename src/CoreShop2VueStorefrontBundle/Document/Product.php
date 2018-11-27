<?php

namespace CoreShop2VueStorefrontBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\Document()
 */
class Product
{
    use ConfigurableProduct;

    // @codingStandardsIgnoreStart
    /** @ES\Id() */
    public $_id;
    // @codingStandardsIgnoreEnd

    /** @ES\Property(type="integer") */
    public $id;

    /** @ES\Property(type="integer") */
    public $attributeSetId;

    /** @ES\Property(type="text") */
    public $typeId;

    /** @ES\Property(type="text") */
    public $sku;

    /** @ES\Property(type="text") */
    public $urlKey;

    /** @ES\Property(type="text") */
    public $name;

    /** @ES\Property(type="float") */
    public $price;

    /** @ES\Property(type="integer") */
    public $status;

    /** @ES\Property(type="integer") */
    public $visibility;

    /** @ES\Property(type="date") */
    public $createdAt;

    /** @ES\Property(type="date") */
    public $updatedAt;

    /** @ES\Property(type="integer") */
    public $weight;

    /** @ES\Property(type="text") */
    public $customAttributes;

    /** @ES\Property(type="float") */
    public $finalPrice;

    /** @ES\Property(type="float") */
    public $maxPrice;

    /** @ES\Property(type="float") */
    public $maxRegularPrice;

    /** @ES\Property(type="float") */
    public $minimalPrice;

    /** @ES\Property(type="float") */
    public $minimalRegularPrice;

    /** @ES\Property(type="float") */
    public $regularPrice;

    /** @ES\Property(type="text") */
    public $ean;

    /** @ES\Property(type="text") */
    public $image;

    /** @ES\Property(type="text") */
    public $pkwiu;

    /** @ES\Property(type="text") */
    public $availability;

    /** @ES\Property(type="text") */
    public $isbn;

    /** @ES\Property(type="text") */
    public $authorsEs;

    /** @ES\Property(type="text") */
    public $publishersEs;

    /** @ES\Property(type="text") */
    public $producersEs;

    /** @ES\Property(type="text") */
    public $optionTextStatus;

    /** @ES\Property(type="text") */
    public $taxClassId;

    /** @ES\Property(type="text") */
    public $optionTextTaxClassId;

    /** @ES\Property(type="text") */
    public $description;

    /** @ES\Property(type="text") */
    public $shortDescription;

    /** @ES\Property(type="text") */
    public $specialPrice;

    /** @ES\Embedded(class="CoreShop2VueStorefrontBundle:Stock") */
    public $stock;

    /** @ES\Embedded(class="CoreShop2VueStorefrontBundle:ProductCategory", multiple=true) */
    public $category;

    /** @ES\Embedded(class="CoreShop2VueStorefrontBundle:MediaGallery", multiple=true) */
    public $mediaGallery;

    /** @ES\Property() */
    public $categoryIds = [];

    /** @ES\Property(type="integer") */
    public $hasOptions = 0;

    /** @ES\Property(type="integer") */
    public $requiredOptions = 0;

    /** @ES\Property() */
    public $productLinks = [];

    public function __construct()
    {
        $this->category = new ArrayCollection();
        $this->configurableChildren = new ArrayCollection();
        $this->configurableOptions = new ArrayCollection();
        $this->mediaGallery = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->setEsId($id);
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getEan(): string
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     */
    public function setEan(?string $ean): void
    {
        $this->ean = $ean;
    }

    /**
     * @return string
     */
    public function getAttributeSetId(): string
    {
        return $this->attributeSetId;
    }

    /**
     * @param string $attributeSetId
     */
    public function setAttributeSetId(string $attributeSetId): void
    {
        $this->attributeSetId = $attributeSetId;
    }

    /**
     * @return string
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     */
    public function setTypeId(string $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function addCategory(ProductCategory $category)
    {
        if (false === in_array($category, $this->category->getValues())) {
            $this->category[] = $category;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getPkwiu(): string
    {
        return $this->pkwiu;
    }

    /**
     * @param string $pkwiu
     */
    public function setPkwiu(string $pkwiu): void
    {
        $this->pkwiu = $pkwiu;
    }

    /**
     * @return string
     */
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    /**
     * @param string $isbn
     */
    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }

    /**
     * @return string
     */
    public function getPublishersEs(): string
    {
        return $this->publishersEs;
    }

    /**
     * @param string $publishersEs
     */
    public function setPublishersEs(string $publishersEs): void
    {
        $this->publishersEs = $publishersEs;
    }

    /**
     * @return string
     */
    public function getAuthorsEs(): string
    {
        return $this->authorsEs;
    }

    /**
     * @param string $authorsEs
     */
    public function setAuthorsEs(string $authorsEs): void
    {
        $this->authorsEs = $authorsEs;
    }

    /**
     * @return string
     */
    public function getOptionTextStatus(): string
    {
        return $this->optionTextStatus;
    }

    /**
     * @param string $optionTextStatus
     */
    public function setOptionTextStatus(string $optionTextStatus): void
    {
        $this->optionTextStatus = $optionTextStatus;
    }

    /**
     * @return string
     */
    public function getTaxClassId(): string
    {
        return $this->taxClassId;
    }

    /**
     * @param string $taxClassId
     */
    public function setTaxClassId(string $taxClassId): void
    {
        $this->taxClassId = $taxClassId;
    }

    /**
     * @return string
     */
    public function getOptionTextTaxClassId(): string
    {
        return $this->optionTextTaxClassId;
    }

    /**
     * @param string $optionTextTaxClassId
     */
    public function setOptionTextTaxClassId(string $optionTextTaxClassId): void
    {
        $this->optionTextTaxClassId = $optionTextTaxClassId;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     */
    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(ProductCategory $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getAvailability(): string
    {
        return $this->availability;
    }

    /**
     * @param string $availability
     */
    public function setAvailability(string $availability): void
    {
        $this->availability = $availability;
    }

    /**
     * @return string
     */
    public function getWeight(): string
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight(?string $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getCustomAttributes(): string
    {
        return $this->customAttributes;
    }

    /**
     * @param string $customAttributes
     */
    public function setCustomAttributes(string $customAttributes): void
    {
        $this->customAttributes = $customAttributes;
    }

    /**
     * @return string
     */
    public function getFinalPrice(): string
    {
        return $this->finalPrice;
    }

    /**
     * @param string $finalPrice
     */
    public function setFinalPrice(string $finalPrice): void
    {
        $this->finalPrice = $finalPrice;
    }

    /**
     * @param $categoryId
     * @return Product
     */
    public function addCategoryIds(string $categoryId)
    {
        $this->categoryIds[] = $categoryId;
        return $this;
    }

    /**
     * @return Stock
     */
    public function getStock(): Stock
    {
        return $this->stock;
    }

    /**
     * @param mixed $stock
     */
    public function setStock(Stock $stock): void
    {
        $this->stock = $stock;
    }

    /**
     * @return string
     */
    public function getUrlKey(): string
    {
        return $this->urlKey;
    }

    /**
     * @param string $urlKey
     */
    public function setUrlKey(string $urlKey): void
    {
        $this->urlKey = $urlKey;
    }

    /**
     * @param mixed $hasOptions
     */
    public function setHasOptions($hasOptions = 0): void
    {
        $this->hasOptions = $hasOptions;
    }

    /**
     * @param mixed $requiredOptions
     */
    public function setRequiredOptions($requiredOptions = 0): void
    {
        $this->requiredOptions = $requiredOptions;
    }

    /**
     * @param mixed $productLinks
     */
    public function setProductLinks($productLinks = []): void
    {
        $this->productLinks = $productLinks;
    }

    public function getCategories()
    {
        return $this->category;
    }

    public function setEsId(int $esId)
    {
        $this->_id = $esId;
    }

    public function getEsId(): int
    {
        return $this->_id;
    }

    public function getHasOptions(): int
    {
        return $this->hasOptions;
    }

    public function getRequiredOptions(): int
    {
        return $this->requiredOptions;
    }

    public function getProductLinks(): array
    {
        return $this->productLinks;
    }

    public function setCategoryIds(array $categoryIds = [])
    {
        $this->categoryIds = $categoryIds;
    }

    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }

    public function getMediaGallery()
    {
        return $this->mediaGallery;
    }

    public function addMediaGallery(MediaGallery $mediaGallery)
    {
        if (false === in_array($mediaGallery, $this->mediaGallery->getValues())) {
            $this->mediaGallery[] = $mediaGallery;
        }

        return $this;
    }
}

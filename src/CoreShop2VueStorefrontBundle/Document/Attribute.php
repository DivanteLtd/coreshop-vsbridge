<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\Document()
 */
class Attribute
{
    // @codingStandardsIgnoreStart
    /** @ES\Id() */
    public $_id;
    // @codingStandardsIgnoreEnd

    /** @ES\Property(type="integer") */
    public $id;

    /** @ES\Property(type="string") */
    public $attributeCode;

    /** @ES\Property(type="string") */
    public $attributeModel;

    /** @ES\Property(type="string") */
    public $backendModel;

    /** @ES\Property(type="string") */
    public $backendType;

    /** @ES\Property(type="string") */
    public $backendTable;

    /** @ES\Property(type="string") */
    public $frontendModel;

    /** @ES\Property(type="string") */
    public $frontendInput;

    /** @ES\Property(type="string") */
    public $frontedLabel;

    /** @ES\Property(type="string") */
    public $frontendClass;

    /** @ES\Property(type="string") */
    public $sourceModel;

    /** @ES\Property(type="boolean") */
    public $isRequired;

    /** @ES\Property(type="boolean") */
    public $isUserDefined;

    /** @ES\Property(type="string") */
    public $defaultValue;

    /** @ES\Property(type="string") */
    public $isUnique = "0";

    /** @ES\Property(type="string") */
    public $note;

    /** @ES\Property(type="integer") */
    public $attributeId;

    /** @ES\Property(type="string") */
    public $frontendInputRenderer;

    /** @ES\Property(type="boolean") */
    public $isGlobal;

    /** @ES\Property(type="boolean") */
    public $isVisible = false;

    /** @ES\Property(type="string") */
    public $isSearchable = "0";

    /** @ES\Property(type="boolean") */
    public $isFilterable;

    /** @ES\Property(type="string") */
    public $isComparable = "0";

    /** @ES\Property(type="string") */
    public $isVisibleOnFront = "0";

    /** @ES\Property(type="boolean") */
    public $isHtmlAllowedOnFront;

    /** @ES\Property(type="boolean") */
    public $isUsedForPriceRules;

    /** @ES\Property(type="boolean") */
    public $isFilterableInSearch;

    /** @ES\Property(type="string") */
    public $usedInProductListing = "0";

    /** @ES\Property(type="boolean") */
    public $usedForSortBy;

    /** @ES\Property(type="boolean") */
    public $isConfigurable;

    /** @ES\Property(type="string") */
    public $applyTo;

    /** @ES\Property(type="string") */
    public $isVisibleInAdvanceSearch = "0";

    /** @ES\Property(type="integer") */
    public $position;

    /** @ES\Property(type="boolean") */
    public $isWyswigEnabled;

    /** @ES\Property(type="string") */
    public $isUsedForPromoRules = "0";

    /** @ES\Property(type="boolean") */
    public $isUsedInGrid;

    /** @ES\Property(type="integer") */
    public $searchWeight;

    /** @ES\Property() */
    public $options = [];

    /** @ES\Property(type="boolean") */
    public $isVisibleInGrid;

    /** @ES\Property(type="boolean") */
    public $isFilterableInGrid;

    /** @ES\Property(type="string") */
    public $scope;

    /** @ES\Property(type="string") */
    public $entityTypeId;

    public function setId(string $id)
    {
        $this->id = $id;
        $this->setEsId($id);
    }

    public function setEsId(string $id)
    {
        $this->_id = $id;
    }

    public function setIsWyswigEnabled(bool $isWyswigEnabled)
    {
        $this->isWyswigEnabled = $isWyswigEnabled;
    }

    public function setIsHtmlAllowedOnFront(bool $isHtmlEnabledOnFront)
    {
        $this->isHtmlAllowedOnFront = $isHtmlEnabledOnFront;
    }

    /**
     * @param mixed $attributeCode
     */
    public function setAttributeCode($attributeCode): void
    {
        $this->attributeCode = $attributeCode;
    }

    /**
     * @param mixed $attributeModel
     */
    public function setAttributeModel($attributeModel): void
    {
        $this->attributeModel = $attributeModel;
    }

    /**
     * @param mixed $backendModel
     */
    public function setBackendModel($backendModel): void
    {
        $this->backendModel = $backendModel;
    }

    /**
     * @param mixed $backendType
     */
    public function setBackendType($backendType): void
    {
        $this->backendType = $backendType;
    }

    /**
     * @param mixed $backendTable
     */
    public function setBackendTable($backendTable): void
    {
        $this->backendTable = $backendTable;
    }

    /**
     * @param mixed $frontendModel
     */
    public function setFrontendModel($frontendModel): void
    {
        $this->frontendModel = $frontendModel;
    }

    /**
     * @param mixed $frontendInput
     */
    public function setFrontendInput($frontendInput): void
    {
        $this->frontendInput = $frontendInput;
    }

    /**
     * @param mixed $frontedLabel
     */
    public function setFrontedLabel($frontedLabel): void
    {
        $this->frontedLabel = $frontedLabel;
    }

    /**
     * @param mixed $frontendClass
     */
    public function setFrontendClass($frontendClass): void
    {
        $this->frontendClass = $frontendClass;
    }

    /**
     * @param mixed $sourceModel
     */
    public function setSourceModel($sourceModel): void
    {
        $this->sourceModel = $sourceModel;
    }

    /**
     * @param mixed $isRequired
     */
    public function setIsRequired($isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @param mixed $isUserDefined
     */
    public function setIsUserDefined($isUserDefined): void
    {
        $this->isUserDefined = $isUserDefined;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param mixed $isUnique
     */
    public function setIsUnique($isUnique): void
    {
        $this->isUnique = $isUnique;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note): void
    {
        $this->note = $note;
    }

    /**
     * @param mixed $attributeId
     */
    public function setAttributeId($attributeId): void
    {
        $this->attributeId = $attributeId;
    }

    /**
     * @param mixed $frontendInputRenderer
     */
    public function setFrontendInputRenderer($frontendInputRenderer): void
    {
        $this->frontendInputRenderer = $frontendInputRenderer;
    }

    /**
     * @param mixed $isGlobal
     */
    public function setIsGlobal($isGlobal): void
    {
        $this->isGlobal = $isGlobal;
    }

    /**
     * @param mixed $isVisible
     */
    public function setIsVisible($isVisible): void
    {
        $this->isVisible = $isVisible;
    }

    /**
     * @param mixed $isSearchable
     */
    public function setIsSearchable($isSearchable): void
    {
        $this->isSearchable = $isSearchable;
    }

    /**
     * @param mixed $isFilterable
     */
    public function setIsFilterable($isFilterable): void
    {
        $this->isFilterable = $isFilterable;
    }

    /**
     * @param mixed $isComparable
     */
    public function setIsComparable($isComparable): void
    {
        $this->isComparable = $isComparable;
    }

    /**
     * @param mixed $isVisibleOnFront
     */
    public function setIsVisibleOnFront($isVisibleOnFront): void
    {
        $this->isVisibleOnFront = $isVisibleOnFront;
    }

    /**
     * @param mixed $isUsedForPriceRules
     */
    public function setIsUsedForPriceRules($isUsedForPriceRules): void
    {
        $this->isUsedForPriceRules = $isUsedForPriceRules;
    }

    /**
     * @param mixed $isFilterableInSearch
     */
    public function setIsFilterableInSearch($isFilterableInSearch): void
    {
        $this->isFilterableInSearch = $isFilterableInSearch;
    }

    /**
     * @param mixed $usedInProductListing
     */
    public function setUsedInProductListing($usedInProductListing): void
    {
        $this->usedInProductListing = $usedInProductListing;
    }

    /**
     * @param mixed $usedForSortBy
     */
    public function setUsedForSortBy($usedForSortBy): void
    {
        $this->usedForSortBy = $usedForSortBy;
    }

    /**
     * @param mixed $isConfigurable
     */
    public function setIsConfigurable($isConfigurable): void
    {
        $this->isConfigurable = $isConfigurable;
    }

    /**
     * @param mixed $applyTo
     */
    public function setApplyTo($applyTo): void
    {
        $this->applyTo = $applyTo;
    }

    /**
     * @param mixed $isVisibleInAdvanceSearch
     */
    public function setIsVisibleInAdvanceSearch($isVisibleInAdvanceSearch): void
    {
        $this->isVisibleInAdvanceSearch = $isVisibleInAdvanceSearch;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @param mixed $isUsedForPromoRules
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules): void
    {
        $this->isUsedForPromoRules = $isUsedForPromoRules;
    }

    /**
     * @param mixed $searchWeight
     */
    public function setSearchWeight($searchWeight): void
    {
        $this->searchWeight = $searchWeight;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options): void
    {
        $this->options = $options;
    }

    /**
     * @param mixed $isUsedInGrid
     */
    public function setIsUsedInGrid($isUsedInGrid): void
    {
        $this->isUsedInGrid = $isUsedInGrid;
    }

    public function setIsVisibleInGrid(bool $isVisibleInGrid)
    {
        $this->isVisibleInGrid = $isVisibleInGrid;
    }

    /**
     * @param mixed $isFilterableInGrid
     */
    public function setIsFilterableInGrid($isFilterableInGrid): void
    {
        $this->isFilterableInGrid = $isFilterableInGrid;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function setEntityTypeId(int $entityTypeId)
    {
        $this->entityTypeId = $entityTypeId;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    public function getFrontedLabel():? string
    {
        return $this->frontedLabel;
    }

    public function getId(): int
    {
        return $this->id;
    }
}

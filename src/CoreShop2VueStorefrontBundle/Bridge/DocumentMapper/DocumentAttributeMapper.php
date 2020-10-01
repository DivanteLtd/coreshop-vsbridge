<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperInterface;
use CoreShop2VueStorefrontBundle\Document\Attribute;
use CoreShop2VueStorefrontBundle\Document\DocumentFactory;
use CoreShop2VueStorefrontBundle\Repository\AttributeRepository;
use Pimcore\Model\DataObject\ClassDefinition;
use CoreShop\Component\Store\Model\StoreInterface;

class DocumentAttributeMapper extends AbstractMapper implements DocumentMapperInterface
{
    const SELECT = 'select';

    /** @var AttributeRepository */
    private $attributeRepository;
    /** @var DocumentFactory */
    private $documentFactory;

    public function __construct(
        AttributeRepository $attributeRepository,
        DocumentFactory $documentFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->documentFactory = $documentFactory;
    }

    public function supports($object): bool
    {
        return $object instanceof ClassDefinition\Data;
    }

    /**
     * @param ClassDefinition\Data $fieldDefinition
     * @param StoreInterface|null $store
     * @param string|null $language
     *
     * @return Attribute
     */
    public function mapToDocument($fieldDefinition, ?StoreInterface $store = null, ?string $language = null): Attribute
    {
        $id = $fieldDefinition->id;

        $attribute = $this->documentFactory->getOrCreate(Attribute::class, $id);

        $attribute->setId($id);
        $attribute->setIsWyswigEnabled(self::BOOLEAN_FALSE);
        $attribute->setIsHtmlAllowedOnFront(self::BOOLEAN_FALSE);
        $attribute->setUsedForSortBy(self::BOOLEAN_FALSE);
        $attribute->setIsFilterable(self::BOOLEAN_FALSE);
        $attribute->setIsFilterableInSearch(self::BOOLEAN_FALSE);
        $attribute->setIsUsedInGrid(self::BOOLEAN_FALSE);
        $attribute->setIsVisibleInGrid(self::BOOLEAN_FALSE);
        $attribute->setIsFilterableInGrid(self::BOOLEAN_FALSE);
        $attribute->setPosition(self::ATTR_POSITION);
        $attribute->setScope(self::ATTR_SCOPE);
        $attribute->setAttributeId($id);
        $attribute->setAttributeCode($fieldDefinition->getName());
        $attribute->setFrontendInput(self::ATTR_TYPE);
        $attribute->setEntityTypeId(self::ATTR_ENTITY_TYPE_ID);
        $attribute->setIsRequired(self::BOOLEAN_FALSE);
        $attribute->setFrontedLabel($fieldDefinition->getTitle());

        $this->setOptions($attribute, $fieldDefinition);

        $attribute->setIsUserDefined(self::BOOLEAN_FALSE);
        $attribute->setBackendType(self::ATTR_BACKEND_TYPE_VARCHAR);

        return $attribute;
    }

    /**
     * @param Attribute $attribute
     * @param $fieldDefinition
     */
    private function setOptions(Attribute $attribute, $fieldDefinition): void
    {
        if ($fieldDefinition->fieldtype == self::SELECT) {
            $options = [];
            foreach ($fieldDefinition->options as $option) {
                $options[] = ['label' => $option['key'], 'value' => $option['value']];
            }
            $attribute->setOptions($options);
        }
    }
}

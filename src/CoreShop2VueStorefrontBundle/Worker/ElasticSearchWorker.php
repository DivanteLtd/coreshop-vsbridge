<?php

namespace CoreShop2VueStorefrontBundle\Worker;

use CoreShop\Bundle\IndexBundle\Worker\AbstractWorker;
use CoreShop\Component\Index\Condition\ConditionInterface;
use CoreShop\Component\Index\Condition\ConditionRendererInterface;
use CoreShop\Component\Index\Model\IndexableInterface;
use CoreShop\Component\Index\Model\IndexColumnInterface;
use CoreShop\Component\Index\Model\IndexInterface;
use CoreShop\Component\Index\Worker\FilterGroupHelperInterface;
use CoreShop\Component\Registry\ServiceRegistryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Attribute\AttributeIdGenerator;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapper\DocumentAttributeMapper;
use ONGR\ElasticsearchBundle\Service\Manager;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\CoreShopProduct;

class ElasticSearchWorker extends AbstractWorker
{
    /** @var DocumentAttributeMapper */
    private $documentAttributeBuilder;
    /** @var Manager */
    private $manager;
    /** @var AttributeIdGenerator */
    private $attributeIdGenerator;

    /**
     * @param ServiceRegistryInterface   $extensionsRegistry
     * @param ServiceRegistryInterface   $getterServiceRegistry
     * @param ServiceRegistryInterface   $interpreterServiceRegistry
     * @param FilterGroupHelperInterface $filterGroupHelper
     * @param ConditionRendererInterface $conditionRenderer
     * @param DocumentAttributeMapper    $documentAttributeBuilder
     * @param Manager                    $manager
     * @param AttributeIdGenerator       $attributeIdGenerator
     */
    public function __construct(
        ServiceRegistryInterface $extensionsRegistry,
        ServiceRegistryInterface $getterServiceRegistry,
        ServiceRegistryInterface $interpreterServiceRegistry,
        FilterGroupHelperInterface $filterGroupHelper,
        ConditionRendererInterface $conditionRenderer,
        DocumentAttributeMapper $documentAttributeBuilder,
        Manager $manager,
        AttributeIdGenerator $attributeIdGenerator
    ) {
        parent::__construct(
            $extensionsRegistry,
            $getterServiceRegistry,
            $interpreterServiceRegistry,
            $filterGroupHelper,
            $conditionRenderer
        );

        $this->documentAttributeBuilder = $documentAttributeBuilder;
        $this->manager = $manager;
        $this->attributeIdGenerator = $attributeIdGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateIndexStructures(IndexInterface $index)
    {
        try {
            $this->updateAttributes($index);
        } catch (\Exception $exception) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndexStructures(IndexInterface $index)
    {
    }

    /**
     * @param CoreShopProduct $index
     * @param IndexableInterface $object
     */
    public function deleteFromIndex(IndexInterface $index, IndexableInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     * @throws \ONGR\ElasticsearchBundle\Exception\BulkWithErrorsException
     */
    public function updateIndex(IndexInterface $index, IndexableInterface $object)
    {
        $this->updateAttributes($index);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(IndexInterface $index)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function renderCondition(ConditionInterface $condition, $prefix = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function renderFieldType($type)
    {
    }

    /**
     * @param IndexInterface $index
     * @throws \ONGR\ElasticsearchBundle\Exception\BulkWithErrorsException
     */
    private function updateAttributes(IndexInterface $index): void
    {
        $classDefinition = ClassDefinition::getByName($index->getClass());
        if (!($classDefinition || $index->getColumns())) {
            return;
        }

        /** @var IndexColumnInterface $field */
        foreach ($index->getColumns() as $field) {
            $fieldDefinition = $classDefinition->getFieldDefinition($field->getName());
            if ($fieldDefinition) {
                $fieldDefinition->id = $this->attributeIdGenerator->getId($index->getClass(), $field->getName());
                $attribute = $this->documentAttributeBuilder->mapToDocument($fieldDefinition);
                $this->manager->persist($attribute);
                $this->manager->commit();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function typeCastValues(IndexColumnInterface $column, $value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function handleArrayValues(IndexInterface $index, array $value)
    {
        return ',' . implode($value, ',') . ',';
    }
}

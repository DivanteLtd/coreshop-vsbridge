<?php

namespace CoreShop2VueStorefrontBundle\Bridge\DocumentMapper;

use Carbon\Carbon;
use ONGR\ElasticsearchBundle\Service\IndexService;

abstract class AbstractMapper
{
    const PRODUCT_DEFAULT_ATTRIBUTE_SET_ID = 11;
    const PRODUCT_DEFAULT_STATUS = 1;
    const PRODUCT_DEFAULT_VISIBILITY = 4;
    const PRODUCT_SIMPLE_TYPE = "simple";
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';
    const PRODUCT_DEFAULT_CATEGORY_ID = 2;
    const PRODUCT_DEFAULT_AVAILABILITY = 1;
    const PRODUCT_DEFAULT_OPTION_STATUS = "Enabled";
    const PRODUCT_DEFAULT_TAX_CLASS_ID = 2;
    const PRODUCT_DEFAULT_OPTION_CLASS_ID = "Taxable Goods";
    const PRODUCT_DEFAULT_CATEGORY = "Default category";

    const CATEGORY_DEFAULT_PATH = "1/2";
    const CATEGORY_DEFAULT_DISPLAY_MODE = "PAGE";
    const CATEGORY_DEFAULT_PAGE_LAYOUT = "1column";

    const BOOLEAN_FALSE = false;
    const BOOLEAN_TRUE = true;
    const ATTR_POSITION = 0;
    const ATTR_SCOPE = "global";
    const ATTR_TYPE = "text";
    const ATTR_ENTITY_TYPE_ID = 4;
    const ATTR_BACKEND_TYPE_VARCHAR = "varchar";

    protected function getDateTime(int $date): string
    {
        return Carbon::createFromTimestamp($date)->format(\DateTimeInterface::RFC3339);
    }

    protected function find(IndexService $service, object $object): object
    {
        $document = $service->findOneBy(['id' => $object->getId()]);
        if ($document === null) {
            $documentClass = $this->getDocumentClass();

            return new $documentClass;
        }

        return $service->find($object->getId());
    }
}

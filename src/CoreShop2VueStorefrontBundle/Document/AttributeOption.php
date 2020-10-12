<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType()
 */
class AttributeOption
{
    /** @ES\Property(type="keyword") */
    public $value;

    /** @ES\Property(type="keyword") */
    public $label;
}

<?php

namespace CoreShop2VueStorefrontBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Annotation as ES;

trait ConfigurableProduct
{
    public $colorOptions = [];

    public $sizeOptions = [];

    /** @ES\Property(type="text") */
    public $performanceFabric;

    /** @ES\Property(type="text") */
    public $erinRecommends;

    /** @ES\Property(type="text") */
    public $new;

    /** @ES\Property(type="text") */
    public $sale;

    /** @ES\Property(type="text") */
    public $pattern;

    /** @ES\Property(type="text") */
    public $climate;

    /**
     * @ES\Embedded(class=\CoreShop2VueStorefrontBundle\Document\ConfigurableOption::class)
     * @var ArrayCollection $configurableChildren
     */
    public $configurableOptions;

    /**
     * @ES\Embedded(class=\CoreShop2VueStorefrontBundle\Document\ConfigurableChildren::class)
     * @var ArrayCollection $configurableChildren
     */
    public $configurableChildren;

    public function addConfigurableOption(ConfigurableOption $configurableOption)
    {
        if (false === in_array($configurableOption, $this->configurableOptions->getValues())) {
            $this->configurableOptions[] = $configurableOption;
        }

        return $this;
    }

    public function addConfigurableChildren(ConfigurableChildren $configurableChildren)
    {
        if (false === in_array($configurableChildren, $this->configurableChildren->getValues())) {
            $this->configurableChildren[] = $configurableChildren;
        }

        return $this;
    }

    public function setColorOptions(array $options)
    {
        $this->colorOptions = $options;
    }

    public function setSizeOptions(array $sizeOptions)
    {
        $this->sizeOptions = $sizeOptions;
    }
}

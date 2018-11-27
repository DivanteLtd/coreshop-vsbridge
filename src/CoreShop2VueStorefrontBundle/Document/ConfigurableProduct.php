<?php

namespace CoreShop2VueStorefrontBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\ElasticsearchBundle\Annotation as ES;

trait ConfigurableProduct
{
    /** @ES\Property() */
    public $colorOptions = [];

    /** @ES\Property() */
    public $sizeOptions = [];

    /** @ES\Property(type="string") */
    public $performanceFabric;

    /** @ES\Property(type="string") */
    public $erinRecommends;

    /** @ES\Property(type="string") */
    public $new;

    /** @ES\Property(type="string") */
    public $sale;

    /** @ES\Property(type="string") */
    public $pattern;

    /** @ES\Property(type="string") */
    public $climate;

    /**
     * @ES\Embedded(class="CoreShop2VueStorefrontBundle:ConfigurableOption", multiple=true)
     * @var ArrayCollection $configurableChildren
     */
    public $configurableOptions;

    /**
     * @ES\Embedded(class="CoreShop2VueStorefrontBundle:ConfigurableChildren", multiple=true)
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

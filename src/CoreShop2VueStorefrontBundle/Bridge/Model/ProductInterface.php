<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Model;

interface ProductInterface
{
    /**
     * @return \CoreShop\Component\Core\Model\ProductInterface
     */
    public function getChildren();

    /**
     * @return mixed
     */
    public function getSize();

    /**
     * @return mixed
     */
    public function getColor();
}

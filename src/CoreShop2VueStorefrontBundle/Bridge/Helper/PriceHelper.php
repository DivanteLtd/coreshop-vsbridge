<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Helper;

use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Core\Model\StoreInterface;

class PriceHelper
{
    public function getItemPrice(ProductInterface $product, ?StoreInterface $store): int
    {
        $standardPrice = ($store !== null ? $product->getStorePrice($store) : $product->getStorePrice($store)[1]) ?? 0;
        return $standardPrice ? abs($standardPrice / 100) : 0;
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Helper;

use CoreShop\Component\Core\Model\ProductInterface;

class PriceHelper
{
    public function getItemPrice(ProductInterface $product): int
    {
        $standardPrice = $product->getStorePrice()[1] ?? 0;
        return $standardPrice ? abs($standardPrice / 100) : 0;
    }
}

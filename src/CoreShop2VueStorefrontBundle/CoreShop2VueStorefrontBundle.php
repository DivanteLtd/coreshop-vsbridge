<?php

namespace CoreShop2VueStorefrontBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class CoreShop2VueStorefrontBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/coreshop2vuestorefront/js/pimcore/startup.js'
        ];
    }
}

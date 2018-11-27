<?php

namespace CoreShop2VueStorefrontBundle\Tests\helpers;

if (!function_exists('imageFactoryMock')) {
    function imageFactoryMock($path)
    {
        return new class($path)
        {
            protected $path;

            public function __construct($path)
            {
                $this->path = $path;
            }

            public function getRealFullPath()
            {
                return $this->path;
            }
        };
    }
}

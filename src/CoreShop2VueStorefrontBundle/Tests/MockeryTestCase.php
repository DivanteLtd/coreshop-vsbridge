<?php

namespace CoreShop2VueStorefrontBundle\Tests;

use Mockery as m;
use Pimcore\Test\KernelTestCase;

class MockeryTestCase extends KernelTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }
}

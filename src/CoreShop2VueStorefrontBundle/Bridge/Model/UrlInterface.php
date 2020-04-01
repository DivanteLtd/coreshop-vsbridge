<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge\Model;

interface UrlInterface
{
    public function getUrlKey(): string;
    public function getUrlPath(): string;
}

<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Model;

interface CategoryInterface
{
    public function getIsActive(): bool;
    public function getIncludeInMenu(): bool;
}

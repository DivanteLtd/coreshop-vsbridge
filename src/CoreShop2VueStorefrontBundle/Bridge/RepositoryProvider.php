<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Resource\Repository\PimcoreRepositoryInterface;

class RepositoryProvider
{
    private $repositories;

    public function __construct(iterable $repositories)
    {
        $this->repositories = $repositories;
    }

    public function getForAlias(string $alias): PimcoreRepositoryInterface
    {
        return $this->repositories[$alias];
    }

    public function getAliases(): array
    {
        return array_keys($this->repositories);
    }
}

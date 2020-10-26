<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Resource\Repository\RepositoryInterface;

class RepositoryProvider
{
    private $repositories;

    public function __construct(iterable $repositories)
    {
        $this->repositories = $repositories;
    }

    public function getForAlias(string $alias): RepositoryInterface
    {
        return $this->repositories[$alias];
    }

    public function getAliases(): array
    {
        return array_keys($this->repositories);
    }
}

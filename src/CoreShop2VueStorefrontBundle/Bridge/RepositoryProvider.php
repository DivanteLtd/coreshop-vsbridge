<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Resource\Repository\RepositoryInterface;

class RepositoryProvider
{
    /**
     * @var array<RepositoryInterface>
     */
    private $repositories;

    public function __construct(iterable $repositories)
    {
        $this->repositories = $repositories;
    }

    public function hasRepositoryFor(object $object): bool
    {
        $className = get_class($object);

        if (isset($cache[$className])) {
            return $cache[$className];
        }

        /** @var RepositoryInterface $repository */
        foreach ($this->repositories as $repository) {
            if ($repository->getClassName() === $className) {
                $cache[$className] = true;

                return true;
            }
        }

        return false;
    }

    public function getAliasFor(object $object): string
    {
        $className = get_class($object);

        static $cache = [];

        if (isset($cache[$className])) {
            return $cache[$className];
        }

        /** @var RepositoryInterface $repository */
        foreach ($this->repositories as $alias => $repository) {
            if ($repository->getClassName() === $className) {
                $cache[$className] = $alias;

                return $alias;
            }
        }

        throw new \InvalidArgumentException('Object not managed by repository provider');
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

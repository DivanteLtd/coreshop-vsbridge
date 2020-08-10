<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

interface ImporterInterface
{
    public function describe(): string;

    public function count(): int;

    public function import(callable $callback): void;
}

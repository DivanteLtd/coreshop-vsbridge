<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\DependencyInjection\CompilerPass;

use CoreShop2VueStorefrontBundle\Bridge\RepositoryProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class RepositoryProviderCompilerPass implements CompilerPassInterface
{
    private const REPOSITORY = 'coreshop2vuestorefront.repository';

    private static $defaults = [
        'category' => 'coreshop.repository.category',
        'product' => 'coreshop.repository.product'
    ];

    public function process(ContainerBuilder $container): void
    {
        $repositories = array_replace(self::$defaults, $container->getParameter('core_shop2_vue_storefront.repositories'));
        foreach ($repositories as $alias => $id) {
            $definition = $container->getDefinition($id);
            $definition->addTag(self::REPOSITORY, ['alias' => $alias]);
        }

        $repositories = [];
        $taggedServices = $container->findTaggedServiceIds(self::REPOSITORY);
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $idx => $tag) {
                if (!array_key_exists('alias', $tag)) {
                    throw new LogicException(
                        sprintf('Missing "alias" for "%s" tag %d for service "%s"', self::REPOSITORY, $idx, $id)
                    );
                }
                $alias = $tag['alias'];

                $repositories[$alias] = new Reference($id);
            }
        }

        $definition = $container->findDefinition(RepositoryProvider::class);
        $definition->setArgument(0, $repositories);
    }
}

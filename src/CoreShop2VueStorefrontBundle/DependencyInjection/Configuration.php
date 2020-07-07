<?php

namespace CoreShop2VueStorefrontBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    private $languages;

    public function __construct(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('core_shop2_vue_storefront');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->arrayNode('hosts')
                            ->beforeNormalization()->castToArray()->end()
                            ->scalarPrototype()->end()
                        ->end()
                        ->scalarNode('index')->end()
                    ->end()
                ->end()
                ->arrayNode('stores')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('languages')
                                ->beforeNormalization()->castToArray()->end()
                                ->enumPrototype()
                                    ->values($this->languages)
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

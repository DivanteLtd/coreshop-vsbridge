<?php

namespace CoreShop2VueStorefrontBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CoreShop2VueStorefrontExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $languages = explode(',', $container->getParameter('pimcore.config')['general']['valid_languages']);

        $configuration = new Configuration($languages);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('core_shop2_vue_storefront.elasticsearch_config', $config['elasticsearch']);
        $container->setParameter('core_shop2_vue_storefront.sites', $config['sites']);
        $container->setParameter('core_shop2_vue_storefront.repositories', array_combine(array_keys($config['repositories']), array_column($config['repositories'], 'id')));
        $container->setParameter('core_shop2_vue_storefront.store_aware', $config['store_aware']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}

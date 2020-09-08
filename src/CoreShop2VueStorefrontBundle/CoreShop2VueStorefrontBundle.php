<?php

namespace CoreShop2VueStorefrontBundle;

use CoreShop2VueStorefrontBundle\DependencyInjection\CompilerPass\RepositoryProviderCompilerPass;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreShop2VueStorefrontBundle extends AbstractPimcoreBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RepositoryProviderCompilerPass());
    }

    public function getJsPaths()
    {
        return [
            '/bundles/coreshop2vuestorefront/js/pimcore/startup.js'
        ];
    }
}

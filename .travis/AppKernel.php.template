<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Kernel;

class AppKernel extends Kernel
{
    /**
     * Adds bundles to register to the bundle collection. The collection is able
     * to handle priorities and environment specific bundles.
     *
     * @param BundleCollection $collection
     */
    public function registerBundlesToCollection(BundleCollection $collection)
    {
        if (class_exists('\\AppBundle\\AppBundle')) {
            $collection->addBundle(new \AppBundle\AppBundle);
        }

        if (class_exists('\Pimcore\Bundle\LegacyBundle\PimcoreLegacyBundle')) {
            $collection->addBundle(new \Pimcore\Bundle\LegacyBundle\PimcoreLegacyBundle);
        }

        if (class_exists('\ONGR\ElasticsearchBundle\ONGRElasticsearchBundle')) {
            $collection->addBundle(new ONGR\ElasticsearchBundle\ONGRElasticsearchBundle);
        }

        if (class_exists('\Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle')) {
            $collection->addBundle(new \Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle());
        }

        if (class_exists('\SymfonyBundles\JsonRequestBundle\SymfonyBundlesJsonRequestBundle')) {
            $collection->addBundle(new \SymfonyBundles\JsonRequestBundle\SymfonyBundlesJsonRequestBundle());
        }

        if (class_exists('\Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle')) {
            $collection->addBundle(new \Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle());
        }

        if (class_exists('\Gfreeau\Bundle\GetJWTBundle\GfreeauGetJWTBundle')) {
            $collection->addBundle(new \Gfreeau\Bundle\GetJWTBundle\GfreeauGetJWTBundle());
        }

        if (class_exists('\Nelmio\CorsBundle\NelmioCorsBundle')) {
            $collection->addBundle(new Nelmio\CorsBundle\NelmioCorsBundle());
        }

        if (class_exists('Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle')) {
            $collection->addBundle(new \Gesdinet\JWTRefreshTokenBundle\GesdinetJWTRefreshTokenBundle());
        }
    }
}

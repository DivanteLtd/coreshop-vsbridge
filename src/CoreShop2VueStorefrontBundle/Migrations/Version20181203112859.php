<?php

namespace CoreShop2VueStorefrontBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use PimcoreDevkitBundle\Service\InstallerService;
use PimcoreDevkitBundle\Service\Migration\AbstractPimcoreMigration;

class Version20181203112859 extends AbstractPimcoreMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $service = $this->container->get(InstallerService::class);
        $data    = [
            'CoreShopCartItem'  => '@CoreShop2VueStorefrontBundle/Resources/install/pimcore/classes/cart_item/CoreShopCartItem.json',
            'CoreShopOrderItem' => '@CoreShop2VueStorefrontBundle/Resources/install/pimcore/classes/order_item/CoreShopOrderItem.json',
        ];
        foreach ($data as $class => $file) {
            $service->createClassDefinition($class, $this->getFileLocator()->locate($file));
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

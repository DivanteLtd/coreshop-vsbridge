<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Order;

use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Order\Model\OrderItemInterface;
use CoreShop\Component\Order\Model\ProposalItemInterface;
use CoreShop\Component\Pimcore\DataObject\ObjectServiceInterface;
use CoreShop2VueStorefrontBundle\Bridge\Helper\PriceHelper;
use CoreShop2VueStorefrontBundle\Repository\ProductRepository;

class ProductItemToOrderItemTransformer
{
    /** @var ObjectServiceInterface */
    private $objectService;
    /** @var string */
    private $pathForItems;
    /** @var ProductRepository */
    private $productRepository;
    /** @var PriceHelper */
    private $priceHelper;

    public function __construct(
        ObjectServiceInterface $objectService,
        string $pathForItems,
        ProductRepository $productRepository,
        PriceHelper $priceHelper
    ) {
        $this->objectService = $objectService;
        $this->pathForItems = $pathForItems;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param ProductInterface $product
     * @param OrderInterface $order
     * @param OrderItemInterface $orderItem
     *
     * @return ProposalItemInterface|false
     *
     * @throws \Exception
     */
    public function transform(array $product, OrderInterface $order, ProposalItemInterface $orderItem)
    {
        $productOrder = $this->productRepository->findOneBySku($product['sku']);
        if (!$productOrder) {
            return false;
        }

        $itemFolder = $this->objectService->createFolderByPath($order->getFullPath() . '/' . $this->pathForItems);

        $orderItem->setKey(uniqid());
        $orderItem->setParent($itemFolder);
        $orderItem->setPublished(true);
        $orderItem->setItemWholesalePrice($productOrder->getWholesalePrice());
        $orderItem->setBaseItemPrice($this->priceHelper->getItemPrice($productOrder));
        $orderItem->setProduct($productOrder);

        $orderItem->setName($productOrder->getName());
        $orderItem->setQuantity($product['qty']);

        $orderItem->save();

        return $orderItem;
    }
}

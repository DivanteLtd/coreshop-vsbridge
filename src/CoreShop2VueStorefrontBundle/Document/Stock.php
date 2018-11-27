<?php

namespace CoreShop2VueStorefrontBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;

/**
 * @ES\ObjectType()
 */
class Stock
{
    /** @ES\Property(type="integer") */
    public $itemId;

    /** @ES\Property(type="integer") */
    public $productId;

    /** @ES\Property(type="integer") */
    public $stockId;

    /** @ES\Property(type="integer") */
    public $qty;

    /** @ES\Property(type="boolean") */
    public $isInStock;

    /** @ES\Property(type="boolean") */
    public $isQtyDecimal;

    /** @ES\Property(type="boolean") */
    public $showDefaultNotificationMessage;

    /** @ES\Property(type="boolean") */
    public $useConfigMinQty;

    /** @ES\Property(type="integer") */
    public $minQty;

    /** @ES\Property(type="integer") */
    public $useConfigMinSaleQty;

    /** @ES\Property(type="integer") */
    public $maxSaleQty;

    /** @ES\Property(type="boolean") */
    public $useConfigBackorders;

    /** @ES\Property(type="integer") */
    public $backorders;

    /** @ES\Property(type="boolean") */
    public $useConfigNotifyStockQty;

    /** @ES\Property(type="integer") */
    public $notifyStockQty;

    /** @ES\Property(type="boolean") */
    public $useConfigQtyIncrements;

    /** @ES\Property(type="integer") */
    public $qtyIncrements;

    /** @ES\Property(type="boolean") */
    public $useConfigEnableQtyInc;

    /** @ES\Property(type="integer") */
    public $enableQtyInc;

    /** @ES\Property(type="boolean") */
    public $useConfigManageStock;

    /** @ES\Property(type="boolean") */
    public $manageStock;

    /** @ES\Property(type="date") */
    public $lowStockDate;

    /** @ES\Property(type="boolean") */
    public $isDecimalDivided;

    /** @ES\Property(type="integer") */
    public $stockStatusChangedAuto;
}

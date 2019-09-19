<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Response\Cart;

use CoreShop\Bundle\MoneyBundle\Formatter\MoneyFormatter;
use CoreShop\Component\Core\Model\CartInterface;
use CoreShop2VueStorefrontBundle\Bridge\Response\ResponseBodyCreator;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Index\Model\IndexColumnInterface;
use CoreShop\Component\Order\Model\ProposalItemInterface;
use CoreShop\Component\Payment\Model\PaymentProviderInterface;
use CoreShop\Component\Shipping\Model\CarrierInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;

/**
 * Class CartResponse
 *
 * @package CoreShop2VueStorefrontBundle\Bridge\Response\Cart
 */
class CartResponse extends ResponseBodyCreator
{
    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getCartItemConfigurableOptions(ProductInterface $product): array
    {
        $ret   = [];
        $index = $this->getCurrentIndex();
        if (!$index) {
            return $ret;
        }

        $classDefinition = ClassDefinition::getByName($index->getClass());
        if (!($classDefinition || $index->getColumns())) {
            return $ret;
        }

        /** @var IndexColumnInterface $field */
        foreach ($index->getColumns() as $field) {
            $fieldDefinition = $classDefinition->getFieldDefinition($field->getName());
            if (!$fieldDefinition) {
                continue;
            }

            $getter = 'get' . $field->getName();
            if (!method_exists($product, $getter)) {
                continue;
            }

            $attribute = $this->attributeRepository->findOneOrNull($product, $field->getName());
            if (!$attribute) {
                continue;
            }

            $method = 'get' . $attribute->getAttributeCode();
            $ret[]  = [
                'option_id'    => $attribute->getId(),
                'option_value' => $product->$method(),
            ];
        }
        return $ret;
    }

    /**
     * @param ProposalItemInterface[] $items
     *
     * @return array
     */
    public function cartItemsResponse($items): array
    {
        $ret = [];
        foreach ($items as $item) {
            $ret[] = $this->singleCartItemResponse($item);
        }
        return $ret;
    }

    public function shippingMethodsResponse($shippingMethods): array
    {
        $ret = [];
        /** @var CarrierInterface $shippingMethod */
        foreach ($shippingMethods as $shippingMethod) {
            $tmp                   = [];
            $tmp['carrier_code']   = $shippingMethod->getIdentifier();
            $tmp['method_code']    = $shippingMethod->getIdentifier();
            $tmp['carrier_title']  = $shippingMethod->getTitle();
            $tmp['method_title']   = $shippingMethod->getTitle();
            $tmp['amount']         = 5; //@todo
            $tmp['base_amount']    = 5;
            $tmp['available']      = true;
            $tmp['error_message']  = "";
            $tmp['price_excl_tax'] = 5;
            $tmp['price_incl_tax'] = 5;
            $ret[] = $tmp;
        }

        return $ret;
    }

    /**
     * @param ProposalItemInterface $item
     *
     * @return array
     */
    public function singleCartItemResponse(ProposalItemInterface $item): array
    {
        /** @var MoneyFormatter $moneyFormatter */
        $moneyFormatter = \Pimcore::getContainer()->get('coreshop.money_formatter');
        $type    = 'simple';
        $product = $item->getProduct();
        if ($product instanceof AbstractObject) {
            $variants = $product->getChildren([AbstractObject::OBJECT_TYPE_VARIANT], true);
            if (false === empty($variants)) {
                $type = 'configurable';
            }
        }
        $ret = [
            'item_id'        => $item->getId(),
            'name '          => $product->getName(),
            'price'          => $moneyFormatter->format($item->getItemPrice(), 'USD'),
            'product_type'   => $type,
            'qty'            => $item->getQuantity(),
            'product_option' => [
                'extensions_attributes' => [
                    'custom_options'            => [],
                    'configurable_item_options' => $this->getCartItemConfigurableOptions($product),
                    'bundle_options'            => [],
                ],
            ],
            'quote_id'       => md5(time()), //TODO
            'sku'            => method_exists($product, 'getSku') ? $product->getSku() : $product->getId(),
        ];
        return $ret;
    }

    /**
     * @param CartInterface              $cart
     * @param PaymentProviderInterface[] $providers
     * @param array                      $payload
     *
     * @return array
     *
     * @todo handle discounts, translations, currency, payload, different rates VAT
     */
    public function shippingInformationResponse($cart, array $providers, array $payload): array
    {
        /** @var MoneyFormatter $moneyFormatter */
        $moneyFormatter = \Pimcore::getContainer()->get('coreshop.money_formatter');
        $items = [];
        $totalQty = 0;
        foreach ($cart->getItems() as $cartItem) {
            $purchasable = $cartItem->getProduct();
            $totalQty += $cartItem->getQuantity();
            $items[]      = [
                'item_id'                 => $purchasable->getId(),
                'price'                   => $moneyFormatter->format($cartItem->getItemPrice(false), 'USD'),
                'base_price'              => $moneyFormatter->format($cartItem->getItemPrice(false), 'USD'),
                'qty'                     => $cartItem->getQuantity(),
                'row_total'               => $moneyFormatter->format($cartItem->getTotal(false), 'USD'),
                'base_row_total'          => $moneyFormatter->format($cartItem->getTotal(false), 'USD'),
                'row_total_with_discount' => 0,
                'tax_amount'              => $moneyFormatter->format($cartItem->getTotalTax(), 'USD'),
                'base_tax_amount'         => $moneyFormatter->format($cartItem->getTotalTax(), 'USD'),
                'tax_percent'             => 23,
                'discount_amount'         => 0,
                'base_discount_amount'    => 0,
                'discount_percent'        => 0,
                'price_incl_tax'          => $moneyFormatter->format($cartItem->getItemPrice(), 'USD'),
                'base_price_incl_tax'     => $moneyFormatter->format($cartItem->getItemPrice(), 'USD'),
                'row_total_incl_tax'      => $moneyFormatter->format($cartItem->getTotal(), 'USD'),
                'base_row_total_incl_tax' => $moneyFormatter->format($cartItem->getTotal(), 'USD'),
                'options'                 => '[]',
                'weee_tax_applied_amount' => null,
                'weee_tax_applied'        => null,
                'name'                    => $purchasable->getName(),
            ];
        }
        $ret   = [
            'payment_methods' => $this->paymentMethodsResponse($providers),
            'totals'          => [
                'grand_total'                   => $moneyFormatter->format($cart->getTotal(), 'USD'),
                'base_grand_total'              => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                'subtotal'                      => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                'base_subtotal'                 => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                'discount_amount'               => 0,
                'base_discount_amount'          => 0,
                'subtotal_with_discount'        => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                'base_subtotal_with_discount'   => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                'shipping_amount'               => $moneyFormatter->format($cart->getShipping(false), 'USD'),
                'base_shipping_amount'          => $moneyFormatter->format($cart->getShipping(false), 'USD'),
                'shipping_discount_amount'      => 0,
                'base_shipping_discount_amount' => 0,
                'tax_amount'                    => $moneyFormatter->format($cart->getTotalTax(), 'USD'),
                'base_tax_amount'               => $moneyFormatter->format($cart->getTotalTax(), 'USD'),
                'weee_tax_applied_amount'       => null,
                'shipping_tax_amount'           => $moneyFormatter->format($cart->getShippingTax(), 'USD'),
                'base_shipping_tax_amount'      => $moneyFormatter->format($cart->getShippingTax(), 'USD'),
                'subtotal_incl_tax'             => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                'shipping_incl_tax'             => $moneyFormatter->format($cart->getShipping(), 'USD'),
                'base_shipping_incl_tax'        => $moneyFormatter->format($cart->getShipping(), 'USD'),
                'base_currency_code'            => 'USD',
                'quote_currency_code'           => 'USD',
                'items_qty'                     => $totalQty,
                'items'                         => $items,
                'total_segments'                => [
                    0 => [
                        'code'  => 'subtotal',
                        'title' => 'Subtotal',
                        'value' => $moneyFormatter->format($cart->getSubtotal(), 'USD'),
                    ],
                    1 => [
                        'code'  => 'shipping',
                        'title' => 'Shipping & Handling (Flat Rate - Fixed)',
                        'value' => $moneyFormatter->format($cart->getShipping(), 'USD'),
                    ],
                    2 => [
                        'code'  => 'discount',
                        'title' => 'Discount',
                        'value' => 0,
                    ],
                    3 => [
                        'code'                 => 'tax',
                        'title'                => 'Tax',
                        'value'                => $moneyFormatter->format($cart->getTotalTax(), 'USD'),
                        'area'                 => 'taxes',
                        'extension_attributes' => [
                            'tax_grandtotal_details' => [
                                0 => [
                                    'amount'   => $moneyFormatter->format($cart->getTotalTax(), 'USD'),
                                    'rates'    => [
                                        0 => [
                                            'percent' => '23',
                                            'title'   => 'VAT23',
                                        ],
                                    ],
                                    'group_id' => 1,
                                ],
                            ],
                        ],
                    ],
                    4 => [
                        'code'  => 'grand_total',
                        'title' => 'Grand Total',
                        'value' => $moneyFormatter->format($cart->getTotal(), 'USD'),
                        'area'  => 'footer',
                    ],
                ],
            ],
        ];
        return $ret;
    }

    /**
     * @param PaymentProviderInterface[] $providers
     *
     * @return array
     */
    public function paymentMethodsResponse(array $providers): array
    {
        $ret = [];
        foreach ($providers as $provider) {
            $ret[] = [
                'code'  => $provider->getIdentifier(),
                'title' => $provider->getTitle(),
            ];
        }
        return $ret;
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Response\Cart;

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

    public function shippingMethodsResponse(CarrierInterface $defaultMethod): array
    {
        $defaultShipping                   = [];
        $defaultShipping['carrier_code']   = $defaultMethod->getIdentifier();
        $defaultShipping['method_code']    = $defaultMethod->getIdentifier();
        $defaultShipping['carrier_title']  = $defaultMethod->getTitle();
        $defaultShipping['method_title']   = $defaultMethod->getTitle();
        $defaultShipping['amount']         = 5;
        $defaultShipping['base_amount']    = 5;
        $defaultShipping['available']      = true;
        $defaultShipping['error_message']  = "";
        $defaultShipping['price_excl_tax'] = 5;
        $defaultShipping['price_incl_tax'] = 5;

        return $defaultShipping;
    }

    /**
     * @param ProposalItemInterface $item
     *
     * @return array
     */
    public function singleCartItemResponse(ProposalItemInterface $item): array
    {
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
            'price'          => $item->getItemPrice(),
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
        $items = [];
        $totalQty = 0;
        foreach ($cart->getItems() as $cartItem) {
            $purchasable = $cartItem->getProduct();
            $totalQty += $cartItem->getQuantity();
            $items[]      = [
                'item_id'                 => $purchasable->getId(),
                'price'                   => $cartItem->getItemPrice(false),
                'base_price'              => $cartItem->getItemPrice(false),
                'qty'                     => $cartItem->getQuantity(),
                'row_total'               => $cartItem->getTotal(false),
                'base_row_total'          => $cartItem->getTotal(false),
                'row_total_with_discount' => 0,
                'tax_amount'              => $cartItem->getTotalTax(),
                'base_tax_amount'         => $cartItem->getTotalTax(),
                'tax_percent'             => 23,
                'discount_amount'         => 0,
                'base_discount_amount'    => 0,
                'discount_percent'        => 0,
                'price_incl_tax'          => $cartItem->getItemPrice(),
                'base_price_incl_tax'     => $cartItem->getItemPrice(),
                'row_total_incl_tax'      => $cartItem->getTotal(),
                'base_row_total_incl_tax' => $cartItem->getTotal(),
                'options'                 => '[]',
                'weee_tax_applied_amount' => null,
                'weee_tax_applied'        => null,
                'name'                    => $purchasable->getName(),
            ];
        }
        $ret   = [
            'payment_methods' => $this->paymentMethodsResponse($providers),
            'totals'          => [
                'grand_total'                   => $cart->getTotal(),
                'base_grand_total'              => $cart->getSubtotal(),
                'subtotal'                      => $cart->getSubtotal(),
                'base_subtotal'                 => $cart->getSubtotal(),
                'discount_amount'               => 0,
                'base_discount_amount'          => 0,
                'subtotal_with_discount'        => $cart->getSubtotal(),
                'base_subtotal_with_discount'   => $cart->getSubtotal(),
                'shipping_amount'               => $cart->getShipping(false),
                'base_shipping_amount'          => $cart->getShipping(false),
                'shipping_discount_amount'      => 0,
                'base_shipping_discount_amount' => 0,
                'tax_amount'                    => $cart->getTotalTax(),
                'base_tax_amount'               => $cart->getTotalTax(),
                'weee_tax_applied_amount'       => null,
                'shipping_tax_amount'           => $cart->getShippingTax(),
                'base_shipping_tax_amount'      => $cart->getShippingTax(),
                'subtotal_incl_tax'             => $cart->getSubtotal(),
                'shipping_incl_tax'             => $cart->getShipping(),
                'base_shipping_incl_tax'        => $cart->getShipping(),
                'base_currency_code'            => 'USD',
                'quote_currency_code'           => 'USD',
                'items_qty'                     => $totalQty,
                'items'                         => $items,
                'total_segments'                => [
                    0 => [
                        'code'  => 'subtotal',
                        'title' => 'Subtotal',
                        'value' => $cart->getSubtotal(),
                    ],
                    1 => [
                        'code'  => 'shipping',
                        'title' => 'Shipping & Handling (Flat Rate - Fixed)',
                        'value' => $cart->getShipping(),
                    ],
                    2 => [
                        'code'  => 'discount',
                        'title' => 'Discount',
                        'value' => 0,
                    ],
                    3 => [
                        'code'                 => 'tax',
                        'title'                => 'Tax',
                        'value'                => $cart->getTotalTax(),
                        'area'                 => 'taxes',
                        'extension_attributes' => [
                            'tax_grandtotal_details' => [
                                0 => [
                                    'amount'   => $cart->getTotalTax(),
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
                        'value' => $cart->getTotal(),
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

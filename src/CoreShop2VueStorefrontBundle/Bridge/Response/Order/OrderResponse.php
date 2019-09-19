<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Response\Order;

use CoreShop\Bundle\MoneyBundle\Formatter\MoneyFormatter;
use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Order\Model\OrderInterface;
use CoreShop2VueStorefrontBundle\Bridge\Response\ResponseBodyCreator;
use Pimcore\Model\DataObject\CoreShopOrderItem;

class OrderResponse extends ResponseBodyCreator
{
    const MOCKED_PAYMENT_TYPE = "Cash on Delivery";
    const MOCKED_PAYMENT_TYPE_ID = "cashondelivery";

    const MOCKED_SHIPPING_METHOD = "flatrate_flatrate";

    /**
     * @param OrderInterface[] $orderHistory
     *
     * @return array
     *
     * @todo handle discounts, different rates VAT, currency
     */
    public function orderHistoryResponse(array $orderHistory): array
    {
        /** @var MoneyFormatter $moneyFormatter */
        $moneyFormatter = \Pimcore::getContainer()->get('coreshop.money_formatter');
        $orderResponse = [];

        /** @var OrderInterface $order */
        foreach ($orderHistory as $order) {
            $orderResponseItem = [];

            $invoiceAddress = $order->getInvoiceAddress();

            $orderResponseItem['applied_rule_ids'] = "1,5";
            $orderResponseItem['base_currency_code'] = 'USD';
            $orderResponseItem['base_discount_amount'] = "";
            $orderResponseItem['base_grand_total'] = $moneyFormatter->format($order->getBaseTotal(false), 'USD');
            $orderResponseItem['base_discount_tax_compensation_amount'] = 0;
            $orderResponseItem['base_shipping_amount'] = $moneyFormatter->format($order->getShipping(false), 'USD');
            $orderResponseItem['base_shipping_discount_amount'] = 0;
            $orderResponseItem['base_shipping_incl_tax'] = $moneyFormatter->format($order->getBaseShipping(), 'USD');
            $orderResponseItem['base_shipping_tax_amount'] = $moneyFormatter->format($order->getBaseShippingTax(), 'USD');
            $orderResponseItem['base_subtotal'] = $moneyFormatter->format($order->getBaseSubtotal(false), 'USD');
            $orderResponseItem['base_subtotal_incl_tax'] = $moneyFormatter->format($order->getBaseSubtotal(), 'USD');
            $orderResponseItem['base_tax_amount'] = $moneyFormatter->format($order->getBaseTotalTax(), 'USD');
            $orderResponseItem['base_total_due'] = $moneyFormatter->format($order->getBaseTotal(), 'USD');
            $orderResponseItem['base_to_global_rate'] = 1;
            $orderResponseItem['base_to_order_rate'] = 1;
            $orderResponseItem['billing_address_id'] = $invoiceAddress ? $invoiceAddress->getId() : null;
            $orderResponseItem['created_at'] = $this->formatDate($order->getCreationDate());
            $orderResponseItem['customer_email'] = $order->getCustomer()->getEmail();
            $orderResponseItem['customer_group_id'] = 0;
            $orderResponseItem['customer_is_guest'] = 0;
            $orderResponseItem['customer_note_notify'] = 1;
            $orderResponseItem['discount_amount'] = 0;
            $orderResponseItem['email_sent'] = 1;
            $orderResponseItem['entity_id'] = $order->getId();
            $orderResponseItem['global_currency_code'] = 'USD';
            $orderResponseItem['grand_total'] = $moneyFormatter->format($order->getTotal(), 'USD');
            $orderResponseItem['discount_tax_compensation_amount'] = 0;
            $orderResponseItem['increment_id'] = "000000" . $order->getId();
            $orderResponseItem['is_virtual'] = 0;
            $orderResponseItem['order_currency_code'] = 'USD';
            $orderResponseItem['protect_code'] = null;
            $orderResponseItem['quote_id'] = null;
            $orderResponseItem['shipping_amount'] = $moneyFormatter->format($order->getShipping(false), 'USD');
            $orderResponseItem['shipping_description'] = "Flat Rate - Fixed";
            $orderResponseItem['shipping_discount_amount'] = 0;
            $orderResponseItem['shipping_discount_tax_compensation_amount'] = 0;
            $orderResponseItem['shipping_incl_tax'] = $moneyFormatter->format($order->getShipping(), 'USD');
            $orderResponseItem['shipping_tax_amount'] = $moneyFormatter->format($order->getShippingTax(), 'USD');
            $orderResponseItem['state'] = $order->getOrderState();
            $orderResponseItem['status'] = $order->getPaymentState();
            $orderResponseItem['store_currency_code'] = 'USD';
            $orderResponseItem['store_id'] = 1;
            $orderResponseItem['store_name'] = "Default";
            $orderResponseItem['store_to_base_rate'] = 0;
            $orderResponseItem['store_to_order_rate'] = 0;
            $orderResponseItem['subtotal'] = $moneyFormatter->format($order->getSubtotal(false), 'USD');
            $orderResponseItem['subtotal_incl_tax'] = $moneyFormatter->format($order->getSubtotal(), 'USD');
            $orderResponseItem['tax_amount'] = $moneyFormatter->format($order->getSubtotalTax(), 'USD');
            $orderResponseItem['total_due'] = $moneyFormatter->format($order->getTotal(), 'USD');
            $orderItems = $order->getItems();
            $orderResponseItem['total_item_count'] = count($orderItems);
            $orderResponseItem['total_qty_ordered'] = array_sum(array_map(function ($orderItem) {
                /** @var CoreShopOrderItem $orderItem */
                return $orderItem->getQuantity();
            }, $orderItems));
            $orderResponseItem['updated_at'] = $this->formatDate($order->getModificationDate());
            $orderResponseItem['weight'] = $order->getWeight();
            $orderResponseItem['items'] = $this->orderProductItems($orderItems);

            if ($invoiceAddress) {
                $orderResponseItem['billing_address'] = $this->orderAddress(
                    $invoiceAddress,
                    $order->getCustomer(),
                    'billing'
                );
            }

            $orderResponseItem['payment'] = $this->orderPayment($order);
            $orderResponseItem['status_histories'] = [];
            $orderResponseItem['extension_attributes'] = $this->orderExtensionsAttributes($order);
            $orderResponseItem['extension_attributes']['shipping_assignments'][]['items'] = $orderResponseItem['items'];

            $orderResponseItem['search_criteria'] = [];

            $orderResponse['items'][] = $orderResponseItem;
        }

        return $orderResponse;
    }

    private function orderProductItems(array $productItems): array
    {
        /** @var MoneyFormatter $moneyFormatter */
        $moneyFormatter = \Pimcore::getContainer()->get('coreshop.money_formatter');
        $orderResponse = [];

        $orderProductItemsResponse = [];

        $orderProductItem  = [];

        /** @var CoreShopOrderItem $orderItem */
        foreach ($productItems as $orderItem) {
            $orderProductItem['amount_refunded'] = 0;
            $orderProductItem['applied_rule_ids'] = "1,5";
            $orderProductItem['base_amount_refunded'] = 0;
            $orderProductItem['base_discount_amount'] = 0;
            $orderProductItem['base_discount_invoiced'] = 0;
            $orderProductItem['base_discount_tax_compensation_amount'] = 0;
            $orderProductItem['base_original_price'] = $moneyFormatter->format($orderItem->getBaseItemPriceNet(), 'USD');
            $orderProductItem['base_price'] = $moneyFormatter->format($orderItem->getBaseItemPriceNet(), 'USD');
            $orderProductItem['base_price_incl_tax'] = $moneyFormatter->format($orderItem->getBaseItemPriceGross(), 'USD');
            $orderProductItem['base_row_invoiced'] = 0;
            $orderProductItem['base_row_total'] = $moneyFormatter->format($orderItem->getBaseTotal(false), 'USD');
            $orderProductItem['base_row_total_incl_tax'] = $moneyFormatter->format($orderItem->getBaseTotalTax(), 'USD');
            $orderProductItem['base_tax_amount'] = $moneyFormatter->format($orderItem->getBaseTotalTax(), 'USD');
            $orderProductItem['base_tax_invoiced'] = 0;
            $orderProductItem['created_at'] = $this->formatDate($orderItem->getCreationDate());
            $orderProductItem['discount_amount'] = 0;
            $orderProductItem['discount_invoiced'] = 0;
            $orderProductItem['discount_percent'] = 0;
            $orderProductItem['free_shipping'] = 0;
            $orderProductItem['discount_tax_compensation_amount'] = 0;
            $orderProductItem['is_qty_decimal'] = 0;
            $orderProductItem['is_virtual'] = 0;
            $orderProductItem['item_id'] = $orderItem->getId();
            $orderProductItem['name'] = $orderItem->getName();
            $orderProductItem['no_discount'] = 0;
            $orderProductItem['order_id'] = $orderItem->getOrder()->getId();
            $orderProductItem['original_price'] = $moneyFormatter->format($orderItem->getBaseItemPriceNet(), 'USD');
            $orderProductItem['price'] = $moneyFormatter->format($orderItem->getBaseItemPriceNet(), 'USD');
            $orderProductItem['price_incl_tax'] = $moneyFormatter->format($orderItem->getBaseItemPriceGross(), 'USD');
            $orderProductItem['product_id'] = $orderItem->getId();
            $orderProductItem['product_type'] = $orderItem->getType();
            $orderProductItem['qty_canceled'] = 0;
            $orderProductItem['qty_invoiced'] = 0;
            $orderProductItem['qty_ordered'] = $orderItem->getQuantity();
            $orderProductItem['qty_refunded'] = 0;
            $orderProductItem['qty_shipped'] = 0;
            $orderProductItem['quote_item_id'] = 0;
            $orderProductItem['row_invoiced'] = 0;
            $orderProductItem['row_total'] = $moneyFormatter->format($orderItem->getTotal(false), 'USD');
            $orderProductItem['row_total_incl_tax'] = $moneyFormatter->format($orderItem->getTotal(), 'USD');
            $orderProductItem['row_weight'] = $orderItem->getItemWeight();
            $orderProductItem['sku'] = $orderItem->getProduct()->getSku();
            $orderProductItem['store_id'] = 1;
            $orderProductItem['tax_amount'] = $moneyFormatter->format($orderItem->getTotalTax(), 'USD');
            $orderProductItem['tax_invoiced'] = 0;
            $orderProductItem['tax_percent'] = 23;
            $orderProductItem['updated_at'] = $this->formatDate($orderItem->getModificationDate());
            $orderProductItem['weight'] = $orderItem->getItemWeight();

            $orderProductItemsResponse[] = $orderProductItem;
        }

        return $orderProductItemsResponse;
    }

    private function orderAddress(AddressInterface $addressInterface, CustomerInterface $customer, string $type): array
    {
        $address = [];
        $address['address_type'] = $type;
        $address['city'] = $addressInterface->getCity();
        $address['company'] = $addressInterface->getCompany();
        $address['country_id'] = $addressInterface->getCountry()->getIsoCode();
        $address['email'] = $customer->getEmail();
        $address['firstname'] = $addressInterface->getFirstname();
        $address['lastname'] = $addressInterface->getLastname();
        $address['entity_id'] = $addressInterface->getId();
        $address['parent_id'] = $addressInterface->getParentId();
        $address['postcode'] = $addressInterface->getPostcode();
        $address['street'] = [$addressInterface->getStreet(), $addressInterface->getNumber()];
        $address['telephone'] = $addressInterface->getPhoneNumber();
        $address['vat_id'] = null;

        return $address;
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     *
     * @todo
     */
    private function orderPayment(OrderInterface $order): array
    {
        $payment = [];
        $payment['account_status'] = null;
        $payment['additional_information'] = [self::MOCKED_PAYMENT_TYPE, ""];
        $payment['amount_ordered'] = 28;
        $payment['base_amount_ordered'] = 28;
        $payment['base_shipping_amount'] = 5;
        $payment['cc_last4'] = null;
        $payment['entity_id'] = $order->getId();
        $payment['method'] = $order->getPaymentProvider()
            ? $order->getPaymentProvider()->getIdentifier() : self::MOCKED_PAYMENT_TYPE_ID;

        $payment['parent_id'] = $order->getParentId();
        $payment['shipping_amount'] = 5;

        return $payment;
    }

    private function orderExtensionsAttributes(OrderInterface $order): array
    {
        $orderExtensionsAttributes = [];

        $shipping = [];
        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shipping['address'] = $this->orderAddress(
                $shippingAddress,
                $order->getCustomer(),
                'shipping'
            );
        }

        $shipping['method'] = self::MOCKED_SHIPPING_METHOD;

        $totalData = [];
        $shipping['total'] = $totalData;

        $orderExtensionsAttributes['shipping_assignments'][]['shipping'] = $shipping;

        return $orderExtensionsAttributes;
    }
}

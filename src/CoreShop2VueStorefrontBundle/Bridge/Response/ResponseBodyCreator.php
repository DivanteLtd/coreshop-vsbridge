<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Response;

use Carbon\Carbon;
use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use CoreShop2VueStorefrontBundle\Repository\AttributeRepository;
use CoreShop\Component\Index\Model\IndexInterface;

class ResponseBodyCreator
{
    /** @var RepositoryInterface */
    protected $indicesRepository;
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * ResponseBodyCreator constructor.
     *
     * @param RepositoryInterface $indicesRepository
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(RepositoryInterface $indicesRepository, AttributeRepository $attributeRepository)
    {
        $this->indicesRepository   = $indicesRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return IndexInterface|null
     * @todo multi store support
     */
    protected function getCurrentIndex(): ?IndexInterface
    {
        return $this->indicesRepository->findOneBy(['worker' => 'elasticsearch']);
    }

    public function userCreateResponse(CustomerInterface $customer): array
    {
        $response = [];

        $response['id'] = $customer->getId();
        $response['group_id'] = 1;

        $response = $this->formatDateTimeFields($customer, $response);

        $response['created_in'] = "Default Store View";
        $response['email'] = $customer->getEmail();
        $response['firstname'] = $customer->getFirstname();
        $response['lastname'] = $customer->getLastname();
        $response['store_id'] = 1;
        $response['website_id'] = 1;
        $response['addresses'] = [];
        $response['disable_auto_group_change'] = 0;

        return $response;
    }

    public function userMeResponse(CustomerInterface $customer): array
    {
        $response = [];
        $response['id'] = $customer->getId();
        $response['group_id'] = 1;

        $address = $customer->getDefaultAddress();
        $response['default_shipping'] = $address->getId();

        $response = $this->formatDateTimeFields($customer, $response);

        $response['email'] = $customer->getEmail();
        $response['firstname'] = $customer->getFirstname();
        $response['lastname'] = $customer->getLastname();
        $response['store_id'] = 1;
        $response['website_id'] = 1;
        $response['addresses'][] = $this->getAddress($address, $customer->getId());
            
        $response['disable_auto_group_change'] = 0;

        return $response;
    }

    private function formatDateTimeFields(CustomerInterface $customer, $response): array
    {
        $response['created_at'] = $this->formatDate($customer->getCreationDate());
        $response['updated_at'] = $this->formatDate($customer->getModificationDate());
        return $response;
    }

    private function getAddress(AddressInterface $defaultAddress, int $customerId): array
    {
        $address = [];

        $address['id'] = $defaultAddress->getId();
        $address['customer_id'] = $customerId;
        $address['region']['region_code'] = null;
        $address['region']['region'] = null;
        $address['region']['region_id'] = 0;
        $address['region_id'] = 0;
	if (!is_null($defaultAddress->getCountry()))
	    $address['country_id'] = $defaultAddress->getCountry()->getIsoCode();
        $address['street'][] = $defaultAddress->getStreet();
        $address['street'][] = $defaultAddress->getNumber();
        $address['telephone'] = $defaultAddress->getPhoneNumber();
        $address['postcode'] = $defaultAddress->getPostcode();
        $address['city'] = $defaultAddress->getCity();
        $address['firstname'] = $defaultAddress->getFirstname();
        $address['lastname'] = $defaultAddress->getLastname();
        $address['default_shipping'] = true;

        return $address;
    }

    public function stockResponse(ProductInterface $product): array
    {
        $stock = [];
        $stock['item_id'] = $product->getId();
        $stock['product_id'] = $product->getId();
        $stock['stock_id'] = 1;
        $stock['qty'] = $product->getOnHand() ?: 0;
        $stock['is_in_stock'] = $product->getOnHand() > 0 ? true : false;

        return $stock;
    }

    protected function formatDate(string $dateTime): string
    {
        return Carbon::createFromTimestamp($dateTime)->format('Y-m-d H:i:s');
    }
}

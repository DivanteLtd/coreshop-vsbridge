<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Order;

use CoreShop\Bundle\CoreBundle\Doctrine\ORM\CountryRepository;
use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Pimcore\DataObject\ObjectService;
use CoreShop\Component\Pimcore\DataObject\ObjectServiceInterface;
use CoreShop\Component\Resource\Factory\PimcoreFactory;

class AddressDataToAddressItemTransformer
{
    /** @var PimcoreFactory */
    private $addressFactory;
    /** @var ObjectService */
    private $objectService;
    /** @var CountryRepository */
    private $countryRepository;
    /** @var string */
    private $pathForAddress;

    public function __construct(
        PimcoreFactory $addressFactory,
        ObjectServiceInterface $objectService,
        string $pathForAddress,
        CountryRepository $countryRepository
    ) {
        $this->addressFactory = $addressFactory;
        $this->objectService = $objectService;
        $this->countryRepository = $countryRepository;
        $this->pathForAddress = $pathForAddress;
    }

    public function transform(array $addressData, OrderInterface $order, string $key)
    {
        $addressesFolder = $this->objectService->createFolderByPath(
            sprintf('%s/%s', $order->getFullPath(), $this->pathForAddress)
        );

        /** @var AddressInterface $address */
        $address = $this->addressFactory->createNew();
        $address->setKey($key);
        $address->setParent($addressesFolder);
        $address->setPublished(true);

        $address->setFirstname($addressData['firstname']);
        $address->setLastname($addressData['lastname']);
        $address->setCompany($addressData['company']);
        $address->setStreet($addressData['street'][0]);
        $address->setNumber($addressData['street'][1]);
        $address->setPostcode($addressData['postcode']);
        $address->setCity($addressData['city']);
        $country = $this->countryRepository->findByCode($addressData['country_id']);
        $address->setCountry($country);
        $address->setPhoneNumber($addressData['telephone']);
        $address->save();

        return $address;
    }
}

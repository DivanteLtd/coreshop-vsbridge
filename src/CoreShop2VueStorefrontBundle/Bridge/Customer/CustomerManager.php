<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Customer;

use CoreShop\Bundle\CoreBundle\Customer\CustomerAlreadyExistsException;
use CoreShop\Bundle\CoreBundle\Customer\RegistrationService;
use CoreShop\Bundle\CoreBundle\Doctrine\ORM\CountryRepository;
use CoreShop\Bundle\CustomerBundle\Pimcore\Repository\CustomerRepository;
use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Resource\Factory\PimcoreFactoryInterface;
use LogicException;

class CustomerManager
{
    const DEFAULT_COUNTRY_CODE = "PL";

    /** @var RegistrationService */
    private $registrationService;
    /** @var CountryRepository */
    private $countryRepository;
    /** @var PimcoreFactoryInterface */
    private $customerFactory;
    /** @var PimcoreFactoryInterface */
    private $addressFactory;
    /** @var CustomerRepository */
    private $customerRepository;

    public function __construct(
        PimcoreFactoryInterface $customerFactory,
        PimcoreFactoryInterface $addressFactory,
        RegistrationService $registrationService,
        CountryRepository $countryRepository,
        CustomerRepository $customerRepository
    ) {
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->registrationService = $registrationService;
        $this->countryRepository = $countryRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param array $user
     *
     * @return CustomerInterface
     *
     * @throws CustomerAlreadyExistsException
     */
    public function createCustomer(array $user): CustomerInterface
    {
        $customer = $this->customerFactory->createNew();
        $customer->setEmail($user['email']);
        $customer->setFirstname($user['firstname']);
        $customer->setLastname($user['lastname']);
        $customer->setPassword($user['password']);

        /** @var AddressInterface $address */
        $address = $this->addressFactory->createNew();
        $defaultCountry = $this->countryRepository->findByCode(self::DEFAULT_COUNTRY_CODE);
        $address->setCountry($defaultCountry->getId());
        $address->setFirstname($customer->getFirstname());
        $address->setLastname($customer->getLastname());

        $formData['customer'] = $customer;
        $formData['address'] = $address;

        $this->registrationService->registerCustomer(
            $customer,
            $address,
            $formData
        );

        return $customer;
    }

    /**
     * @param array $customerData
     * @return array|bool|mixed
     */
    public function editCustomer(array $customerData): CustomerInterface
    {
        $customer = $this->customerRepository->findCustomerByEmail($customerData['email']);

        if (!$customer instanceof CustomerInterface) {
            throw new LogicException("Username could not be found.");
        }

        $customer->setFirstname($customerData['firstname']);
        $customer->setLastname($customerData['lastname']);
        $customer->save();

        $address = $customer->getDefaultAddress();

        $addressData = $customerData['addresses'][0] ?? [];

	if (!empty($addressData)) {
	    if (!is_null($this->countryRepository->findByCode($addressData['country_id']))) {
		$byCodeCountryId = $this->countryRepository->findByCode($addressData['country_id'])->getId();
		$addressData['country_id'] = $byCodeCountryId;
	    }
	}

        $address->setLastname($addressData['lastname']);
        $address->setFirstname($addressData['firstname']);
        $address->setCompany($addressData['company']);
        $address->setPostcode($addressData['postcode']);
        $address->setCity($addressData['city']);
        $address->setPhoneNumber($addressData['telephone']);
        $address->setCountry($addressData['country_id']);
        $address->setStreet($addressData['street'][0]);
        $address->setNumber($addressData['street'][1]);
        $address->save();

        return $customer;
    }
}

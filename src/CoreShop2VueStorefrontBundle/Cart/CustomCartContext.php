<?php
namespace CoreShop2VueStorefrontBundle\Cart;

use CoreShop\Component\Customer\Context\CustomerContextInterface;
use CoreShop\Component\Customer\Context\CustomerNotFoundException;
use CoreShop\Component\Order\Context\CartContextInterface;
use CoreShop\Component\Order\Context\CartNotFoundException;
use CoreShop\Component\Order\Repository\CartRepositoryInterface;
use CoreShop\Component\Store\Context\StoreContextInterface;
use CoreShop\Component\Store\Context\StoreNotFoundException;
use Pimcore\Http\RequestHelper;

final class CustomCartContext implements CartContextInterface
{
    /**
     * @var CustomerContextInterface
     */
    private $customerContext;

    /**
     * @var StoreContextInterface
     */
    private $storeContext;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var RequestHelper
     */
    private $pimcoreRequestHelper;

    /**
     * @param CustomerContextInterface $customerContext
     * @param StoreContextInterface $storeContext
     * @param CartRepositoryInterface $cartRepository
     * @param RequestHelper $pimcoreRequestHelper
     */
    public function __construct(
        CustomerContextInterface $customerContext,
        StoreContextInterface $storeContext,
        CartRepositoryInterface $cartRepository,
        RequestHelper $pimcoreRequestHelper
    )
    {
        $this->customerContext = $customerContext;
        $this->storeContext = $storeContext;
        $this->cartRepository = $cartRepository;
        $this->pimcoreRequestHelper = $pimcoreRequestHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart()
    {
        try {
            $store = $this->storeContext->getStore();
        } catch (StoreNotFoundException $exception) {
            throw new CartNotFoundException('CoreShop was not able to find the cart, as there is no current store.');
        }

        try {
            $customer = $this->customerContext->getCustomer();
        } catch (CustomerNotFoundException $exception) {
            throw new CartNotFoundException('CoreShop was not able to find the cart, as there is no logged in user.');
        }

        $cart = $this->cartRepository->findLatestByStoreAndCustomer($store, $customer);
        if (null === $cart) {
            throw new CartNotFoundException('CoreShop was not able to find the cart for currently logged in user.');
        }

        return $cart;
    }
}

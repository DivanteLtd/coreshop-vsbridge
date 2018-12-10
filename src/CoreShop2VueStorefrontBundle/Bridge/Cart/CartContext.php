<?php
namespace CoreShop2VueStorefrontBundle\Bridge\Cart;

use CoreShop\Component\Core\Model\CartInterface;
use CoreShop\Component\Customer\Context\CustomerContextInterface;
use CoreShop\Component\Customer\Context\CustomerNotFoundException;
use CoreShop\Component\Customer\Model\CustomerInterface;
use CoreShop\Component\Order\Context\CartContextInterface;
use CoreShop\Component\Order\Context\CartNotFoundException;
use CoreShop\Component\Order\Repository\CartRepositoryInterface;
use CoreShop\Component\Store\Context\StoreContextInterface;
use CoreShop\Component\Store\Context\StoreNotFoundException;
use CoreShop\Component\Store\Model\StoreInterface;
use Pimcore\Http\RequestHelper;

final class CartContext implements CartContextInterface
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

        $cart = $this->findLatestByStoreAndCustomer($store, $customer);
        if (null === $cart) {
            throw new CartNotFoundException('CoreShop was not able to find the cart for currently logged in user.');
        }

        return $cart;
    }

    /**
     * @param StoreInterface    $store
     * @param CustomerInterface $customer
     *
     * @return null|CartInterface
     */
    public function findLatestByStoreAndCustomer(StoreInterface $store, CustomerInterface $customer)
    {
        $list = $this->cartRepository->getList();
        $list
            ->setCondition('customer__id = ? AND store = ? AND order__id is null ', [$customer->getId(), $store->getId()])
            ->setOrderKey('o_id')
            ->setOrder('DESC')
            ->setLimit(1)
            ->load();

        if ($list->getTotalCount() > 0) {
            $objects = $list->getObjects();

            return $objects[0];
        }

        return null;
    }
}

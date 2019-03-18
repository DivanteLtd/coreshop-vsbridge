<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop\Component\Customer\Model\CustomerInterface;
use CoreShop\Component\Customer\Repository\CustomerRepositoryInterface;
use CoreShop\Component\Order\Repository\CartRepositoryInterface;
use CoreShop\Component\Store\Context\StoreContextInterface;
use CoreShop\Component\Store\Model\StoreInterface;
use CoreShop2VueStorefrontBundle\Bridge\Order\OrderManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
{
    /**
     * @Route("/vsbridge/order")
     * @Route("/vsbridge/order/create")
     * @Method("POST")
     *
     * @param Request $request
     * @param OrderManager $orderManager
     *
     * @return JsonResponse
     */
    public function createOrder(Request $request, OrderManager $orderManager)
    {
        try {
            $cart = $this->findLatestByStoreAndCustomer(
                $this->getStoreContext()->getStore(),
                $this->getCustomerRepository()->find($request->get('user_id'))
            );

            $orderManager->createOrder(
		$request->get('order_id',0),
                $request->get('user_id'),
                $cart,
                $request->get('addressInformation')
            );

            return $this->json([
                'code' => 200,
                'result' => 'OK'
            ]);
        } catch (Exception $unexpectedException) {
            return $this->json([
                'code' => 200,
                'result' => sprintf("Can't proceed new order request. %s.", $unexpectedException->getMessage())
            ]);
        }
    }

    /**
     * @return CartRepositoryInterface
     */
    protected function getCartRepository()
    {
        return $this->get('coreshop.repository.cart');
    }

    /**
     * @return CustomerRepositoryInterface
     */
    protected function getCustomerRepository()
    {
        return $this->get('coreshop.repository.customer');
    }

    /**
     * @return StoreContextInterface
     */
    protected function getStoreContext()
    {
        return $this->get('coreshop.context.store');
    }

    public function findLatestByStoreAndCustomer(StoreInterface $store, CustomerInterface $customer)
    {
        $list = $this->getCartRepository()->getList();
        $list->setCondition('customer__id = ? AND store = ? AND order__id is null ', [$customer->getId(), $store->getId()]);
        $list->setOrderKey('o_id');
        $list->setOrder('DESC');
        $list->load();

        if ($list->getTotalCount() > 0) {
            $objects = $list->getObjects();

            return $objects[0];
        }

        return null;
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop\Bundle\OrderBundle\Pimcore\Repository\OrderRepository;
use CoreShop\Component\Customer\Model\CustomerInterface;
use CoreShop\Component\Customer\Repository\CustomerRepositoryInterface;
use CoreShop\Component\Order\Repository\CartRepositoryInterface;
use CoreShop\Component\Store\Context\StoreContextInterface;
use CoreShop\Component\Store\Model\StoreInterface;
use CoreShop2VueStorefrontBundle\Bridge\Order\OrderManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrderController extends Controller
{
    /**
     * @Route("/vsbridge/order", methods={"POST"})
     * @Route("/vsbridge/order/create", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createOrder(Request $request)
    {
        try {
            $orderManager = $this->get(OrderManager::class);
            $orderRepository = $this->get('coreshop.repository.order');
            $orderId = $request->get('order_id', 0);

            $order = $orderRepository->findOneBy(['orderNumber' => $orderNumber]); //@FIXME
            if ($order instanceof OrderInterface) {
                throw new LogicException(sprintf("Order number %s already exists", $orderNumber));
            }

            $cart = $this->findLatestByStoreAndCustomer(
                $this->getStoreContext()->getStore(),
                $this->getCustomerRepository()->find($request->get('user_id'))
            );

            $orderManager->createOrder(
                $orderId,
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

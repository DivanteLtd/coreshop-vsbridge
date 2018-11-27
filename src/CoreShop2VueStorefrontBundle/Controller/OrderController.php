<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop2VueStorefrontBundle\Bridge\Order\OrderManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends AbstractController
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
            $orderManager->createOrder(
                $request->get('order_id'),
                $request->get('user_id'),
                $request->get('cart_id'),
                $request->get('products'),
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
}

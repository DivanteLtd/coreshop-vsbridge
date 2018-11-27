<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop\Bundle\CoreBundle\Customer\CustomerAlreadyExistsException;
use CoreShop\Bundle\CustomerBundle\Event\RequestPasswordChangeEvent;
use CoreShop\Bundle\CustomerBundle\Pimcore\Repository\CustomerRepository;
use CoreShop\Bundle\OrderBundle\Pimcore\Repository\OrderRepository;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Pimcore\Routing\LinkGenerator;
use CoreShop2VueStorefrontBundle\Bridge\Customer\CustomerManager;
use CoreShop2VueStorefrontBundle\Bridge\Response\Order\OrderResponse;
use CoreShop2VueStorefrontBundle\Bridge\Response\ResponseBodyCreator;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerController extends AbstractController
{
    /**
     * @Route("/vsbridge/user/login")
     * @Method("POST")
     *
     * @return Response
     */
    public function login()
    {
        return new Response('', 401);
    }

    /**
     * @Route("/vsbridge/user/create")
     * @Method("POST")
     *
     * @param Request $request
     * @param CustomerManager $customerManager
     * @param ResponseBodyCreator $responseBodyCreator
     * @return JsonResponse
     */
    public function createUser(
        Request $request,
        CustomerManager $customerManager,
        ResponseBodyCreator $responseBodyCreator
    ) {
        try {
            $data = $request->get('customer');
            $data['password'] = $request->get('password');

            $customer = $customerManager->createCustomer($data);

            return $this->json([
                'code' => 200,
                'result' => $responseBodyCreator->userCreateResponse($customer)
            ]);
        } catch (CustomerAlreadyExistsException $customerAlreadyExistsException) {
            return $this->json([
                'code' => 500,
                'result' => "User with given address already exists"
            ]);
        }
    }

    /**
     * @Route("/vsbridge/user/me")
     * @Method("POST")
     *
     * @param Request $request
     * @param CustomerManager $customerManager
     * @param ResponseBodyCreator $responseBodyCreator
     *
     * @return JsonResponse
     */
    public function editUser(
        Request $request,
        CustomerManager $customerManager,
        ResponseBodyCreator $responseBodyCreator
    ) {
        try {
            $customer = $customerManager->editCustomer($request->get('customer'));

            return $this->json([
                'code' => 200,
                'result' => $responseBodyCreator->userMeResponse($customer)
            ]);
        } catch (Exception $usernameNotFoundException) {
            return $this->json([
                'code' => 500,
                'result' => sprintf(
                    "There was a problem with edit user data. Try again. Error %s",
                    $usernameNotFoundException->getMessage()
                )
            ]);
        }
    }

    /**
     * @Route("/vsbridge/user/me")
     * @Method("GET")
     *
     * @param ResponseBodyCreator $responseBodyCreator
     *
     * @return JsonResponse
     */
    public function userProfile(ResponseBodyCreator $responseBodyCreator)
    {
        return $this->json([
            'code' => 200,
            'result' => $responseBodyCreator->userMeResponse($this->getUser())
        ]);
    }

    /**
     * @Route("/vsbridge/user/reset-password")
     * @Method("POST")
     *
     * @param Request $request
     * @param CustomerRepository $customerRepository
     * @param LinkGenerator $linkGenerator
     * @param EventDispatcher $eventDispatcher
     *
     * @return JsonResponse
     */
    public function resetPassword(
        Request $request,
        CustomerRepository $customerRepository,
        LinkGenerator $linkGenerator,
        EventDispatcher $eventDispatcher
    ) {
        $email = $request->get('email');

        /** @var CustomerInterface $customer */
        $customer = $customerRepository->findCustomerByEmail($email);

        if (!$customer instanceof CustomerInterface) {
            return $this->json([
                'code' => 500,
                'result' => sprintf('No such entity with email = %s, websiteId = 1', $email)
            ]);
        }

        $customer->setPasswordResetHash(hash('md5', $customer->getId() . $customer->getEmail() . mt_rand() . time()));
        $customer->save();

        $resetLink = $linkGenerator->generate(
            null,
            'coreshop_customer_password_reset',
            ['token' => $customer->getPasswordResetHash()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $eventDispatcher->dispatch(
            'coreshop.customer.request_password_reset',
            new RequestPasswordChangeEvent($customer, $resetLink)
        );

        return $this->json(['code' => 200]);
    }

    /**
     * @Route("/vsbridge/user/changePassword")
     * @Method("POST")
     *
     * @param Request $request
     * @param CustomerRepository $customerRepository
     *
     * @return JsonResponse
     */
    public function changePassword(Request $request, CustomerRepository $customerRepository)
    {
        /** @var CustomerInterface $customer */
        $email = $this->getUser()->getEmail();

        $customer = $customerRepository->findCustomerByEmail($email);
        if (!$customer instanceof CustomerInterface) {
            return $this->json([
                'code' => 500,
                'result' => sprintf('No such entity with email = %s, websiteId = 1', $email)
            ]);
        }

        try {
            $customer->setPassword($request->get('newPassword'));
            $customer->save();

            return $this->json(['code' => 200]);
        } catch (Exception $exception) {
            return $this->json(['code' => 500]);
        }
    }

    /**
     * @Route("/vsbridge/user/order-history")
     * @Method("GET")
     *
     * @param OrderRepository $orderRepository
     * @param OrderResponse $orderResponse
     *
     * @return JsonResponse
     */
    public function orderHistory(OrderRepository $orderRepository, OrderResponse $orderResponse)
    {
        $customer = $this->getUser();

        $orderHistory = $orderRepository->findByCustomer($customer);

        return $this->json([
            'code' => 200,
            'result' => $orderResponse->orderHistoryResponse($orderHistory)
        ]);
    }
}

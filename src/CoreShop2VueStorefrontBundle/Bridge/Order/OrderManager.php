<?php

namespace CoreShop2VueStorefrontBundle\Bridge\Order;

use Carbon\Carbon;
use CoreShop\Bundle\CustomerBundle\Pimcore\Repository\CustomerRepository;
use CoreShop\Bundle\OrderBundle\Pimcore\Repository\OrderRepository;
use CoreShop\Bundle\WorkflowBundle\Applier\StateMachineApplierInterface;
use CoreShop\Component\Core\Model\CartInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Order\OrderStates;
use CoreShop\Component\Order\OrderTransitions;
use CoreShop\Component\Order\Transformer\ProposalTransformerInterface;
use CoreShop\Component\Pimcore\DataObject\ObjectServiceInterface;
use CoreShop\Component\Resource\Factory\PimcoreFactory;
use CoreShop\Component\Resource\Factory\PimcoreFactoryInterface;
use CoreShop\Component\Store\Repository\StoreRepositoryInterface;
use LogicException;
use Pimcore\File;

class OrderManager
{
    /** @var OrderRepository */
    private $orderRepository;
    /** @var CustomerRepository */
    private $customerRepository;
    /** @var PimcoreFactory */
    private $orderFactory;
    private $orderFolderPath;
    /** @var ObjectServiceInterface */
    private $objectService;
    /** @var PimcoreFactoryInterface */
    private $orderItemFactory;
    /** @var AddressDataToAddressItemTransformer */
    private $addressDataToAddressItemTransformer;
    /** @var StateMachineApplierInterface */
    private $stateMachineApplier;
    /** @var ProposalTransformerInterface */
    private $proposalTransformer;
    /** @var StoreRepositoryInterface */
    private $storeRepository;

    const SHIPPING_ADDRESS = 'shipping';
    const INVOICE_ADDRESS = 'invoice';

    public function __construct(
        PimcoreFactoryInterface $pimcoreFactory,
        string $orderFolderPath,
        ObjectServiceInterface $objectService,
        PimcoreFactoryInterface $orderItemFactory,
        StateMachineApplierInterface $stateMachineApplier,
        ProposalTransformerInterface $proposalTransformer,
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        AddressDataToAddressItemTransformer $addressDataToAddressItemTransformer,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->orderFactory = $pimcoreFactory;
        $this->orderFolderPath = $orderFolderPath;
        $this->objectService = $objectService;
        $this->orderItemFactory = $orderItemFactory;
        $this->addressDataToAddressItemTransformer = $addressDataToAddressItemTransformer;
        $this->stateMachineApplier = $stateMachineApplier;
        $this->proposalTransformer = $proposalTransformer;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param string        $orderNumber
     * @param string        $customerId
     * @param CartInterface $cart
     * @param array         $addressInformation
     *
     * @return void
     * @throws \Exception
     * @todo
     */
    public function createOrder(
        string $orderNumber,
        string $customerId,
        CartInterface $cart,
        array $addressInformation
    ) {
        $order = $this->orderRepository->findOneBy(['orderNumber' => $orderNumber]);
        if ($order instanceof OrderInterface) {
            throw new LogicException(sprintf("Order number %s already exists", $orderNumber));
        }

        /** @var OrderInterface $newOrder */
        $newOrder = $this->orderFactory->createNew();
        $newOrder->setOrderNumber($orderNumber);
        $newOrder->setPublished(true);
        $newOrder->setOrderDate(Carbon::now());
        $newOrder->setStore($this->storeRepository->findStandard());

        $orderFolder = $this->objectService->createFolderByPath(
            sprintf('%s/%s', $this->orderFolderPath, date('Y/m/d'))
        );

        $newOrder->setParent($orderFolder);
        $newOrder->setKey(File::getValidFilename($orderNumber));

        $customer = $this->customerRepository->find($customerId);
        if (!$customer) {
            throw new LogicException(sprintf("Can't create order for not existing customer"));
        }
        if (isset($addressInformation['shippingAddress'])) {
            $cart->setShippingAddress(
                $this->addressDataToAddressItemTransformer->transform(
                    $addressInformation['shippingAddress'],
                    $newOrder,
                    self::SHIPPING_ADDRESS
                )
            );
        }

        if (isset($addressInformation['billingAddress'])) {
            $cart->setInvoiceAddress(
                $this->addressDataToAddressItemTransformer->transform(
                    $addressInformation['billingAddress'],
                    $newOrder,
                    self::INVOICE_ADDRESS
                )
            );
        }
        $newOrder = $this->proposalTransformer->transform($cart, $newOrder);

        $newOrder->setCustomer($customer);
        $newOrder->save();

        $newOrder->setOrderState(OrderStates::STATE_INITIALIZED);
        $newOrder->setShippingState(OrderStates::STATE_NEW);
        $newOrder->setInvoiceState(OrderStates::STATE_NEW);
        $newOrder->save();

        if ($newOrder instanceof OrderInterface) {
            $this->stateMachineApplier->apply($newOrder, 'coreshop_order', OrderTransitions::TRANSITION_CREATE);
        }
    }

}

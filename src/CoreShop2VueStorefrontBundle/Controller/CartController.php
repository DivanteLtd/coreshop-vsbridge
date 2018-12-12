<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop\Bundle\CoreBundle\Doctrine\ORM\CarrierRepository;
use CoreShop\Bundle\CoreBundle\Doctrine\ORM\PaymentProviderRepository;
use CoreShop\Component\Core\Model\CartInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop\Component\Inventory\Model\StockableInterface;
use CoreShop\Component\Order\Context\CartContextInterface;
use CoreShop\Component\Order\Manager\CartManagerInterface;
use CoreShop\Component\Order\Model\CartItemInterface;
use CoreShop\Component\Order\Model\PurchasableInterface;
use CoreShop\Component\Payment\Model\PaymentProviderInterface;
use CoreShop\Component\Shipping\Model\Carrier;
use CoreShop\Component\StorageList\StorageListModifierInterface;
use CoreShop2VueStorefrontBundle\Bridge\Response\Cart\CartResponse;
use CoreShop2VueStorefrontBundle\Bridge\Attribute\AttributeResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class CartController extends Controller
{
    /** @var AttributeResolver */
    private $attributeResolver;

    /**
     * @Route("/vsbridge/cart/create")
     * @Method("POST")
     *
     * @return JsonResponse
     */
    public function cartCreate()
    {
        return $this->json([
            'status' => 200,
            'result' => $this->getCart()->getId(),
        ]);
    }

    /**
     * @Route("/vsbridge/cart/pull")
     * @Method("GET")
     *
     * @param CartResponse $cartResponse
     *
     * @return JsonResponse
     *
     * @todo handle cartId, I couldn't to this because VSF doesn't send it locally
     */
    public function pull(CartResponse $cartResponse)
    {
        $items = $this->getCart()->getItems();

        return $this->json([
            'code'   => 200,
            'result' => $cartResponse->cartItemsResponse($items),
        ]);
    }

    /**
     * @Route("/vsbridge/cart/update")
     * @Method("POST")
     *
     * @param Request                    $request
     * @param CartResponse               $cartResponse
     * @param TranslatorInterface        $translator
     * @param ProductRepositoryInterface $productRepository
     *
     * @return JsonResponse
     *
     * @throws \Exception
     * @todo handle cartId, I couldn't to this because VSF doesn't send it locally
     */
    public function update(
        Request $request,
        CartResponse $cartResponse,
        TranslatorInterface $translator,
        ProductRepositoryInterface $productRepository
    ) {
        $payload = json_decode($request->getContent(), true);

        $attributes = $this->attributeResolver->resolve($payload['cartItem']['product_option']);
        if (!isset($attributes['sku'])) {
            $attributes['sku'] = $payload['cartItem']['sku'];
        }
        $product = $productRepository->findOneBy($attributes);

        if (!$product instanceof PurchasableInterface) {
            return $this->json([
                'code' => 500,
            ]);
        }

        $quantity = intval($payload['cartItem']['qty'] ?? 1);

        if (!is_int($quantity)) {
            $quantity = 1;
        }

        if ($product instanceof StockableInterface) {
            $quantityToCheckStock = $quantity;

            $hasStock = $this->get('coreshop.inventory.availability_checker.default')->isStockSufficient($product,
                $quantityToCheckStock);

            if (!$hasStock) {
                return $this->json([
                    'code'   => 500,
                    'result' => $translator->trans('Out of stock.'),
                ]);
            }
        }

        $item = $this->getCartModifier()->updateItemQuantity($this->getCart(), $product, $quantity);
        $this->getCartManager()->persistCart($this->getCart());

        $this->get('coreshop.tracking.manager')->trackCartAdd($this->getCart(), $product, $quantity);

        return $this->json([
            'code'   => 200,
            'result' => $cartResponse->singleCartItemResponse($item),
        ]);
    }

    /**
     * @Route("/vsbridge/cart/delete")
     * @Method("POST")
     *
     * @param Request           $request
     *
     * @param ProductRepositoryInterface $productRepository
     *
     * @return JsonResponse
     * @todo handle cartId, I couldn't to this because VSF doesn't send it locally
     */
    public function delete(
        Request $request,
        ProductRepositoryInterface $productRepository
    ) {
        $payload = json_decode($request->getContent(), true);
        $product = $productRepository->findOneBy(['sku' => $payload['cartItem']['sku']]);

        if (!$product instanceof PurchasableInterface) {
            return $this->json([
                'code' => 500,
            ]);
        }

        $cartItem = $this->getCart()->getItemForProduct($product);

        if (!$cartItem instanceof CartItemInterface) {
            return $this->json([
                'code' => 500,
            ]);
        }

        if ($cartItem->getCart()->getId() !== $this->getCart()->getId()) {
            return $this->json([
                'code' => 500,
            ]);
        }

        $this->getCartModifier()->removeItem($this->getCart(), $cartItem);
        $this->getCartManager()->persistCart($this->getCart());

        $this->get('coreshop.tracking.manager')->trackCartRemove($this->getCart(), $cartItem->getProduct(),
            $cartItem->getQuantity());

        return $this->json([
            'code'   => 200,
            'result' => true,
        ]);
    }

    /**
     * @Route("/vsbridge/cart/shipping-methods")
     * @Method("POST")
     *
     * @param Request           $request
     * @param CarrierRepository $carrierRepository
     * @param CartResponse      $cartResponse
     *
     * @return JsonResponse
     * @todo handle different shipping methods
     */
    public function shippingMethods(
        Request $request,
        CarrierRepository $carrierRepository,
        CartResponse $cartResponse
    ) {
        $countryId = $request->get('address');
        /** @var Carrier $defaultMethod */
        $defaultMethod = $carrierRepository->findOneBy(['identifier' => 'default']);

        return $this->json([
            'status' => 200,
            'result' => $cartResponse->shippingMethodsResponse($defaultMethod),
        ]);
    }

    /**
     * @Route("/vsbridge/cart/shipping-information")
     * @Method("POST")
     *
     * @param Request                   $request
     * @param PaymentProviderRepository $paymentProviderRepository
     * @param CartResponse              $cartResponse
     *
     * @return JsonResponse
     */
    public function shippingInformation(
        Request $request,
        PaymentProviderRepository $paymentProviderRepository,
        CartResponse $cartResponse
    ) {
        /** @var PaymentProviderInterface $providers */
        $providers = $paymentProviderRepository->findActive();
        $payload   = json_decode($request->getContent(), true);

        return $this->json([
            'status' => 200,
            'result' => $cartResponse->shippingInformationResponse($this->getCart(), $providers, $payload),
        ]);

    }

    /**
     * @Route("/vsbridge/cart/payment-methods")
     * @Method("GET")
     *
     * @param PaymentProviderRepository $paymentProviderRepository
     * @param CartResponse              $cartResponse
     *
     * @return JsonResponse
     */
    public function paymentMethods(
        PaymentProviderRepository $paymentProviderRepository,
        CartResponse $cartResponse
    ) {
        /** @var PaymentProviderInterface $providers */
        $providers = $paymentProviderRepository->findActive();
        return $this->json([
            'status' => 200,
            'result' => $cartResponse->paymentMethodsResponse($providers),
        ]);

    }

    /**
     * @return StorageListModifierInterface
     */
    protected function getCartModifier()
    {
        return $this->get('coreshop.cart.modifier');
    }

    /**
     * @return CartInterface
     */
    protected function getCart()
    {
        return $this->getCartContext()->getCart();
    }

    /**
     * @return CartContextInterface
     */
    protected function getCartContext()
    {
        return $this->get('coreshop.context.cart.composite');
    }

    /**
     * @return CartManagerInterface
     */
    protected function getCartManager()
    {
        return $this->get('coreshop.cart.manager');
    }
}

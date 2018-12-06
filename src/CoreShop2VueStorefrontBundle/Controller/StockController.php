<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop2VueStorefrontBundle\Bridge\Response\ResponseBodyCreator;
use CoreShop2VueStorefrontBundle\Repository\ProductRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class StockController extends AbstractController
{
    /**
     * @Route("/vsbridge/stock/check")
     * @Method("GET")
     *
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ResponseBodyCreator $responseBodyCreator
     *
     * @return JsonResponse
     */
    public function checkStockForSku(
        Request $request,
        ProductRepository $productRepository,
        ResponseBodyCreator $responseBodyCreator
    ) {
        try {
            $product = $productRepository->findOneBySku($request->get('sku'));
            return $this->json([
                'code' => 200,
                'result' => $responseBodyCreator->stockResponse($product)
            ]);
        } catch (Exception $exception) {
            return $this->json([
                'code' => 500,
                'result' => 'Product not exists or is out of stock'
            ]);
        }
    }
}

<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\Response\ResponseBodyCreator;
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
     * @param ProductRepositoryInterface $productRepository
     * @param ResponseBodyCreator $responseBodyCreator
     *
     * @return JsonResponse
     */
    public function checkStockForSku(
        Request $request,
        ProductRepositoryInterface $productRepository,
        ResponseBodyCreator $responseBodyCreator
    ) {
        try {
            /** @var ProductInterface $product */
            $product = $productRepository->findOneBy(['sku' => $request->get('sku')]);
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

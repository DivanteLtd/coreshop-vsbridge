<?php

namespace CoreShop2VueStorefrontBundle\Controller;

use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    /**
     * @Route("/vsbridge/stock/check", methods={"GET"})
     *
     * @param Request $request
     * @param ProductRepositoryInterface $productRepository
     *
     * @return JsonResponse
     */
    public function checkStockForSku(
        Request $request,
        ProductRepositoryInterface $productRepository
    ): JsonResponse
    {
        try {
            /** @var ProductInterface $product */
            $product = $productRepository->findOneBy(['sku' => $request->get('sku')]);
            return $this->json([
                'code' => 200,
                'result' => $this->stockResponse($product)
            ]);
        } catch (Exception $exception) {
            return $this->json([
                'code' => 500,
                'result' => 'Product not exists or is out of stock'
            ]);
        }
    }

    private function stockResponse(ProductInterface $product): array
    {
        $stock = [];
        $stock['item_id'] = $product->getId();
        $stock['product_id'] = $product->getId();
        $stock['stock_id'] = 1;
        $stock['qty'] = $product->getOnHand() ?: 0;
        $stock['is_in_stock'] = $product->getOnHand() > 0 ? true : false;
        $stock['manage_stock'] = true;

        return $stock;
    }

}

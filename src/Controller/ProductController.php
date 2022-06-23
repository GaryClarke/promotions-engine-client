<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private string $promotionsEngineUrl,
        private HttpClientInterface $client
    )
    {
    }

    #[Route(path: '/products/{id}', name: 'show_product')]
    public function show($id, Request $request): Response
    {
        $params = $request->query->all();

        $product = $this->productRepository->find($id);

        $response = $this->client->request(
            'POST',
            $this->promotionsEngineUrl . '/products/' . $product->getProductId() . ' /lowest-price', [
            'json' => [
                'quantity' => $params['quantity'] ?? 1,
                'request_location' => $params['requestLocation'] ?? '',
                'voucher_code' => $params['voucherCode'] ?? '',
                'request_date' => date('Y-m-d'),
                'product_id' => $product->getProductId()
            ],
        ]);

        if($response->getStatusCode() === Response::HTTP_OK) {

            $promotionData = $response->toArray();

            return $this->render('product/show.html.twig', [
                'product' => $product,
                'promotion' => $promotionData
            ]);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'promotion' => null
        ]);
    }
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    private const PRODUCTS = [
        [
            'id' => '1',
            'name' => 'Laptop',
            'description' => 'Workstation laptop',
            'price' => 1200,
        ],
        [
            'id' => '2',
            'name' => 'Smartphone',
            'description' => 'Flagship phone',
            'price' => 800,
        ],
    ];

    /**
     * @return JsonResponse
     */
    #[Route('/products', name: 'get_products', methods: [Request::METHOD_GET])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse(['data' => self::PRODUCTS], status: Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
    */
    #[Route('/products/{id}', name: 'get_product_item', methods: [Request::METHOD_GET])]
    public function getProductItem(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(
                ['data' => ['error' => 'Not found product by id ' . $id]],
                status: Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse(['data' => $product], status: Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/products', name: 'post_products', methods: [Request::METHOD_POST])]
    public function createProduct(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $newProductData = [
            'id' => (string) random_int(3, 100),
            'name' => $requestData['name'] ?? '',
            'description' => $requestData['description'] ?? '',
            'price' => $requestData['price'] ?? 0,
        ];

        return new JsonResponse(['data' => $newProductData], status: Response::HTTP_CREATED);
    }

    
    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
    */
    #[Route('/products/{id}', name: 'put_product_item', methods: [Request::METHOD_PUT])]
    public function updateProduct(string $id, Request $request): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(
                ['data' => ['error' => 'Not found product by id ' . $id]],
                status: Response::HTTP_NOT_FOUND
            );
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $updatedProduct = [
            'id' => $id,
            'name' => $requestData['name'] ?? $product['name'],
            'description' => $requestData['description'] ?? $product['description'],
            'price' => $requestData['price'] ?? $product['price'],
        ];

        return new JsonResponse(['data' => $updatedProduct], status: Response::HTTP_OK);
    }

    
    /**
    * @param string $id
    * @return JsonResponse
    */
    #[Route('/products/{id}', name: 'delete_product_item', methods: [Request::METHOD_DELETE])]
    public function deleteProduct(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(
                ['data' => ['error' => 'Not found product by id ' . $id]],
                status: Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse(['data' => ['message' => 'Product deleted', 'id' => $id]], status: Response::HTTP_OK);
    }

    /**
     * @param array $products
     * @param string $id
     * @return array|null
     */
    private function getProductItemById(array $products, string $id): ?array
    {
        foreach ($products as $product) {
            if ($product['id'] === $id) {
                return $product;
            }
        }

        return null;
    }
}
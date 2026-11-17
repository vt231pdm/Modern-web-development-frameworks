<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
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
    public function getProducts(): JsonResponse
    {
        return response()->json(['data' => self::PRODUCTS], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getProductItem(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(
                ['data' => ['error' => 'Not found product by id ' . $id]],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json(['data' => $product], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createProduct(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $newProductData = [
            'id' => (string) random_int(3, 100),
            'name' => $requestData['name'] ?? '',
            'description' => $requestData['description'] ?? '',
            'price' => $requestData['price'] ?? 0,
        ];

        return response()->json(['data' => $newProductData], Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProduct(string $id, Request $request): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(
                ['data' => ['error' => 'Not found product by id ' . $id]],
                Response::HTTP_NOT_FOUND
            );
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $updatedProduct = [
            'id' => $id,
            'name' => $requestData['name'] ?? $product['name'],
            'description' => $requestData['description'] ?? $product['description'],
            'price' => $requestData['price'] ?? $product['price'],
        ];

        return response()->json(['data' => $updatedProduct], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteProduct(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(
                ['data' => ['error' => 'Not found product by id ' . $id]],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json(['data' => ['message' => 'Product deleted', 'id' => $id]], Response::HTTP_OK);
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
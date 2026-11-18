<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\CustomerRepository;
use App\Repository\MenuItemRepository;
use App\Repository\OrderRepository;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
final class OrderController extends AbstractController
{
    #[Route('', name: 'orders_index', methods: ['GET'])]
    public function index(Request $request, OrderRepository $repository): JsonResponse
    {
        $result = $repository->findByFilters($request->query->all());

        $data = array_map(fn(Order $o) => [
            'id' => $o->getId(),
            'customer' => $o->getCustomer()?->getFullName(),
            'tableNumber' => $o->getRestaurantTable()?->getTableNumber(),
            'menuItem' => $o->getMenuItem()?->getName(),
            'quantity' => $o->getQuantity(),
            'totalPrice' => $o->getTotalPrice(),
            'status' => $o->getStatus(),
        ], $result['items']);

        return $this->json([
            'data' => $data,
            'pagination' => $result['meta'],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'orders_show', methods: ['GET'])]
    public function show(int $id, OrderRepository $repository): JsonResponse
    {
        $order = $repository->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => [
            'id' => $order->getId(),
            'customerId' => $order->getCustomer()?->getId(),
            'tableId' => $order->getRestaurantTable()?->getId(),
            'menuItemId' => $order->getMenuItem()?->getId(),
            'quantity' => $order->getQuantity(),
            'totalPrice' => $order->getTotalPrice(),
            'status' => $order->getStatus(),
        ]], Response::HTTP_OK);
    }

    #[Route('', name: 'orders_create', methods: ['POST'])]
    public function create(
        Request $request,
        CustomerRepository $customerRepo,
        RestaurantTableRepository $tableRepo,
        MenuItemRepository $menuItemRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        $customer = $customerRepo->find($payload['customerId'] ?? 0);
        $table = $tableRepo->find($payload['tableId'] ?? 0);
        $menuItem = $menuItemRepo->find($payload['menuItemId'] ?? 0);

        if (!$customer || !$table || !$menuItem) {
            return $this->json(['error' => 'Invalid customerId, tableId or menuItemId'], Response::HTTP_BAD_REQUEST);
        }

        $quantity = (int) ($payload['quantity'] ?? 1);
        $totalPrice = $payload['totalPrice'] ?? number_format((float) $menuItem->getPrice() * $quantity, 2, '.', '');

        $order = new Order();
        $order->setCustomer($customer);
        $order->setRestaurantTable($table);
        $order->setMenuItem($menuItem);
        $order->setQuantity($quantity);
        $order->setTotalPrice((string) $totalPrice);
        $order->setStatus($payload['status'] ?? 'new');

        $em->persist($order);
        $em->flush();

        return $this->json(['data' => ['id' => $order->getId(), 'totalPrice' => $order->getTotalPrice(), 'message' => 'Order created']], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'orders_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        OrderRepository $repository,
        CustomerRepository $customerRepo,
        RestaurantTableRepository $tableRepo,
        MenuItemRepository $menuItemRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $order = $repository->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);

        if (isset($payload['customerId'])) {
            $customer = $customerRepo->find($payload['customerId']);
            if (!$customer) return $this->json(['error' => 'Customer not found'], Response::HTTP_BAD_REQUEST);
            $order->setCustomer($customer);
        }
        if (isset($payload['tableId'])) {
            $table = $tableRepo->find($payload['tableId']);
            if (!$table) return $this->json(['error' => 'Table not found'], Response::HTTP_BAD_REQUEST);
            $order->setRestaurantTable($table);
        }
        if (isset($payload['menuItemId'])) {
            $menuItem = $menuItemRepo->find($payload['menuItemId']);
            if (!$menuItem) return $this->json(['error' => 'Menu item not found'], Response::HTTP_BAD_REQUEST);
            $order->setMenuItem($menuItem);
        }
        if (isset($payload['quantity'])) $order->setQuantity((int) $payload['quantity']);
        if (isset($payload['totalPrice'])) $order->setTotalPrice((string) $payload['totalPrice']);
        if (isset($payload['status'])) $order->setStatus($payload['status']);

        $em->flush();

        return $this->json(['data' => ['id' => $order->getId(), 'message' => 'Order updated']], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'orders_delete', methods: ['DELETE'])]
    public function delete(int $id, OrderRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $order = $repository->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($order);
        $em->flush();

        return $this->json(['data' => ['message' => 'Order deleted']], Response::HTTP_OK);
    }
}
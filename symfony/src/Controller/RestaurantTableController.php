<?php

namespace App\Controller;

use App\Entity\RestaurantTable;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tables')]
final class RestaurantTableController extends AbstractController
{
    #[Route('', name: 'tables_index', methods: ['GET'])]
    public function index(RestaurantTableRepository $repository): JsonResponse
    {
        $data = array_map(fn(RestaurantTable $t) => [
            'id' => $t->getId(),
            'tableNumber' => $t->getTableNumber(),
            'seats' => $t->getSeats(),
            'zone' => $t->getZone(),
            'isAvailable' => $t->isAvailable(),
        ], $repository->findAll());

        return $this->json(['data' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'tables_show', methods: ['GET'])]
    public function show(int $id, RestaurantTableRepository $repository): JsonResponse
    {
        $table = $repository->find($id);
        if (!$table) {
            return $this->json(['error' => 'Table not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => [
            'id' => $table->getId(),
            'tableNumber' => $table->getTableNumber(),
            'seats' => $table->getSeats(),
            'zone' => $table->getZone(),
            'isAvailable' => $table->isAvailable(),
        ]], Response::HTTP_OK);
    }

    #[Route('', name: 'tables_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $table = new RestaurantTable();
        $table->setTableNumber((int) ($payload['tableNumber'] ?? 1));
        $table->setSeats((int) ($payload['seats'] ?? 2));
        $table->setZone($payload['zone'] ?? 'Main Hall');
        $table->setIsAvailable((bool) ($payload['isAvailable'] ?? true));

        $em->persist($table);
        $em->flush();

        return $this->json(['data' => ['id' => $table->getId(), 'message' => 'Table created']], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'tables_update', methods: ['PUT'])]
    public function update(int $id, Request $request, RestaurantTableRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $table = $repository->find($id);
        if (!$table) {
            return $this->json(['error' => 'Table not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (isset($payload['tableNumber'])) $table->setTableNumber((int) $payload['tableNumber']);
        if (isset($payload['seats'])) $table->setSeats((int) $payload['seats']);
        if (isset($payload['zone'])) $table->setZone($payload['zone']);
        if (isset($payload['isAvailable'])) $table->setIsAvailable((bool) $payload['isAvailable']);

        $em->flush();

        return $this->json(['data' => ['id' => $table->getId(), 'message' => 'Table updated']], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'tables_delete', methods: ['DELETE'])]
    public function delete(int $id, RestaurantTableRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $table = $repository->find($id);
        if (!$table) {
            return $this->json(['error' => 'Table not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($table);
        $em->flush();

        return $this->json(['data' => ['message' => 'Table deleted']], Response::HTTP_OK);
    }
}
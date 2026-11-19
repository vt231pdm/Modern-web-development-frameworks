<?php

namespace App\Controller;

use App\Entity\MenuItem;
use App\Repository\MenuItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/menu-items')]
final class MenuItemController extends AbstractController
{
    #[Route('', name: 'menu_items_index', methods: ['GET'])]
    public function index(Request $request, MenuItemRepository $repository): JsonResponse
    {
        $result = $repository->findByFilters($request->query->all());

        $data = array_map(fn(MenuItem $m) => [
            'id' => $m->getId(),
            'name' => $m->getName(),
            'category' => $m->getCategory(),
            'description' => $m->getDescription(),
            'price' => $m->getPrice(),
            'isActive' => $m->isActive(),
        ], $result['items']);

        return $this->json([
            'data' => $data,
            'pagination' => $result['meta'],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'menu_items_show', methods: ['GET'])]
    public function show(int $id, MenuItemRepository $repository): JsonResponse
    {
        $item = $repository->find($id);
        if (!$item) {
            return $this->json(['error' => 'Menu item not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'category' => $item->getCategory(),
            'description' => $item->getDescription(),
            'price' => $item->getPrice(),
            'isActive' => $item->isActive(),
        ]], Response::HTTP_OK);
    }

    #[Route('', name: 'menu_items_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $item = new MenuItem();
        $item->setName($payload['name'] ?? '');
        $item->setCategory($payload['category'] ?? 'General');
        $item->setDescription($payload['description'] ?? null);
        $item->setPrice((string) ($payload['price'] ?? '0.00'));
        $item->setIsActive((bool) ($payload['isActive'] ?? true));

        $em->persist($item);
        $em->flush();

        return $this->json(['data' => ['id' => $item->getId(), 'message' => 'Menu item created']], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'menu_items_update', methods: ['PUT'])]
    public function update(int $id, Request $request, MenuItemRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $item = $repository->find($id);
        if (!$item) {
            return $this->json(['error' => 'Menu item not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (isset($payload['name'])) $item->setName($payload['name']);
        if (isset($payload['category'])) $item->setCategory($payload['category']);
        if (array_key_exists('description', $payload)) $item->setDescription($payload['description']);
        if (isset($payload['price'])) $item->setPrice((string) $payload['price']);
        if (isset($payload['isActive'])) $item->setIsActive((bool) $payload['isActive']);

        $em->flush();

        return $this->json(['data' => ['id' => $item->getId(), 'message' => 'Menu item updated']], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'menu_items_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, MenuItemRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $item = $repository->find($id);
        if (!$item) {
            return $this->json(['error' => 'Menu item not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($item);
        $em->flush();

        return $this->json(['data' => ['message' => 'Menu item deleted']], Response::HTTP_OK);
    }
}
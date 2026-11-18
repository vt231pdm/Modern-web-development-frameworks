<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\CustomerRepository;
use App\Repository\ReservationRepository;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/reservations')]
final class ReservationController extends AbstractController
{
    #[Route('', name: 'reservations_index', methods: ['GET'])]
    public function index(Request $request, ReservationRepository $repository): JsonResponse
    {
        $result = $repository->findByFilters($request->query->all());

        $data = array_map(fn(Reservation $r) => [
            'id' => $r->getId(),
            'customer' => [
                'id' => $r->getCustomer()?->getId(),
                'fullName' => $r->getCustomer()?->getFullName(),
            ],
            'table' => [
                'id' => $r->getRestaurantTable()?->getId(),
                'tableNumber' => $r->getRestaurantTable()?->getTableNumber(),
            ],
            'reservedAt' => $r->getReservedAt()?->format('Y-m-d H:i:s'),
            'guestsCount' => $r->getGuestsCount(),
            'status' => $r->getStatus(),
        ], $result['items']);

        return $this->json([
            'data' => $data,
            'pagination' => $result['meta'],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'reservations_show', methods: ['GET'])]
    public function show(int $id, ReservationRepository $repository): JsonResponse
    {
        $reservation = $repository->find($id);
        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => [
            'id' => $reservation->getId(),
            'customerId' => $reservation->getCustomer()?->getId(),
            'tableId' => $reservation->getRestaurantTable()?->getId(),
            'reservedAt' => $reservation->getReservedAt()?->format('Y-m-d H:i:s'),
            'guestsCount' => $reservation->getGuestsCount(),
            'status' => $reservation->getStatus(),
        ]], Response::HTTP_OK);
    }

    #[Route('', name: 'reservations_create', methods: ['POST'])]
    public function create(
        Request $request,
        CustomerRepository $customerRepo,
        RestaurantTableRepository $tableRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        $customer = $customerRepo->find($payload['customerId'] ?? 0);
        $table = $tableRepo->find($payload['tableId'] ?? 0);

        if (!$customer || !$table) {
            return $this->json(['error' => 'Invalid customerId or tableId'], Response::HTTP_BAD_REQUEST);
        }

        $reservation = new Reservation();
        $reservation->setCustomer($customer);
        $reservation->setRestaurantTable($table);
        $reservation->setReservedAt(new \DateTimeImmutable($payload['reservedAt'] ?? 'now'));
        $reservation->setGuestsCount((int) ($payload['guestsCount'] ?? 2));
        $reservation->setStatus($payload['status'] ?? 'confirmed');

        $em->persist($reservation);
        $em->flush();

        return $this->json(['data' => ['id' => $reservation->getId(), 'message' => 'Reservation created']], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'reservations_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        ReservationRepository $repository,
        CustomerRepository $customerRepo,
        RestaurantTableRepository $tableRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $reservation = $repository->find($id);
        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);

        if (isset($payload['customerId'])) {
            $customer = $customerRepo->find($payload['customerId']);
            if (!$customer) return $this->json(['error' => 'Customer not found'], Response::HTTP_BAD_REQUEST);
            $reservation->setCustomer($customer);
        }
        if (isset($payload['tableId'])) {
            $table = $tableRepo->find($payload['tableId']);
            if (!$table) return $this->json(['error' => 'Table not found'], Response::HTTP_BAD_REQUEST);
            $reservation->setRestaurantTable($table);
        }
        if (isset($payload['reservedAt'])) $reservation->setReservedAt(new \DateTimeImmutable($payload['reservedAt']));
        if (isset($payload['guestsCount'])) $reservation->setGuestsCount((int) $payload['guestsCount']);
        if (isset($payload['status'])) $reservation->setStatus($payload['status']);

        $em->flush();

        return $this->json(['data' => ['id' => $reservation->getId(), 'message' => 'Reservation updated']], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'reservations_delete', methods: ['DELETE'])]
    public function delete(int $id, ReservationRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $reservation = $repository->find($id);
        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($reservation);
        $em->flush();

        return $this->json(['data' => ['message' => 'Reservation deleted']], Response::HTTP_OK);
    }
}
<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/customers')]
final class CustomerController extends AbstractController
{
    #[Route('', name: 'customers_index', methods: ['GET'])]
    public function index(Request $request, CustomerRepository $repository): JsonResponse
    {
        $result = $repository->findByFilters($request->query->all());

        $data = array_map(fn(Customer $c) => [
            'id' => $c->getId(),
            'fullName' => $c->getFullName(),
            'phone' => $c->getPhone(),
            'email' => $c->getEmail(),
            'createdAt' => $c->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $result['items']);

        return $this->json([
            'data' => $data,
            'pagination' => $result['meta'],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'customers_show', methods: ['GET'])]
    public function show(int $id, CustomerRepository $repository): JsonResponse
    {
        $customer = $repository->find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => [
            'id' => $customer->getId(),
            'fullName' => $customer->getFullName(),
            'phone' => $customer->getPhone(),
            'email' => $customer->getEmail(),
            'createdAt' => $customer->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]], Response::HTTP_OK);
    }

    #[Route('', name: 'customers_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $customer = new Customer();
        $customer->setFullName($payload['fullName'] ?? '');
        $customer->setPhone($payload['phone'] ?? '');
        $customer->setEmail($payload['email'] ?? null);
        $customer->setCreatedAt(new \DateTimeImmutable());

        $em->persist($customer);
        $em->flush();

        return $this->json(['data' => ['id' => $customer->getId(), 'message' => 'Customer created']], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'customers_update', methods: ['PUT'])]
    public function update(int $id, Request $request, CustomerRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $customer = $repository->find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (isset($payload['fullName'])) $customer->setFullName($payload['fullName']);
        if (isset($payload['phone'])) $customer->setPhone($payload['phone']);
        if (array_key_exists('email', $payload)) $customer->setEmail($payload['email']);

        $em->flush();

        return $this->json(['data' => ['id' => $customer->getId(), 'message' => 'Customer updated']], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'customers_delete', methods: ['DELETE'])]
    public function delete(int $id, CustomerRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $customer = $repository->find($id);
        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($customer);
        $em->flush();

        return $this->json(['data' => ['message' => 'Customer deleted']], Response::HTTP_OK);
    }
}
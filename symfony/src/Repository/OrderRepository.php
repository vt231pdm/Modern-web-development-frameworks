<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param array $filters
     * @return array
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('o');

        if (!empty($filters['id'])) {
            $qb->andWhere('o.id = :id')
            ->setParameter('id', (int) $filters['id']);
        }

        if (!empty($filters['customerId'])) {
            $qb->andWhere('o.customer = :customerId')
            ->setParameter('customerId', (int) $filters['customerId']);
        }

        if (!empty($filters['tableId'])) {
            $qb->andWhere('o.restaurantTable = :tableId')
            ->setParameter('tableId', (int) $filters['tableId']);
        }

        if (!empty($filters['menuItemId'])) {
            $qb->andWhere('o.menuItem = :menuItemId')
            ->setParameter('menuItemId', (int) $filters['menuItemId']);
        }

        if (!empty($filters['quantity'])) {
            $qb->andWhere('o.quantity = :quantity')
            ->setParameter('quantity', (int) $filters['quantity']);
        }

        if (!empty($filters['totalPrice'])) {
            $qb->andWhere('o.totalPrice = :totalPrice')
            ->setParameter('totalPrice', $filters['totalPrice']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('o.status = :status')
            ->setParameter('status', $filters['status']);
        }

        return $this->paginate($qb, $filters);
    }
}

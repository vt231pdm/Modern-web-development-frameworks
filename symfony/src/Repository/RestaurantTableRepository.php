<?php

namespace App\Repository;

use App\Entity\RestaurantTable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RestaurantTable>
 */
class RestaurantTableRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantTable::class);
    }

    /**
     * @param array $filters
     * @return array
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('t');

        if (!empty($filters['id'])) {
            $qb->andWhere('t.id = :id')
            ->setParameter('id', (int) $filters['id']);
        }

        if (!empty($filters['tableNumber'])) {
            $qb->andWhere('t.tableNumber = :tableNumber')
            ->setParameter('tableNumber', (int) $filters['tableNumber']);
        }

        if (!empty($filters['seats'])) {
            $qb->andWhere('t.seats = :seats')
            ->setParameter('seats', (int) $filters['seats']);
        }

        if (!empty($filters['zone'])) {
            $qb->andWhere('t.zone LIKE :zone')
            ->setParameter('zone', '%' . $filters['zone'] . '%');
        }

        if (isset($filters['isAvailable']) && $filters['isAvailable'] !== '') {
            $isAvailable = filter_var($filters['isAvailable'], FILTER_VALIDATE_BOOLEAN);
            $qb->andWhere('t.isAvailable = :isAvailable')
            ->setParameter('isAvailable', $isAvailable);
        }

        return $this->paginate($qb, $filters);
    }
}

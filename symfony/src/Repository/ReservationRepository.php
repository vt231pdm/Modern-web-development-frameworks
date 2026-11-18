<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * @param array $filters
     * @return array
     */
        public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('r');

        if (!empty($filters['id'])) {
            $qb->andWhere('r.id = :id')
            ->setParameter('id', (int) $filters['id']);
        }

        if (!empty($filters['customerId'])) {
            $qb->andWhere('r.customer = :customerId')
            ->setParameter('customerId', (int) $filters['customerId']);
        }

        if (!empty($filters['tableId'])) {
            $qb->andWhere('r.restaurantTable = :tableId')
            ->setParameter('tableId', (int) $filters['tableId']);
        }

        if (!empty($filters['reservedAt'])) {
            $qb->andWhere('r.reservedAt LIKE :reservedAt')
            ->setParameter('reservedAt', $filters['reservedAt'] . '%');
        }

        if (!empty($filters['guestsCount'])) {
            $qb->andWhere('r.guestsCount = :guestsCount')
            ->setParameter('guestsCount', (int) $filters['guestsCount']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('r.status = :status')
            ->setParameter('status', $filters['status']);
        }

        return $this->paginate($qb, $filters);
    }
}

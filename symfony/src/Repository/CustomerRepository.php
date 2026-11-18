<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @param array $filters
     * @return array
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($filters['id'])) {
            $qb->andWhere('c.id = :id')
            ->setParameter('id', (int) $filters['id']);
        }

        if (!empty($filters['fullName'])) {
            $qb->andWhere('c.fullName LIKE :fullName')
            ->setParameter('fullName', '%' . $filters['fullName'] . '%');
        }

        if (!empty($filters['phone'])) {
            $qb->andWhere('c.phone LIKE :phone')
            ->setParameter('phone', '%' . $filters['phone'] . '%');
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('c.email LIKE :email')
            ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['createdAt'])) {
            $qb->andWhere('c.createdAt LIKE :createdAt')
            ->setParameter('createdAt', $filters['createdAt'] . '%');
        }

        return $this->paginate($qb, $filters);
    }
}

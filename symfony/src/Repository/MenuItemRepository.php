<?php

namespace App\Repository;

use App\Entity\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuItem>
 */
class MenuItemRepository extends ServiceEntityRepository
{
    use PaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    /**
     * @param array $filters
     * @return array
     */
        public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('m');

        if (!empty($filters['id'])) {
            $qb->andWhere('m.id = :id')
            ->setParameter('id', (int) $filters['id']);
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('m.name LIKE :name')
            ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('m.category LIKE :category')
            ->setParameter('category', '%' . $filters['category'] . '%');
        }

        if (!empty($filters['description'])) {
            $qb->andWhere('m.description LIKE :description')
            ->setParameter('description', '%' . $filters['description'] . '%');
        }

        if (!empty($filters['price'])) {
            $qb->andWhere('m.price = :price')
            ->setParameter('price', $filters['price']);
        }

        if (isset($filters['isActive']) && $filters['isActive'] !== '') {
            $isActive = filter_var($filters['isActive'], FILTER_VALIDATE_BOOLEAN);
            $qb->andWhere('m.isActive = :isActive')
            ->setParameter('isActive', $isActive);
        }

        return $this->paginate($qb, $filters);
    }
}

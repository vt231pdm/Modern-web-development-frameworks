<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait PaginationTrait
{
    protected function paginate(QueryBuilder $qb, array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $itemsPerPage = max(1, min(100, (int) ($filters['itemsPerPage'] ?? 10)));

        $qb->setFirstResult(($page - 1) * $itemsPerPage)
           ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($qb->getQuery(), fetchJoinCollection: true);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $itemsPerPage);

        return [
            'items' => iterator_to_array($paginator),
            'meta' => [
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
            ],
        ];
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected function paginatedResponse(Builder $query, Request $request): JsonResponse
    {
        $itemsPerPage = max(1, min(100, (int) $request->query('itemsPerPage', 10)));

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($itemsPerPage)->withQueryString();

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'itemsPerPage' => $paginator->perPage(),
                'totalItems' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ], Response::HTTP_OK);
    }
}
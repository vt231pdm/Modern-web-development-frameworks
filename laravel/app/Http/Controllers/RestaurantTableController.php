<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantTableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RestaurantTable::filter($request->query());

        return $this->paginatedResponse($query, $request);
    }

    public function show(string $id): JsonResponse
    {
        $table = RestaurantTable::find($id);
        if (!$table) {
            return response()->json(['error' => 'Table not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $table], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => 'required|integer|unique:restaurant_tables,table_number',
            'seats' => 'required|integer|min:1',
            'zone' => 'required|string|max:100',
            'is_available' => 'boolean',
        ]);

        $table = RestaurantTable::create($validated);

        return response()->json(['data' => $table], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $table = RestaurantTable::find($id);
        if (!$table) {
            return response()->json(['error' => 'Table not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'table_number' => 'sometimes|integer|unique:restaurant_tables,table_number,' . $table->id,
            'seats' => 'sometimes|integer|min:1',
            'zone' => 'sometimes|string|max:100',
            'is_available' => 'sometimes|boolean',
        ]);

        $table->update($validated);

        return response()->json(['data' => $table], Response::HTTP_OK);
    }

    public function destroy(string $id): JsonResponse
    {
        $table = RestaurantTable::find($id);
        if (!$table) {
            return response()->json(['error' => 'Table not found'], Response::HTTP_NOT_FOUND);
        }

        $table->delete();

        return response()->json(['data' => ['message' => 'Table deleted']], Response::HTTP_OK);
    }
}
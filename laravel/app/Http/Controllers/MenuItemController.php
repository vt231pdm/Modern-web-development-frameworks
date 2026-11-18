<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => MenuItem::all()], Response::HTTP_OK);
    }

    public function show(string $id): JsonResponse
    {
        $item = MenuItem::find($id);
        if (!$item) {
            return response()->json(['error' => 'Menu item not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $item], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $item = MenuItem::create($validated);

        return response()->json(['data' => $item], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $item = MenuItem::find($id);
        if (!$item) {
            return response()->json(['error' => 'Menu item not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $item->update($validated);

        return response()->json(['data' => $item], Response::HTTP_OK);
    }

    public function destroy(string $id): JsonResponse
    {
        $item = MenuItem::find($id);
        if (!$item) {
            return response()->json(['error' => 'Menu item not found'], Response::HTTP_NOT_FOUND);
        }

        $item->delete();

        return response()->json(['data' => ['message' => 'Menu item deleted']], Response::HTTP_OK);
    }
}
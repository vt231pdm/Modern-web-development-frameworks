<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['customer', 'table', 'menuItem'])->get();

        return response()->json(['data' => $orders], Response::HTTP_OK);
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::with(['customer', 'table', 'menuItem'])->find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $order], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:restaurant_tables,id',
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'nullable|numeric|min:0',
            'status' => 'sometimes|string|max:50',
        ]);

        if (!isset($validated['total_price'])) {
            $menuItem = MenuItem::findOrFail($validated['menu_item_id']);
            $validated['total_price'] = $menuItem->price * $validated['quantity'];
        }

        $order = Order::create($validated);

        return response()->json(['data' => $order->load(['customer', 'table', 'menuItem'])], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'customer_id' => 'sometimes|exists:customers,id',
            'table_id' => 'sometimes|exists:restaurant_tables,id',
            'menu_item_id' => 'sometimes|exists:menu_items,id',
            'quantity' => 'sometimes|integer|min:1',
            'total_price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|max:50',
        ]);

        $order->update($validated);

        return response()->json(['data' => $order->load(['customer', 'table', 'menuItem'])], Response::HTTP_OK);
    }

    public function destroy(string $id): JsonResponse
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $order->delete();

        return response()->json(['data' => ['message' => 'Order deleted']], Response::HTTP_OK);
    }
}
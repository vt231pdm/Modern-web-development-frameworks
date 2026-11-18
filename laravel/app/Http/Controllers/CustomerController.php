<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::filter($request->query());

        return $this->paginatedResponse($query, $request);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $customer], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:180',
        ]);

        $customer = Customer::create($validated);

        return response()->json(['data' => $customer], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:30',
            'email' => 'nullable|email|max:180',
        ]);

        $customer->update($validated);

        return response()->json(['data' => $customer], Response::HTTP_OK);
    }

    public function destroy(string $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $customer->delete();

        return response()->json(['data' => ['message' => 'Customer deleted']], Response::HTTP_OK);
    }
}
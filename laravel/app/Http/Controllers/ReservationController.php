<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Reservation::with(['customer', 'table'])
            ->filter($request->query());

        return $this->paginatedResponse($query, $request);
    }

    public function show(string $id): JsonResponse
    {
        $reservation = Reservation::with(['customer', 'table'])->find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $reservation], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'table_id' => 'required|exists:restaurant_tables,id',
            'reserved_at' => 'required|date',
            'guests_count' => 'required|integer|min:1',
            'status' => 'sometimes|string|max:50',
        ]);

        $reservation = Reservation::create($validated);

        return response()->json(['data' => $reservation->load(['customer', 'table'])], Response::HTTP_CREATED);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'customer_id' => 'sometimes|exists:customers,id',
            'table_id' => 'sometimes|exists:restaurant_tables,id',
            'reserved_at' => 'sometimes|date',
            'guests_count' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|max:50',
        ]);

        $reservation->update($validated);

        return response()->json(['data' => $reservation->load(['customer', 'table'])], Response::HTTP_OK);
    }

    public function destroy(string $id): JsonResponse
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $reservation->delete();

        return response()->json(['data' => ['message' => 'Reservation deleted']], Response::HTTP_OK);
    }
}
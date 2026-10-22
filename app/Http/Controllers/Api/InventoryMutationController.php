<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryMutationRequest;
use App\Models\InventoryMutation;
use App\Services\InventoryMutationService;
use Illuminate\Http\Request;

class InventoryMutationController extends Controller
{
    public function __construct(
        private InventoryMutationService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'inventory_item_id' => ['sometimes', 'integer', 'exists:inventory_items,id'],
            'type' => ['sometimes', 'in:in,out,adjustment'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        // Per-page dapat disesuaikan melalui query parameter.
        $filters['per_page'] = $filters['per_page'] ?? 15;

        return response()->json([
            'success' => true,
            'message' => 'Riwayat mutasi stok berhasil diambil.',
            'data' => $this->service->getAll($filters),
        ]);
    }

    public function store(StoreInventoryMutationRequest $request)
    {
        $mutation = $this->service->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Mutasi stok berhasil dicatat.',
            'data' => $mutation,
        ], 201);
    }

    public function show(InventoryMutation $inventoryMutation)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail mutasi stok berhasil diambil.',
            'data' => $this->service->findById(
                $inventoryMutation->id
            ),
        ]);
    }
}

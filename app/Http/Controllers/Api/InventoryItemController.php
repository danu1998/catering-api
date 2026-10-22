<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use App\Services\InventoryItemService;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct(
        private InventoryItemService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $items = $this->inventoryService->getAll(
            $request->only([
                'search',
                'category',
                'is_active',
                'low_stock',
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Data stok berhasil diambil.',
            'data' => $items,
        ]);
    }

    public function store(StoreInventoryItemRequest $request)
    {
        $item = $this->inventoryService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Bahan berhasil ditambahkan.',
            'data' => $item,
        ], 201);
    }

    public function show(InventoryItem $inventoryItem)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail bahan berhasil diambil.',
            'data' => $inventoryItem,
        ]);
    }

    public function update(
        UpdateInventoryItemRequest $request,
        InventoryItem $inventoryItem
    ) {
        $item = $this->inventoryService->update(
            $inventoryItem,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data bahan berhasil diperbarui.',
            'data' => $item,
        ]);
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $this->inventoryService->delete($inventoryItem);

        return response()->json([
            'success' => true,
            'message' => 'Bahan berhasil dihapus.',
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockOpnameRequest;
use App\Http\Requests\UpdateStockOpnameItemRequest;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function __construct(
        private StockOpnameService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => [
                'sometimes',
                'in:draft,completed,cancelled',
            ],
            'date_from' => ['sometimes', 'date'],
            'date_to' => [
                'sometimes',
                'date',
                'after_or_equal:date_from',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar Stock Opname berhasil diambil.',
            'data' => $this->service->getAll($filters),
        ]);
    }

    public function store(StoreStockOpnameRequest $request)
    {
        $opname = $this->service->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Sesi Stock Opname berhasil dibuat.',
            'data' => $opname,
        ], 201);
    }

    public function show(StockOpname $stockOpname)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail Stock Opname berhasil diambil.',
            'data' => $this->service->findById(
                $stockOpname->id
            ),
        ]);
    }

    public function updateItem(
        UpdateStockOpnameItemRequest $request,
        StockOpname $stockOpname,
        StockOpnameItem $stockOpnameItem
    ) {
        $item = $this->service->updateItem(
            $stockOpname,
            $stockOpnameItem,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Hasil hitung stok berhasil disimpan.',
            'data' => $item,
        ]);
    }

    public function complete(
        Request $request,
        StockOpname $stockOpname
    ) {
        $opname = $this->service->complete(
            $stockOpname->id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock Opname berhasil difinalisasi.',
            'data' => $opname,
        ]);
    }

    public function cancel(StockOpname $stockOpname)
    {
        $opname = $this->service->cancel($stockOpname);

        return response()->json([
            'success' => true,
            'message' => 'Sesi Stock Opname berhasil dibatalkan.',
            'data' => $opname,
        ]);
    }
}
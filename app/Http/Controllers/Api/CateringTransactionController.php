<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCateringTransactionRequest;
use App\Http\Requests\UpdateCateringTransactionRequest;
use App\Http\Requests\UpdateTransactionStatusRequest;
use App\Models\CateringTransaction;
use App\Services\CateringTransactionService;
use Illuminate\Http\Request;

class CateringTransactionController extends Controller
{
    public function __construct(
        private CateringTransactionService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'string', 'max:150'],

            'order_status' => [
                'sometimes',
                'in:pending,confirmed,processing,ready,delivered,cancelled',
            ],

            'payment_status' => [
                'sometimes',
                'in:unpaid,dp_paid,paid,refunded',
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
            'message' => 'Daftar transaksi berhasil diambil.',
            'data' => $this->service->getAll($filters),
        ]);
    }

    public function store(StoreCateringTransactionRequest $request)
    {
        $transaction = $this->service->create(
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaksi catering berhasil dibuat.',
            'data' => $transaction,
        ], 201);
    }

    public function show(CateringTransaction $cateringTransaction)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi berhasil diambil.',
            'data' => $this->service->findById(
                $cateringTransaction->id
            ),
        ]);
    }

    public function update(
        UpdateCateringTransactionRequest $request,
        CateringTransaction $cateringTransaction
    ) {
        $transaction = $this->service->update(
            $cateringTransaction,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => $transaction,
        ]);
    }


    public function updateStatus(
        UpdateTransactionStatusRequest $request,
        CateringTransaction $cateringTransaction
    ) {
        $transaction = $this->service->updateStatus(
            $cateringTransaction,
            $request->validated()['status'],
            $request->validated()['notes'] ?? null,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => $transaction,
        ]);
    }
}

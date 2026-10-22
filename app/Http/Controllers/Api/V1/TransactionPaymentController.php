<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionPaymentRequest;
use App\Http\Requests\UpdateTransactionPaymentStatusRequest;
use App\Models\CateringTransactionPayment;
use App\Services\TransactionPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionPaymentController extends Controller
{
    public function __construct(
        private TransactionPaymentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil diambil.',
            'data' => $this->service->paginate(
                $request->only([
                    'catering_transaction_id',
                    'payment_status',
                    'per_page',
                ])
            ),
        ]);
    }

    public function store(
        StoreTransactionPaymentRequest $request
    ): JsonResponse {
        $payment = $this->service->create(
            $request->validated(),
            $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat dan menunggu verifikasi.',
            'data' => $payment,
        ], 201);
    }

    public function show(
        CateringTransactionPayment $transactionPayment
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $this->service->findById(
                $transactionPayment->id
            ),
        ]);
    }

    public function updateStatus(
        UpdateTransactionPaymentStatusRequest $request,
        CateringTransactionPayment $transactionPayment
    ): JsonResponse {
        $payment = $this->service->updateStatus(
            $transactionPayment,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran berhasil diperbarui.',
            'data' => $payment,
        ]);
    }
}

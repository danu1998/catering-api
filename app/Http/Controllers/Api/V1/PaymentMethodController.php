<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function __construct(
        private PaymentMethodService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data metode pembayaran berhasil diambil.',
            'data' => $this->service->paginate(
                $request->only([
                    'search',
                    'is_active',
                    'per_page',
                ])
            ),
        ]);
    }

    public function store(
        StorePaymentMethodRequest $request
    ): JsonResponse {
        $paymentMethod = $this->service->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil dibuat.',
            'data' => $paymentMethod,
        ], 201);
    }

    public function show(
        PaymentMethod $paymentMethod
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $paymentMethod,
        ]);
    }

    public function update(
        UpdatePaymentMethodRequest $request,
        PaymentMethod $paymentMethod
    ): JsonResponse {
        $updated = $this->service->update(
            $paymentMethod,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran berhasil diperbarui.',
            'data' => $updated,
        ]);
    }
}

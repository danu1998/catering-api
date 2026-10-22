<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diambil.',
            'data' => $this->service->paginate(
                $request->only([
                    'search',
                    'customer_type',
                    'is_active',
                    'per_page',
                ])
            ),
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil dibuat.',
            'data' => $customer,
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $customer,
        ]);
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer
    ): JsonResponse {
        $updated = $this->service->update(
            $customer,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil diperbarui.',
            'data' => $updated,
        ]);
    }

    
public function transactions(
    \Illuminate\Http\Request $request,
    \App\Models\Customer $customer
) {
    $transactions = $this->service->getTransactions(
        $customer,
        $request->only([
            'search',
            'order_status',
            'payment_status',
            'date_from',
            'date_to',
            'per_page',
        ])
    );

    return response()->json([
        'success' => true,
        'message' => 'Riwayat transaksi pelanggan berhasil diambil.',
        'data' => $transactions,
    ]);
}

    
}
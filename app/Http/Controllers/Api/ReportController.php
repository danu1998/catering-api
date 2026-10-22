<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $service
    ) {}

    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'date_from' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],
            'date_to' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],
            'order_status' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],
            'payment_status' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],
            'order_source' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],
            'expense_category_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:expense_categories,id',
            ],
            'payment_method_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:payment_methods,id',
            ],
            'rating' => [
                'sometimes',
                'nullable',
                'integer',
                'between:1,5',
            ],
            'is_visible' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        if (
            !empty($filters['date_from'])
            && !empty($filters['date_to'])
            && $filters['date_from'] > $filters['date_to']
        ) {
            throw ValidationException::withMessages([
                'date_from' => [
                    'Tanggal mulai tidak boleh melebihi tanggal akhir.',
                ],
            ]);
        }

        return $filters;
    }

    public function summary(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan laporan berhasil diambil.',
            'data' => $this->service->getSummary($filters),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'success' => true,
            'message' => 'Laporan transaksi berhasil diambil.',
            'data' => $this->service->getTransactions($filters),
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'success' => true,
            'message' => 'Laporan pembayaran berhasil diambil.',
            'data' => $this->service->getPaymentSummary($filters),
        ]);
    }

    public function expenses(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'success' => true,
            'message' => 'Laporan pengeluaran berhasil diambil.',
            'data' => $this->service->getExpenses($filters),
            'by_category' => $this->service->getExpensesByCategory($filters),
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        $filters = $this->filters($request);

        return response()->json([
            'success' => true,
            'message' => 'Laporan review berhasil diambil.',
            'data' => $this->service->getReviews($filters),
        ]);
    }
}
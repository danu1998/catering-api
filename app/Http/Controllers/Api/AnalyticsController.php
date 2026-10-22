<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $service
    ) {}

    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'date_from' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'date_to' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],
        ]);

        if (
            isset($filters['date_from'], $filters['date_to'])
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

    public function dashboard(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => $this->service->getDashboard($filters),
        ]);
    }

    public function dailyChart(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'success' => true,
            'message' => 'Data grafik harian berhasil diambil.',
            'data' => $this->service->getDailyChart($filters),
        ]);
    }

    public function transactionStatuses(
        Request $request
    ): JsonResponse {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'success' => true,
            'message' => 'Distribusi status berhasil diambil.',
            'data' => $this->service->getTransactionStatuses($filters),
        ]);
    }
}

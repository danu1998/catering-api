<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLandingPageRequest;
use App\Models\Category;
use App\Services\CateringTransactionService;
use App\Services\LandingPageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LandingPageController extends Controller
{
    public function __construct(
        private LandingPageService $landingPageService,
        private CateringTransactionService $transactionService
    ) {}

    /**
     * GET /api/v1/public/home
     */
    public function home()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data landing page berhasil diambil.',
            'data' => $this->landingPageService->getHome(),
        ]);
    }

    /**
     * GET /api/v1/public/menus
     */
    public function menus(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'category_slug' => ['nullable', 'string', 'max:150'],
            'sort' => [
                'nullable',
                'in:latest,price_low,price_high,name',
            ],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->landingPageService->getMenus($filters),
        ]);
    }

    /**
     * GET /api/v1/public/menus/{slug}
     */
    public function menuDetail(string $slug)
    {
        return response()->json([
            'success' => true,
            'data' => $this->landingPageService
                ->getMenuDetail($slug),
        ]);
    }

    /**
     * GET /api/v1/public/categories
     */
    public function categories()
    {
        return response()->json([
            'success' => true,
            'data' => $this->landingPageService->getCategories(),
        ]);
    }

    /**
     * GET /api/v1/public/schedules?date=YYYY-MM-DD
     */
    public function schedules(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        return response()->json([
            'success' => true,
            'date' => $date,
            'data' => $this->landingPageService
                ->getSchedules($date),
        ]);
    }

    /**
     * GET /api/v1/public/reviews
     */
    public function reviews(Request $request)
    {
        $filters = $request->validate([
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->landingPageService->getReviews($filters),
        ]);
    }

    /**
     * GET /api/v1/public/reviews/summary
     */
    public function reviewSummary()
    {
        return response()->json([
            'success' => true,
            'data' => $this->landingPageService->getReviewSummary(),
        ]);
    }

    /**
     * POST /api/v1/public/requests
     *
     * Menerima pesanan dari website dan menggunakan
     * service transaksi yang sudah tersedia.
     */
    public function storeRequest(StoreLandingPageRequest $request)
    {
        try {
            $data = $request->validated();

            // Website menjadi sumber pesanan.
            $data['order_source'] = 'website';

            $transaction = $this->transactionService->create(
                $data,
                null
            );

            return response()->json([
                'success' => true,
                'message' =>
                'Permintaan catering berhasil dikirim.',
                'data' => [
                    'transaction_number' =>
                    $transaction->transaction_number,
                    'customer_name' =>
                    $transaction->customer_name,
                    'delivery_date' =>
                    $transaction->delivery_date,
                    'total_pax' =>
                    $transaction->total_pax,
                    'total_amount' =>
                    $transaction->total_amount,
                    'order_status' =>
                    $transaction->order_status,
                    'payment_status' =>
                    $transaction->payment_status,
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('Landing page order request failed.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' =>
                'Permintaan belum dapat diproses. Silakan periksa data pesanan.',
            ], 422);
        }
    }
}

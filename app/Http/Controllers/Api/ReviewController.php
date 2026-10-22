<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'rating',
            'is_visible',
            'date_from',
            'date_to',
            'per_page',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar review berhasil diambil.',
            'data' => $this->service->getAll($filters),
            'summary' => $this->service->getSummary($filters),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $filters = $request->only([
            'rating',
            'is_visible',
            'date_from',
            'date_to',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan review berhasil diambil.',
            'data' => $this->service->getSummary($filters),
        ]);
    }

    public function store(
        StoreReviewRequest $request
    ): JsonResponse {
        $review = $this->service->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil ditambahkan.',
            'data' => $review->load([
                'customer',
                'transaction',
            ]),
        ], 201);
    }

    public function show(Review $review): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail review berhasil diambil.',
            'data' => $this->service->findById($review->id),
        ]);
    }

    public function update(
        UpdateReviewRequest $request,
        Review $review
    ): JsonResponse {
        $updatedReview = $this->service->update(
            $review,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil diperbarui.',
            'data' => $updatedReview,
        ]);
    }

    public function destroy(Review $review): JsonResponse
    {
        $this->service->delete($review);

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil dihapus.',
        ]);
    }
}

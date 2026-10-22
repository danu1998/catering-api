<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Daftar review dengan filter dan pagination.
     */
    public function getAll(
        array $filters = []
    ): LengthAwarePaginator {
        $query = Review::query()
            ->with([
                'customer:id,name',
                'transaction',
            ]);

        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(
                max(1, min((int) ($filters['per_page'] ?? 15), 100))
            )
            ->withQueryString();
    }

    /**
     * Ringkasan rating dan distribusi bintang.
     */
    public function getSummary(array $filters = []): array
    {
        $query = Review::query();

        $this->applyFilters($query, $filters);

        $totalReviews = (clone $query)->count();

        $averageRating = (clone $query)->avg('rating');

        $distribution = [];

        for ($rating = 1; $rating <= 5; $rating++) {
            $distribution[$rating] = (clone $query)
                ->where('rating', $rating)
                ->count();
        }

        return [
            'total_reviews' => $totalReviews,
            'average_rating' => $averageRating !== null
                ? round((float) $averageRating, 2)
                : 0,
            'distribution' => $distribution,
        ];
    }

    /**
     * Membuat review baru.
     */
    public function create(array $data): Review
    {
        return DB::transaction(function () use ($data) {
            return Review::create($data);
        });
    }

    /**
     * Mengambil detail review.
     */
    public function findById(int $id): Review
    {
        return Review::with([
            'customer',
            'transaction',
        ])->findOrFail($id);
    }

    /**
     * Memperbarui review.
     */
    public function update(
        Review $review,
        array $data
    ): Review {
        return DB::transaction(function () use ($review, $data) {
            $review->update($data);

            return $review->fresh([
                'customer',
                'transaction',
            ]);
        });
    }

    /**
     * Menghapus review.
     */
    public function delete(Review $review): void
    {
        DB::transaction(function () use ($review) {
            $review->delete();
        });
    }

    /**
     * Menerapkan filter yang digunakan oleh daftar
     * dan ringkasan review.
     */
    private function applyFilters(
        Builder $query,
        array $filters
    ): Builder {
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if (
            isset($filters['rating'])
            && $filters['rating'] !== ''
        ) {
            $query->where(
                'rating',
                (int) $filters['rating']
            );
        }

        if (
            array_key_exists('is_visible', $filters)
            && $filters['is_visible'] !== ''
            && $filters['is_visible'] !== null
        ) {
            $query->where(
                'is_visible',
                (int) $filters['is_visible']
            );
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }

        return $query;
    }
}
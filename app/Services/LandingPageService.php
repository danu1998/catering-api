<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuSchedule;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingPageService
{
    /**
     * Data utama landing page.
     */
    public function getHome(): array
    {
        $menus = Menu::query()
            ->where('is_active', true)
            ->with('category:id,name,slug')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        return [
            'new_menu' => (clone $menus)
                ->latest('created_at')
                ->limit(8)
                ->get(),

            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'reviews' => Review::query()
                ->where('is_visible', true)
                ->latest()
                ->limit(6)
                ->get(),

            'review_summary' => [
                'average_rating' => round(
                    (float) Review::query()
                        ->where('is_visible', true)
                        ->avg('rating'),
                    1
                ),
                'total_reviews' => Review::query()
                    ->where('is_visible', true)
                    ->count(),
            ],
        ];
    }

    /**
     * Daftar menu publik dengan filter.
     */
    public function getMenus(array $filters = [])
    {
        $query = Menu::query()
            ->where('is_active', true)
            ->with('category:id,name,slug');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where(
                'category_id',
                $filters['category_id']
            );
        }

        if (!empty($filters['category_slug'])) {
            $query->whereHas(
                'category',
                function ($q) use ($filters) {
                    $q->where(
                        'slug',
                        $filters['category_slug']
                    )->where('is_active', true);
                }
            );
        }

        $sort = $filters['sort'] ?? 'latest';

        switch ($sort) {
            case 'price_low':
                $query->orderBy('price');
                break;

            case 'price_high':
                $query->orderByDesc('price');
                break;

            case 'name':
                $query->orderBy('name');
                break;

            default:
                $query->orderBy('sort_order')
                    ->orderByDesc('id');
                break;
        }

        $perPage = min(
            max((int) ($filters['per_page'] ?? 12), 1),
            100
        );

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Detail satu menu.
     */
    public function getMenuDetail(string $slug): Menu
    {
        return Menu::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category',
                'variants' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->firstOrFail();
    }

    /**
     * Kategori yang ditampilkan kepada pengunjung.
     */
    public function getCategories()
    {
        return Category::query()
            ->where('is_active', true)
            ->withCount([
                'menus' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Jadwal menu berdasarkan tanggal.
     *
     * Catatan:
     * Sesuaikan schedule_date jika nama kolom tanggal
     * di migration MenuSchedule berbeda.
     */
    public function getSchedules(string $date)
    {
        return MenuSchedule::query()
            ->whereDate('schedule_date', $date)
            ->with([
                'menu' => function ($query) {
                    $query->where('is_active', true)
                        ->with('category:id,name,slug');
                },
            ])
            ->whereHas('menu', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('schedule_date')
            ->get();
    }

    /**
     * Ulasan publik.
     */
    public function getReviews(array $filters = [])
    {
        $query = Review::query()
            ->where('is_visible', true);

        if (!empty($filters['rating'])) {
            $query->where(
                'rating',
                (int) $filters['rating']
            );
        }

        $perPage = min(
            max((int) ($filters['per_page'] ?? 10), 1),
            50
        );

        return $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ringkasan ulasan publik.
     */
    public function getReviewSummary(): array
    {
        $query = Review::query()
            ->where('is_visible', true);

        return [
            'average_rating' => round(
                (float) (clone $query)->avg('rating'),
                1
            ),
            'total_reviews' => (clone $query)->count(),
            'rating_distribution' => (clone $query)
                ->select('rating', DB::raw('COUNT(*) as total'))
                ->groupBy('rating')
                ->orderByDesc('rating')
                ->get(),
        ];
    }
}
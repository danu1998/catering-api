<?php

namespace App\Services;

use App\Models\CateringTransaction;
use App\Models\Expense;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private function applyDateFilter(
        Builder $query,
        array $filters,
        string $column = 'created_at'
    ): Builder {
        if (!empty($filters['date_from'])) {
            $query->whereDate(
                $column,
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                $column,
                '<=',
                $filters['date_to']
            );
        }

        return $query;
    }

    /**
     * Ringkasan seluruh laporan.
     */
    public function getSummary(array $filters = []): array
    {
        $transactions = CateringTransaction::query();
        $this->applyDateFilter($transactions, $filters);

        $expenses = Expense::query();
        $this->applyDateFilter(
            $expenses,
            $filters,
            'expense_date'
        );

        $reviews = Review::query();
        $this->applyDateFilter($reviews, $filters);

        $transactionCount = (clone $transactions)->count();
        $transactionValue = (float) (clone $transactions)
            ->sum('total_amount');

        $expenseCount = (clone $expenses)->count();
        $expenseValue = (float) (clone $expenses)->sum('amount');

        return [
            'transactions' => [
                'total_count' => $transactionCount,
                'total_value' => round($transactionValue, 2),
                'average_value' => $transactionCount > 0
                    ? round(
                        $transactionValue / $transactionCount,
                        2
                    )
                    : 0,
            ],
            'expenses' => [
                'total_count' => $expenseCount,
                'total_amount' => round($expenseValue, 2),
            ],
            'reviews' => [
                'total_count' => (clone $reviews)->count(),
                'visible_count' => (clone $reviews)
                    ->where('is_visible', true)
                    ->count(),
                'average_rating' => round(
                    (float) ((clone $reviews)->avg('rating') ?? 0),
                    2
                ),
            ],
            'financial_overview' => [
                'transaction_value' => round($transactionValue, 2),
                'expense_value' => round($expenseValue, 2),
                'difference' => round(
                    $transactionValue - $expenseValue,
                    2
                ),
                'note' => 'Selisih nilai transaksi dan pengeluaran, bukan laba bersih atau penerimaan kas.',
            ],
        ];
    }

    /**
     * Detail transaksi dengan filter dan pagination.
     */
    public function getTransactions(
        array $filters = []
    ): LengthAwarePaginator {
        $query = CateringTransaction::query()
            ->with(['customer', 'creator']);

        $this->applyDateFilter($query, $filters);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['order_status'])) {
            $query->where(
                'order_status',
                $filters['order_status']
            );
        }

        if (!empty($filters['payment_status'])) {
            $query->where(
                'payment_status',
                $filters['payment_status']
            );
        }

        if (!empty($filters['order_source'])) {
            $query->where(
                'order_source',
                $filters['order_source']
            );
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    /**
     * Rekap nilai transaksi berdasarkan status pembayaran.
     * Nilai di sini bukan bukti uang sudah diterima.
     */
    public function getPaymentSummary(
        array $filters = []
    ): array {
        $query = CateringTransaction::query();
        $this->applyDateFilter($query, $filters);

        return [
            'total_transactions' => (clone $query)->count(),
            'total_transaction_value' => round(
                (float) (clone $query)->sum('total_amount'),
                2
            ),
            'by_payment_status' => (clone $query)
                ->select(
                    'payment_status',
                    DB::raw('COUNT(*) as total_transactions'),
                    DB::raw('SUM(total_amount) as transaction_value')
                )
                ->groupBy('payment_status')
                ->orderBy('payment_status')
                ->get()
                ->map(fn($row) => [
                    'payment_status' => $row->payment_status,
                    'total_transactions' =>
                    (int) $row->total_transactions,
                    'transaction_value' =>
                    round((float) $row->transaction_value, 2),
                ])
                ->values(),
        ];
    }

    /**
     * Detail pengeluaran.
     */
    public function getExpenses(
        array $filters = []
    ): LengthAwarePaginator {
        $query = Expense::query()
            ->with([
                'category:id,code,name',
                'paymentMethod:id,code,name,type',
                'creator:id,name',
            ]);

        $this->applyDateFilter(
            $query,
            $filters,
            'expense_date'
        );

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['expense_category_id'])) {
            $query->where(
                'expense_category_id',
                $filters['expense_category_id']
            );
        }

        if (!empty($filters['payment_method_id'])) {
            $query->where(
                'payment_method_id',
                $filters['payment_method_id']
            );
        }

        return $query
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    /**
     * Rekap pengeluaran per kategori.
     */
    public function getExpensesByCategory(
        array $filters = []
    ): array {
        $query = Expense::query()
            ->join(
                'expense_categories',
                'expenses.expense_category_id',
                '=',
                'expense_categories.id'
            );

        $this->applyDateFilter(
            $query,
            $filters,
            'expenses.expense_date'
        );

        return $query
            ->select(
                'expenses.expense_category_id',
                'expense_categories.code',
                'expense_categories.name',
                DB::raw('COUNT(expenses.id) as total_expenses'),
                DB::raw('SUM(expenses.amount) as total_amount')
            )
            ->groupBy(
                'expenses.expense_category_id',
                'expense_categories.code',
                'expense_categories.name'
            )
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($row) => [
                'expense_category_id' =>
                (int) $row->expense_category_id,
                'code' => $row->code,
                'name' => $row->name,
                'total_expenses' => (int) $row->total_expenses,
                'total_amount' => round((float) $row->total_amount, 2),
            ])
            ->all();
    }

    /**
     * Laporan rating dan ulasan.
     */
    public function getReviews(
        array $filters = []
    ): LengthAwarePaginator {
        $query = Review::query()
            ->with(['customer', 'transaction']);

        $this->applyDateFilter($query, $filters);

        if (isset($filters['rating']) && $filters['rating'] !== '') {
            $query->where('rating', (int) $filters['rating']);
        }

        if (
            isset($filters['is_visible'])
            && $filters['is_visible'] !== ''
        ) {
            $query->where(
                'is_visible',
                (int) $filters['is_visible']
            );
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($this->perPage($filters))
            ->withQueryString();
    }

    private function perPage(array $filters): int
    {
        return max(
            1,
            min((int) ($filters['per_page'] ?? 15), 100)
        );
    }
}

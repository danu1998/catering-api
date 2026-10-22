<?php

namespace App\Services;

use App\Models\CateringTransaction;
use App\Models\Expense;
use App\Models\Review;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Menentukan rentang tanggal laporan.
     * Default: bulan berjalan.
     */
    private function getDateRange(array $filters): array
    {
        $from = !empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->toDateString()
            : now()->startOfMonth()->toDateString();

        $to = !empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->toDateString()
            : now()->toDateString();

        return [$from, $to];
    }

    /**
     * Ringkasan dashboard.
     */
    public function getDashboard(array $filters = []): array
    {
        [$from, $to] = $this->getDateRange($filters);

        $transactions = CateringTransaction::query()
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ]);

        $expenses = Expense::query()
            ->whereBetween('expense_date', [$from, $to]);

        $totalTransactions = (clone $transactions)->count();

        $transactionValue = (float) (clone $transactions)
            ->sum('total_amount');

        $totalExpenses = (float) (clone $expenses)
            ->sum('amount');

        $orderStatuses = (clone $transactions)
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $paymentStatuses = (clone $transactions)
            ->select('payment_status', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        return [
            'period' => [
                'date_from' => $from,
                'date_to' => $to,
            ],
            'transactions' => [
                'total' => $totalTransactions,
                'total_value' => round($transactionValue, 2),
                'average_value' => $totalTransactions > 0
                    ? round($transactionValue / $totalTransactions, 2)
                    : 0,
                'by_order_status' => $orderStatuses,
                'by_payment_status' => $paymentStatuses,
            ],
            'expenses' => [
                'total_count' => (clone $expenses)->count(),
                'total_amount' => round($totalExpenses, 2),
            ],
            'financial_overview' => [
                'transaction_value' => round($transactionValue, 2),
                'expense_value' => round($totalExpenses, 2),
                'difference' => round(
                    $transactionValue - $totalExpenses,
                    2
                ),
            ],
            'reviews' => $this->getReviewSummary([
                'date_from' => $from,
                'date_to' => $to,
            ]),
        ];
    }

    /**
     * Grafik harian: nilai transaksi dan pengeluaran.
     * Transaksi dikelompokkan berdasarkan created_at.
     * Pengeluaran dikelompokkan berdasarkan expense_date.
     */
    public function getDailyChart(array $filters = []): array
    {
        [$from, $to] = $this->getDateRange($filters);

        $transactionRows = CateringTransaction::query()
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ])
            ->selectRaw(
                'DATE(created_at) as report_date,
                 COUNT(*) as total_transactions,
                 SUM(total_amount) as transaction_value'
            )
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('report_date');

        $expenseRows = Expense::query()
            ->whereBetween('expense_date', [$from, $to])
            ->selectRaw(
                'expense_date as report_date,
                 COUNT(*) as total_expenses,
                 SUM(amount) as expense_value'
            )
            ->groupBy('expense_date')
            ->get()
            ->keyBy('report_date');

        $chart = [];

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $key = $date->toDateString();

            $transaction = $transactionRows->get($key);
            $expense = $expenseRows->get($key);

            $chart[] = [
                'date' => $key,
                'total_transactions' => (int) (
                    $transaction->total_transactions ?? 0
                ),
                'transaction_value' => (float) (
                    $transaction->transaction_value ?? 0
                ),
                'total_expenses' => (int) (
                    $expense->total_expenses ?? 0
                ),
                'expense_value' => (float) (
                    $expense->expense_value ?? 0
                ),
            ];
        }

        return $chart;
    }

    /**
     * Distribusi status transaksi.
     */
    public function getTransactionStatuses(
        array $filters = []
    ): array {
        [$from, $to] = $this->getDateRange($filters);

        $transactions = CateringTransaction::query()
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ]);

        return [
            'order_status' => (clone $transactions)
                ->select('order_status', DB::raw('COUNT(*) as total'))
                ->groupBy('order_status')
                ->orderBy('order_status')
                ->get(),

            'payment_status' => (clone $transactions)
                ->select('payment_status', DB::raw('COUNT(*) as total'))
                ->groupBy('payment_status')
                ->orderBy('payment_status')
                ->get(),
        ];
    }

    /**
     * Ringkasan pengeluaran.
     */
    public function getExpenseSummary(array $filters = []): array
    {
        [$from, $to] = $this->getDateRange($filters);

        $query = Expense::query()
            ->whereBetween('expense_date', [$from, $to]);

        return [
            'total_expenses' => (clone $query)->count(),
            'total_amount' => round(
                (float) (clone $query)->sum('amount'),
                2
            ),
        ];
    }

    /**
     * Ringkasan ulasan yang tampil di publik.
     */
    public function getReviewSummary(array $filters = []): array
    {
        [$from, $to] = $this->getDateRange($filters);

        $query = Review::query()
            ->where('is_visible', true)
            ->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59',
            ]);

        $total = (clone $query)->count();
        $average = (clone $query)->avg('rating');

        $distribution = [];

        for ($rating = 1; $rating <= 5; $rating++) {
            $distribution[$rating] = (clone $query)
                ->where('rating', $rating)
                ->count();
        }

        return [
            'total_reviews' => $total,
            'average_rating' => $average !== null
                ? round((float) $average, 2)
                : 0,
            'distribution' => $distribution,
        ];
    }
}

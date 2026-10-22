<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Expense::query()
            ->with([
                'category:id,code,name',
                'paymentMethod:id,code,name,type',
                'creator:id,name',
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
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

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'expense_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'expense_date',
                '<=',
                $filters['date_to']
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
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function getSummary(array $filters = []): array
    {
        $query = Expense::query();

        if (!empty($filters['expense_category_id'])) {
            $query->where(
                'expense_category_id',
                $filters['expense_category_id']
            );
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'expense_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'expense_date',
                '<=',
                $filters['date_to']
            );
        }

        if (!empty($filters['payment_method_id'])) {
            $query->where(
                'payment_method_id',
                $filters['payment_method_id']
            );
        }

        return [
            'total_expenses' => (clone $query)->count(),
            'total_amount' => (float) $query->sum('amount'),
        ];
    }

    public function create(array $data, ?int $userId): Expense
    {
        return DB::transaction(function () use ($data, $userId) {
            $this->validateActiveCategory(
                (int) $data['expense_category_id']
            );

            if (!empty($data['payment_method_id'])) {
                $this->validateActivePaymentMethod(
                    (int) $data['payment_method_id']
                );
            }

            $data['expense_number'] = $this->generateNumber();
            $data['created_by'] = $userId;

            $expense = Expense::create($data);

            return $this->findById($expense->id);
        }, 3);
    }

    public function findById(int $id): Expense
    {
        return Expense::query()
            ->with([
                'category:id,code,name',
                'paymentMethod:id,code,name,type',
                'creator:id,name',
            ])
            ->findOrFail($id);
    }

    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            if (isset($data['expense_category_id'])) {
                $this->validateActiveCategory(
                    (int) $data['expense_category_id']
                );
            }

            if (!empty($data['payment_method_id'])) {
                $this->validateActivePaymentMethod(
                    (int) $data['payment_method_id']
                );
            }

            $expense->update($data);

            return $this->findById($expense->id);
        }, 3);
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    private function validateActiveCategory(int $categoryId): void
    {
        $category = ExpenseCategory::query()
            ->whereKey($categoryId)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'expense_category_id' =>
                    'Kategori pengeluaran tidak ditemukan atau tidak aktif.',
            ]);
        }
    }

    private function validateActivePaymentMethod(int $paymentMethodId): void
    {
        $method = PaymentMethod::query()
            ->whereKey($paymentMethodId)
            ->where('is_active', true)
            ->first();

        if (!$method) {
            throw ValidationException::withMessages([
                'payment_method_id' =>
                    'Metode pembayaran tidak ditemukan atau tidak aktif.',
            ]);
        }
    }

    private function generateNumber(): string
    {
        do {
            $number = 'EXP-' . now()->format('Ymd')
                . '-' . Str::upper(Str::random(6));
        } while (
            Expense::where('expense_number', $number)->exists()
        );

        return $number;
    }
}
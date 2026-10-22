<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ExpenseCategoryService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = ExpenseCategory::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (array_key_exists('is_active', $filters)
            && $filters['is_active'] !== null
            && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function create(array $data): ExpenseCategory
    {
        $data['code'] = $this->generateCode();
        $data['is_active'] = $data['is_active'] ?? true;

        return ExpenseCategory::create($data);
    }

    public function findById(int $id): ExpenseCategory
    {
        return ExpenseCategory::findOrFail($id);
    }

    public function update(
        ExpenseCategory $category,
        array $data
    ): ExpenseCategory {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(ExpenseCategory $category): void
    {
        if ($category->expenses()->exists()) {
            throw ValidationException::withMessages([
                'expense_category' =>
                    'Kategori tidak dapat dihapus karena sudah digunakan oleh pengeluaran.',
            ]);
        }

        $category->delete();
    }

    private function generateCode(): string
    {
        do {
            $code = 'EXC-' . Str::upper(Str::random(8));
        } while (ExpenseCategory::where('code', $code)->exists());

        return $code;
    }
}
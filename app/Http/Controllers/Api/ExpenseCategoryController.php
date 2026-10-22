<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        private ExpenseCategoryService $service
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori pengeluaran berhasil diambil.',
            'data' => $this->service->getAll(
                $request->only(['search', 'is_active', 'per_page'])
            ),
        ]);
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        $category = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori pengeluaran berhasil dibuat.',
            'data' => $category,
        ], 201);
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail kategori pengeluaran berhasil diambil.',
            'data' => $this->service->findById($expenseCategory->id),
        ]);
    }

    public function update(
        UpdateExpenseCategoryRequest $request,
        ExpenseCategory $expenseCategory
    ) {
        $category = $this->service->update(
            $expenseCategory,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Kategori pengeluaran berhasil diperbarui.',
            'data' => $category,
        ]);
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $this->service->delete($expenseCategory);

        return response()->json([
            'success' => true,
            'message' => 'Kategori pengeluaran berhasil dihapus.',
        ]);
    }
}
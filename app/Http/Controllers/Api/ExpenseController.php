<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $service
    ) {}

    /**
     * Menampilkan daftar pengeluaran.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'expense_category_id',
            'date_from',
            'date_to',
            'payment_method_id',
            'per_page',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pengeluaran berhasil diambil.',
            'data' => $this->service->getAll($filters),
            'summary' => $this->service->getSummary($filters),
        ]);
    }

    /**
     * Menampilkan ringkasan pengeluaran.
     */
    public function summary(Request $request)
    {
        $filters = $request->only([
            'expense_category_id',
            'date_from',
            'date_to',
            'payment_method_id',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pengeluaran berhasil diambil.',
            'data' => $this->service->getSummary($filters),
        ]);
    }

    /**
     * Menyimpan pengeluaran baru.
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = $this->service->create(
            $request->validated(),
            $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan.',
            'data' => $expense,
        ], 201);
    }

    /**
     * Menampilkan detail pengeluaran.
     */
    public function show(Expense $expense)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pengeluaran berhasil diambil.',
            'data' => $this->service->findById($expense->id),
        ]);
    }

    /**
     * Memperbarui pengeluaran.
     */
    public function update(
        UpdateExpenseRequest $request,
        Expense $expense
    ) {
        $updatedExpense = $this->service->update(
            $expense,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil diperbarui.',
            'data' => $updatedExpense,
        ]);
    }

    /**
     * Menghapus pengeluaran.
     */
    public function destroy(Expense $expense)
    {
        $this->service->delete($expense);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus.',
        ]);
    }
}

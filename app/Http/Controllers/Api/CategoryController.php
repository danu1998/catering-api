<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getAll(
            $request->only(['search', 'is_active'])
        );

        return response()->json([
            'message' => 'Data kategori berhasil diambil.',
            'data' => $categories,
        ]);
    }

    public function store(
        StoreCategoryRequest $request
    ): JsonResponse {
        $category = $this->categoryService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Kategori berhasil dibuat.',
            'data' => $category->append('image_url'),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'message' => 'Detail kategori berhasil diambil.',
            'data' => $category->append('image_url'),
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse {
        $category = $this->categoryService->update(
            $category,
            $request->validated()
        );

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $category->append('image_url'),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVariantRequest;
use App\Http\Requests\UpdateVariantRequest;
use App\Models\Variant;
use App\Services\VariantService;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function __construct(
        private VariantService $variantService
    ) {}

    public function index(Request $request)
    {
        $variants = $this->variantService->getAll(
            $request->only([
                'search',
                'menu_id',
                'is_active',
            ])
        );

        $variants->getCollection()->each(function ($variant) {
            $variant->append('image_url');
        });

        return response()->json([
            'success' => true,
            'message' => 'Data varian berhasil diambil.',
            'data' => $variants,
        ]);
    }

    public function store(StoreVariantRequest $request)
    {
        $variant = $this->variantService->create(
            $request->validated()
        );

        $variant->append('image_url');

        return response()->json([
            'success' => true,
            'message' => 'Varian berhasil ditambahkan.',
            'data' => $variant,
        ], 201);
    }

    public function show(Variant $variant)
    {
        $variant = $this->variantService->findById($variant->id);
        $variant->append('image_url');

        return response()->json([
            'success' => true,
            'message' => 'Detail varian berhasil diambil.',
            'data' => $variant,
        ]);
    }

    public function update(
        UpdateVariantRequest $request,
        Variant $variant
    ) {
        $variant = $this->variantService->update(
            $variant,
            $request->validated()
        );

        $variant->append('image_url');

        return response()->json([
            'success' => true,
            'message' => 'Varian berhasil diperbarui.',
            'data' => $variant,
        ]);
    }

    public function destroy(Variant $variant)
    {
        $this->variantService->delete($variant);

        return response()->json([
            'success' => true,
            'message' => 'Varian berhasil dihapus.',
        ]);
    }
}

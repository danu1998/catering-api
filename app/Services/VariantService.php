<?php

namespace App\Services;

use App\Models\Variant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VariantService
{
    public function getAll(array $filters = [])
    {
        $query = Variant::with('menu:id,name,code,price,min_pax');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['menu_id'])) {
            $query->where('menu_id', $filters['menu_id']);
        }

        if (array_key_exists('is_active', $filters)
            && $filters['is_active'] !== null
            && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
    }

    public function findById(int $id): Variant
    {
        return Variant::with('menu:id,name,code,price,min_pax')
            ->findOrFail($id);
    }

    public function create(array $data): Variant
    {
        $image = $data['image'] ?? null;

        unset($data['image']);

        $data['code'] = $data['code'] ?? $this->generateCode();
        $data['slug'] = $this->generateSlug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('variants', 'public');
        }

        try {
            $variant = DB::transaction(function () use ($data) {
                return Variant::create($data);
            });
        } catch (\Throwable $e) {
            if (!empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            throw $e;
        }

        return $variant->load('menu:id,name,code,price,min_pax');
    }

    public function update(Variant $variant, array $data): Variant
    {
        $newImage = $data['image'] ?? null;
        $oldImage = $variant->image;

        unset($data['image']);

        if (isset($data['name']) && $data['name'] !== $variant->name) {
            $data['slug'] = $this->generateSlug(
                $data['name'],
                $variant->id
            );
        }

        if ($newImage instanceof UploadedFile) {
            $data['image'] = $newImage->store('variants', 'public');
        }

        try {
            DB::transaction(function () use ($variant, $data) {
                $variant->update($data);
            });
        } catch (\Throwable $e) {
            if (!empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            throw $e;
        }

        if ($newImage instanceof UploadedFile && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $variant->fresh()
            ->load('menu:id,name,code,price,min_pax');
    }

    public function delete(Variant $variant): void
    {
        $oldImage = $variant->image;

        DB::transaction(function () use ($variant) {
            $variant->delete();
        });

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'VAR-' . strtoupper(Str::random(8));
        } while (Variant::where('code', $code)->exists());

        return $code;
    }

    private function generateSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Variant::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
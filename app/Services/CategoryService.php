<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function getAll(array $filters = [])
    {
        return Category::query()
            ->when(
                isset($filters['search']) && $filters['search'] !== '',
                fn($query) => $query->where(
                    'name',
                    'like',
                    '%' . $filters['search'] . '%'
                )
            )
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where(
                    'is_active',
                    $filters['is_active']
                )
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
    }

    public function findById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(array $data): Category
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $data['slug'] = $this->generateSlug($data['name']);

        $path = null;

        try {
            if ($image instanceof UploadedFile) {
                $path = $image->store('categories', 'public');
                $data['image'] = $path;
            }

            return DB::transaction(
                fn() => Category::create($data)
            );
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    public function update(
        Category $category,
        array $data
    ): Category {
        $image = $data['image'] ?? null;
        unset($data['image']);

        if (isset($data['name'])) {
            $data['slug'] = $this->generateSlug(
                $data['name'],
                $category->id
            );
        }

        $oldImage = $category->image;
        $newPath = null;

        try {
            if ($image instanceof UploadedFile) {
                $newPath = $image->store('categories', 'public');
                $data['image'] = $newPath;
            }

            DB::transaction(function () use ($category, $data) {
                $category->update($data);
            });

            if ($newPath && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            return $category->refresh();
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }
    }

    public function delete(Category $category): void
    {
        if ($category->menus()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'category_id' => [
                    'Kategori tidak dapat dihapus karena masih digunakan oleh menu.',
                ],
            ]);
        }

        $oldImage = $category->image;

        DB::transaction(function () use ($category) {
            $category->delete();
        });

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }
    }

    private function generateSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'kategori';
        }

        $slug = $base;
        $counter = 1;

        while (
            Category::where('slug', $slug)
            ->when(
                $ignoreId,
                fn($query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}

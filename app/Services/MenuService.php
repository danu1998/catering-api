<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MenuService
{
    public function getAll(array $filters = [])
    {
        $query = Menu::with('category:id,name,slug');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
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

    public function findById(int $id): Menu
    {
        return Menu::with('category:id,name,slug')->findOrFail($id);
    }

    public function create(array $data): Menu
    {
        $image = $data['image'] ?? null;

        unset($data['image']);

        $data['code'] = $data['code'] ?? $this->generateCode();
        $data['slug'] = $this->generateSlug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('menus', 'public');
        }

        try {
            $menu = DB::transaction(function () use ($data) {
                return Menu::create($data);
            });
        } catch (\Throwable $e) {
            if (!empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            throw $e;
        }

        return $menu->load('category:id,name,slug');
    }

    public function update(Menu $menu, array $data): Menu
    {
        $newImage = $data['image'] ?? null;
        $oldImage = $menu->image;

        unset($data['image']);

        if (isset($data['name']) && $data['name'] !== $menu->name) {
            $data['slug'] = $this->generateSlug(
                $data['name'],
                $menu->id
            );
        }

        if ($newImage instanceof UploadedFile) {
            $data['image'] = $newImage->store('menus', 'public');
        }

        try {
            DB::transaction(function () use ($menu, $data) {
                $menu->update($data);
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

        return $menu->fresh()->load('category:id,name,slug');
    }

    public function delete(Menu $menu): void
    {
        $oldImage = $menu->image;

        DB::transaction(function () use ($menu) {
            $menu->delete();
        });

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'MNU-' . strtoupper(Str::random(8));
        } while (Menu::where('code', $code)->exists());

        return $code;
    }

    private function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Menu::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryItemService
{
    public function getAll(array $filters = [])
    {
        $query = InventoryItem::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (array_key_exists('is_active', $filters)
            && $filters['is_active'] !== null
            && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        if (($filters['low_stock'] ?? null) === '1') {
            $query->whereColumn('current_stock', '<=', 'minimum_stock');
        }

        return $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
    }

    public function findById(int $id): InventoryItem
    {
        return InventoryItem::findOrFail($id);
    }

    public function create(array $data): InventoryItem
    {
        $data['code'] = $data['code'] ?? $this->generateCode();
        $data['current_stock'] = $data['current_stock'] ?? 0;
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['average_cost'] = $data['average_cost'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        return DB::transaction(function () use ($data) {
            return InventoryItem::create($data);
        });
    }

    public function update(
        InventoryItem $item,
        array $data
    ): InventoryItem {
        // Perubahan saldo rutin harus melalui mutasi stok.
        unset($data['current_stock']);

        return DB::transaction(function () use ($item, $data) {
            $item->update($data);

            return $item->fresh();
        });
    }

    public function delete(InventoryItem $item): void
    {
        if ((float) $item->current_stock !== 0.0) {
            throw ValidationException::withMessages([
                'inventory_item' => [
                    'Bahan tidak dapat dihapus karena stok masih tersedia. '
                    . 'Lakukan penyesuaian stok terlebih dahulu.',
                ],
            ]);
        }

        DB::transaction(function () use ($item) {
            $item->delete();
        });
    }

    private function generateCode(): string
    {
        do {
            $code = 'ING-' . strtoupper(Str::random(8));
        } while (InventoryItem::where('code', $code)->exists());

        return $code;
    }
}
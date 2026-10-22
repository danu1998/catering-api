<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMutationService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = InventoryMutation::with([
            'inventoryItem:id,code,name,unit,current_stock',
            'user:id,name',
        ]);

        if (!empty($filters['inventory_item_id'])) {
            $query->where(
                'inventory_item_id',
                $filters['inventory_item_id']
            );
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }

        return $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function findById(int $id): InventoryMutation
    {
        return InventoryMutation::with([
            'inventoryItem',
            'user:id,name',
        ])->findOrFail($id);
    }

    public function create(
        array $data,
        ?int $userId
    ): InventoryMutation {
        return DB::transaction(function () use ($data, $userId) {
            // Mengunci baris bahan untuk mencegah dua mutasi
            // bersamaan menghitung saldo dari nilai yang sama.
            $item = InventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($data['inventory_item_id']);

            if (!$item->is_active) {
                throw ValidationException::withMessages([
                    'inventory_item_id' =>
                    'Bahan baku tidak aktif dan tidak dapat dimutasi.',
                ]);
            }

            $before = (float) $item->current_stock;
            $quantity = (float) $data['quantity'];

            if (
                $data['type'] !== 'adjustment'
                && $quantity <= 0
            ) {
                throw ValidationException::withMessages([
                    'quantity' =>
                    'Jumlah stok masuk atau keluar harus lebih besar dari nol.',
                ]);
            }

            if ($data['type'] === 'in') {
                $after = $before + $quantity;
            } elseif ($data['type'] === 'out') {
                if ($quantity > $before) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                        'Jumlah stok keluar melebihi stok yang tersedia.',
                    ]);
                }

                $after = $before - $quantity;
            } else {
                // Adjustment: quantity adalah stok fisik hasil hitung.
                $after = $quantity;
            }

            $before = round($before, 3);
            $after = round($after, 3);

            $item->current_stock = $after;
            $item->save();

            $mutation = InventoryMutation::create([
                'inventory_item_id' => $item->id,
                'user_id' => $userId,
                'type' => $data['type'],
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'reference_number' =>
                $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $mutation->load([
                'inventoryItem',
                'user:id,name',
            ]);
        }, 3);
    }
}

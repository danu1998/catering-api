<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMutation;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockOpnameService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = StockOpname::with([
            'creator:id,name',
            'completer:id,name',
        ])->withCount('items');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'opname_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'opname_date',
                '<=',
                $filters['date_to']
            );
        }

        return $query->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function findById(int $id): StockOpname
    {
        return StockOpname::with([
            'creator:id,name',
            'completer:id,name',
            'items.inventoryItem:id,code,name,unit,current_stock',
        ])->findOrFail($id);
    }

    public function create(array $data, ?int $userId): StockOpname
    {
        return DB::transaction(function () use ($data, $userId) {
            $opname = StockOpname::create([
                'number' => $this->generateNumber(),
                'opname_date' => $data['opname_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Snapshot seluruh bahan baku aktif.
            $items = InventoryItem::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'current_stock']);

            foreach ($items as $item) {
                $opname->items()->create([
                    'inventory_item_id' => $item->id,
                    'system_stock' => $item->current_stock,
                    'physical_stock' => null,
                    'difference' => null,
                ]);
            }

            return $this->findById($opname->id);
        });
    }

    public function updateItem(
        StockOpname $opname,
        StockOpnameItem $opnameItem,
        array $data
    ): StockOpnameItem {
        if ($opname->status !== 'draft') {
            throw ValidationException::withMessages([
                'stock_opname' =>
                    'Hanya sesi draft yang dapat diubah.',
            ]);
        }

        if ($opnameItem->stock_opname_id !== $opname->id) {
            throw ValidationException::withMessages([
                'item' => 'Item tidak termasuk dalam sesi Stock Opname ini.',
            ]);
        }

        $opnameItem->physical_stock = $data['physical_stock'];
        $opnameItem->difference = round(
            (float) $data['physical_stock']
                - (float) $opnameItem->system_stock,
            3
        );
        $opnameItem->notes = $data['notes'] ?? null;
        $opnameItem->save();

        return $opnameItem->load(
            'inventoryItem:id,code,name,unit,current_stock'
        );
    }

    public function complete(
        int $opnameId,
        ?int $userId
    ): StockOpname {
        return DB::transaction(function () use ($opnameId, $userId) {
            $opname = StockOpname::query()
                ->lockForUpdate()
                ->findOrFail($opnameId);

            if ($opname->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Sesi bukan draft dan tidak dapat difinalisasi.',
                ]);
            }

            $items = $opname->items()
                ->orderBy('inventory_item_id')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Tidak ada bahan baku dalam sesi ini.',
                ]);
            }

            if ($items->contains(
                fn ($item) => $item->physical_stock === null
            )) {
                throw ValidationException::withMessages([
                    'items' =>
                        'Isi hasil hitung semua bahan baku sebelum finalisasi.',
                ]);
            }

            foreach ($items as $opnameItem) {
                // Lock bahan baku dengan urutan ID yang konsisten.
                $inventoryItem = InventoryItem::query()
                    ->lockForUpdate()
                    ->findOrFail($opnameItem->inventory_item_id);

                $snapshot = round(
                    (float) $opnameItem->system_stock,
                    3
                );

                $current = round(
                    (float) $inventoryItem->current_stock,
                    3
                );

                if ($current !== $snapshot) {
                    throw ValidationException::withMessages([
                        'stock' =>
                            "Stok {$inventoryItem->name} berubah sejak sesi dibuat. "
                            . 'Batalkan sesi ini dan buat sesi Stock Opname baru.',
                    ]);
                }

                $physical = round(
                    (float) $opnameItem->physical_stock,
                    3
                );

                $difference = round($physical - $current, 3);

                $opnameItem->difference = $difference;
                $opnameItem->save();

                // Tidak perlu mutasi koreksi jika saldo tidak berubah.
                if ($difference === 0.0) {
                    continue;
                }

                $inventoryItem->current_stock = $physical;
                $inventoryItem->save();

                InventoryMutation::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => $userId,
                    'type' => 'adjustment',
                    // Pada adjustment, quantity berarti stok fisik baru.
                    'quantity' => $physical,
                    'stock_before' => $current,
                    'stock_after' => $physical,
                    'reference_number' => $opname->number,
                    'notes' => 'Koreksi dari Stock Opname '
                        . $opname->number
                        . ($opnameItem->notes
                            ? ': ' . $opnameItem->notes
                            : ''),
                ]);
            }

            $opname->status = 'completed';
            $opname->completed_by = $userId;
            $opname->completed_at = now();
            $opname->save();

            return $this->findById($opname->id);
        }, 3);
    }

    public function cancel(StockOpname $opname): StockOpname
    {
        return DB::transaction(function () use ($opname) {
            $locked = StockOpname::query()
                ->lockForUpdate()
                ->findOrFail($opname->id);

            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Hanya sesi draft yang dapat dibatalkan.',
                ]);
            }

            $locked->status = 'cancelled';
            $locked->save();

            return $this->findById($locked->id);
        });
    }

    private function generateNumber(): string
    {
        do {
            $number = 'SO-' . now()->format('Ymd')
                . '-' . Str::upper(Str::random(6));
        } while (StockOpname::where('number', $number)->exists());

        return $number;
    }
}
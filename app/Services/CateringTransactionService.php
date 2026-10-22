<?php

namespace App\Services;

use App\Models\CateringTransaction;
use App\Models\CateringTransactionStatusHistory;
use App\Models\Customer;
use App\Models\Menu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class CateringTransactionService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = CateringTransaction::withCount('items')
            ->with('creator:id,name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'delivery_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'delivery_date',
                '<=',
                $filters['date_to']
            );
        }

        return $query
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    public function findById(int $id): CateringTransaction
    {
        return CateringTransaction::query()
            ->with([
                'customer',
                'items.menu:id,code,name,price',
                'creator:id,name',
                'statusHistories.changedBy:id,name',
            ])
            ->findOrFail($id);
    }


    public function create(
        array $data,
        ?int $userId
    ): CateringTransaction {
        return DB::transaction(function () use ($data, $userId) {
            $items = $this->prepareItems($data['items']);

            $subtotal = $this->calculateSubtotal($items);
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0);
            $discount = (float) ($data['discount_amount'] ?? 0);

            $total = $this->calculateTotal(
                $subtotal,
                $deliveryFee,
                $discount
            );

            // Ambil pelanggan terdaftar jika customer_id dikirim.
            $customer = null;

            if (!empty($data['customer_id'])) {
                $customer = Customer::query()
                    ->whereKey($data['customer_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$customer) {
                    throw ValidationException::withMessages([
                        'customer_id' =>
                        'Pelanggan tidak ditemukan atau sudah tidak aktif.',
                    ]);
                }
            }

            // Data pelanggan terdaftar menjadi nilai default.
            // Data manual tetap dapat digunakan jika tidak ada customer_id.
            $customerName = $customer
                ? $customer->name
                : ($data['customer_name'] ?? null);

            $customerPhone = $customer
                ? $customer->phone
                : ($data['customer_phone'] ?? null);

            $customerEmail = $customer
                ? $customer->email
                : ($data['customer_email'] ?? null);

            $companyName = $customer
                && in_array($customer->customer_type, ['company', 'group'], true)
                ? $customer->name
                : ($data['company_name'] ?? null);

            $deliveryAddress = !empty($data['delivery_address'])
                ? $data['delivery_address']
                : ($customer?->address);

            $transaction = CateringTransaction::create([
                'transaction_number' => $this->generateNumber(),
                'order_source' => $data['order_source'] ?? 'pos',

                'customer_id' => $customer?->id,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail,
                'company_name' => $companyName,

                'delivery_address' => $deliveryAddress,
                'delivery_date' => $data['delivery_date'],
                'delivery_time' => $data['delivery_time'] ?? null,

                'total_pax' => array_sum(
                    array_column($items, 'quantity')
                ),

                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discount,
                'total_amount' => $total,

                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Simpan seluruh item transaksi.
            foreach ($items as $item) {
                $transaction->items()->create($item);
            }

            // Riwayat status awal hanya dibuat satu kali.
            CateringTransactionStatusHistory::create([
                'catering_transaction_id' => $transaction->id,
                'old_status' => null,
                'new_status' => 'pending',
                'notes' => 'Pesanan dibuat',
                'changed_by' => $userId,
            ]);

            return $this->findById($transaction->id);
        }, 3);
    }


    public function update(
        CateringTransaction $transaction,
        array $data
    ): CateringTransaction {
        return DB::transaction(function () use ($transaction, $data) {
            $locked = CateringTransaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if (
                $locked->order_status !== 'pending'
                || $locked->payment_status !== 'unpaid'
            ) {
                throw ValidationException::withMessages([
                    'transaction' =>
                    'Hanya pesanan pending yang belum dibayar yang dapat diubah.',
                ]);
            }

            $locked->fill(collect($data)->except('items')->all());

            if (array_key_exists('items', $data)) {
                $preparedItems = $this->prepareItems($data['items']);

                $locked->items()->delete();

                foreach ($preparedItems as $item) {
                    $locked->items()->create($item);
                }
            } else {
                $preparedItems = $locked->items()
                    ->get()
                    ->map(fn($item) => [
                        'menu_id' => $item->menu_id,
                        'menu_name' => $item->menu_name,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'subtotal' => (float) $item->subtotal,
                        'notes' => $item->notes,
                    ])
                    ->all();
            }

            $subtotal = $this->calculateSubtotal($preparedItems);
            $deliveryFee = (float) $locked->delivery_fee;
            $discount = (float) $locked->discount_amount;

            $locked->subtotal = $subtotal;
            $locked->total_pax = array_sum(
                array_column($preparedItems, 'quantity')
            );
            $locked->total_amount = $this->calculateTotal(
                $subtotal,
                $deliveryFee,
                $discount
            );

            $locked->save();

            return $this->findById($locked->id);
        }, 3);
    }

    private function prepareItems(array $items): array
    {
        $prepared = [];

        foreach ($items as $row) {
            $menu = Menu::query()
                ->lockForUpdate()
                ->findOrFail($row['menu_id']);

            if (!$menu->is_active) {
                throw ValidationException::withMessages([
                    'items' => "Menu {$menu->name} tidak aktif.",
                ]);
            }

            $quantity = (int) $row['quantity'];
            $minimumPax = (int) $menu->min_pax;

            if ($quantity < $minimumPax) {
                throw ValidationException::withMessages([
                    'items' =>
                    "Jumlah pesanan menu {$menu->name} minimal {$minimumPax} porsi.",
                ]);
            }

            $unitPrice = round((float) $menu->price, 2);
            $lineSubtotal = round($unitPrice * $quantity, 2);

            $prepared[] = [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
                'notes' => $row['notes'] ?? null,
            ];
        }

        return $prepared;
    }

    private function calculateSubtotal(array $items): float
    {
        return round(
            array_sum(array_column($items, 'subtotal')),
            2
        );
    }

    private function calculateTotal(
        float $subtotal,
        float $deliveryFee,
        float $discount
    ): float {
        $total = round($subtotal + $deliveryFee - $discount, 2);

        if ($discount > $subtotal + $deliveryFee) {
            throw ValidationException::withMessages([
                'discount_amount' =>
                'Diskon tidak boleh melebihi subtotal ditambah biaya pengiriman.',
            ]);
        }

        return $total;
    }

    private function generateNumber(): string
    {
        do {
            $number = 'TRX-' . now()->format('Ymd')
                . '-' . Str::upper(Str::random(6));
        } while (
            CateringTransaction::where(
                'transaction_number',
                $number
            )->exists()
        );

        return $number;
    }


    public function updateStatus(
        CateringTransaction $transaction,
        string $newStatus,
        ?string $notes,
        ?int $userId
    ): CateringTransaction {
        return DB::transaction(function () use (
            $transaction,
            $newStatus,
            $notes,
            $userId
        ) {
            $lockedTransaction = CateringTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentStatus = $lockedTransaction->order_status;

            $allowedTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['processing', 'cancelled'],
                'processing' => ['ready', 'cancelled'],
                'ready' => ['delivered', 'cancelled'],
                'delivered' => [],
                'cancelled' => [],
            ];

            $allowedStatuses = $allowedTransitions[$currentStatus] ?? [];

            if (! in_array($newStatus, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'status' => [
                        "Status tidak dapat diubah dari {$currentStatus} menjadi {$newStatus}.",
                    ],
                ]);
            }

            $lockedTransaction->order_status = $newStatus;
            $lockedTransaction->save();

            CateringTransactionStatusHistory::create([
                'catering_transaction_id' => $lockedTransaction->id,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
                'notes' => $notes,
                'changed_by' => $userId,
            ]);

            return $this->findById($lockedTransaction->id);
        });
    }
}

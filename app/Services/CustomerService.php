<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when(
                $filters['customer_type'] ?? null,
                fn($query, $type) => $query->where('customer_type', $type)
            )
            ->when(
                isset($filters['is_active']) && $filters['is_active'] !== '',
                fn($query) => $query->where(
                    'is_active',
                    filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)
                )
            )
            ->orderBy('name')
            ->paginate(
                min(max((int) ($filters['per_page'] ?? 10), 1), 100)
            );
    }

    public function create(array $data): Customer
    {
        $data['code'] = $this->generateCode();
        $data['people_count'] = $data['people_count'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }

    public function findById(int $id): Customer
    {
        return Customer::findOrFail($id);
    }

    private function generateCode(): string
    {
        do {
            $code = 'CUS-' . Str::upper(Str::random(8));
        } while (Customer::where('code', $code)->exists());

        return $code;
    }

    public function getTransactions(
        Customer $customer,
        array $filters = []
    ): LengthAwarePaginator {
        $query = $customer->transactions()
            ->withCount('items')
            ->with('creator:id,name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where(
                    'transaction_number',
                    'like',
                    "%{$search}%"
                )->orWhere(
                    'customer_name',
                    'like',
                    "%{$search}%"
                );
            });
        }

        if (!empty($filters['order_status'])) {
            $query->where(
                'order_status',
                $filters['order_status']
            );
        }

        if (!empty($filters['payment_status'])) {
            $query->where(
                'payment_status',
                $filters['payment_status']
            );
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
}

<?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PaymentMethodService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return PaymentMethod::query()
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                isset($filters['is_active'])
                    && $filters['is_active'] !== '',
                fn ($query) => $query->where(
                    'is_active',
                    filter_var(
                        $filters['is_active'],
                        FILTER_VALIDATE_BOOLEAN
                    )
                )
            )
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 10));
    }

    public function create(array $data): PaymentMethod
    {
        $data['code'] = $this->generateCode();
        $data['is_active'] = $data['is_active'] ?? true;

        return PaymentMethod::create($data);
    }

    public function update(
        PaymentMethod $paymentMethod,
        array $data
    ): PaymentMethod {
        $paymentMethod->update($data);

        return $paymentMethod->refresh();
    }

    public function findById(int $id): PaymentMethod
    {
        return PaymentMethod::findOrFail($id);
    }

    private function generateCode(): string
    {
        do {
            $code = 'PAY-' . Str::upper(Str::random(8));
        } while (PaymentMethod::where('code', $code)->exists());

        return $code;
    }
}
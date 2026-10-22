<?php

namespace App\Services;

use App\Models\CateringTransaction;
use App\Models\CateringTransactionPayment;
use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionPaymentService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return CateringTransactionPayment::query()
            ->with([
                'transaction:id,transaction_number,total_amount,payment_status',
                'paymentMethod:id,code,name,type',
                'creator:id,name',
            ])
            ->when(
                $filters['catering_transaction_id'] ?? null,
                fn ($query, $id) => $query->where(
                    'catering_transaction_id',
                    $id
                )
            )
            ->when(
                $filters['payment_status'] ?? null,
                fn ($query, $status) => $query->where(
                    'payment_status',
                    $status
                )
            )
            ->orderByDesc('id')
            ->paginate(
                min(max((int) ($filters['per_page'] ?? 10), 1), 100)
            );
    }

    public function findById(int $id): CateringTransactionPayment
    {
        return CateringTransactionPayment::with([
            'transaction',
            'paymentMethod',
            'creator:id,name',
        ])->findOrFail($id);
    }

    public function create(
        array $data,
        ?int $userId
    ): CateringTransactionPayment {
        return DB::transaction(function () use ($data, $userId) {
            $transaction = CateringTransaction::query()
                ->lockForUpdate()
                ->findOrFail($data['catering_transaction_id']);

            $method = PaymentMethod::query()
                ->whereKey($data['payment_method_id'])
                ->where('is_active', true)
                ->first();

            if (!$method) {
                throw ValidationException::withMessages([
                    'payment_method_id' =>
                        'Metode pembayaran tidak ditemukan atau tidak aktif.',
                ]);
            }

            if (in_array($transaction->order_status, [
                'cancelled',
            ], true)) {
                throw ValidationException::withMessages([
                    'catering_transaction_id' =>
                        'Pembayaran tidak dapat ditambahkan untuk pesanan yang dibatalkan.',
                ]);
            }

            $payment = CateringTransactionPayment::create([
                'catering_transaction_id' => $transaction->id,
                'payment_method_id' => $method->id,
                'amount' => $data['amount'],
                'payment_status' => 'pending',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            return $payment->load([
                'transaction',
                'paymentMethod',
                'creator:id,name',
            ]);
        }, 3);
    }

    public function updateStatus(
        CateringTransactionPayment $payment,
        array $data
    ): CateringTransactionPayment {
        return DB::transaction(function () use ($payment, $data) {
            // Kunci transaksi terlebih dahulu untuk mencegah
            // dua pembayaran diverifikasi bersamaan secara berlebihan.
            $transaction = CateringTransaction::query()
                ->lockForUpdate()
                ->findOrFail($payment->catering_transaction_id);

            $payment = CateringTransactionPayment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->payment_status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment_status' =>
                        'Hanya pembayaran berstatus pending yang dapat diperbarui.',
                ]);
            }

            $newStatus = $data['payment_status'];

            if ($newStatus === 'paid') {
                if ($transaction->order_status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'payment_status' =>
                            'Pesanan dibatalkan sehingga pembayaran tidak dapat disahkan.',
                    ]);
                }

                $otherPaidAmount = CateringTransactionPayment::query()
                    ->where(
                        'catering_transaction_id',
                        $transaction->id
                    )
                    ->where('payment_status', 'paid')
                    ->where('id', '!=', $payment->id)
                    ->sum('amount');

                $totalPaid = round(
                    (float) $otherPaidAmount + (float) $payment->amount,
                    2
                );

                $totalAmount = round(
                    (float) $transaction->total_amount,
                    2
                );

                if ($totalPaid > $totalAmount) {
                    throw ValidationException::withMessages([
                        'amount' =>
                            'Nominal pembayaran melebihi sisa tagihan transaksi.',
                    ]);
                }

                $payment->paid_at = now();
            }

            $payment->payment_status = $newStatus;

            if (array_key_exists('reference_number', $data)) {
                $payment->reference_number = $data['reference_number'];
            }

            if (array_key_exists('notes', $data)) {
                $payment->notes = $data['notes'];
            }

            $payment->save();

            $paidAmount = CateringTransactionPayment::query()
                ->where('catering_transaction_id', $transaction->id)
                ->where('payment_status', 'paid')
                ->sum('amount');

            $paidAmount = round((float) $paidAmount, 2);
            $totalAmount = round((float) $transaction->total_amount, 2);

            if ($paidAmount <= 0) {
                $transaction->payment_status = 'unpaid';
            } elseif ($paidAmount < $totalAmount) {
                $transaction->payment_status = 'partial';
            } else {
                $transaction->payment_status = 'paid';
            }

            $transaction->save();

            return $payment->load([
                'transaction',
                'paymentMethod',
                'creator:id,name',
            ]);
        }, 3);
    }
}
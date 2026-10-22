
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_transaction_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('catering_transaction_id');
            $table->unsignedBigInteger('payment_method_id');

            $table->decimal('amount', 15, 2);
            $table->string('payment_status', 30)->default('pending');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign(
                'catering_transaction_id',
                'ctp_transaction_fk'
            )->references('id')
                ->on('catering_transactions')
                ->cascadeOnDelete();

            $table->foreign(
                'payment_method_id',
                'ctp_method_fk'
            )->references('id')
                ->on('payment_methods')
                ->restrictOnDelete();

            $table->foreign(
                'created_by',
                'ctp_creator_fk'
            )->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(
                ['catering_transaction_id', 'payment_status'],
                'ctp_transaction_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_transaction_payments');
    }
};

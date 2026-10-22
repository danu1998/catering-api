
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();

            // Sumber pesanan: POS admin atau landing page.
            $table->string('order_source', 20)->default('pos');

            // Informasi pelanggan.
            $table->string('customer_name', 150);
            $table->string('customer_phone', 30);
            $table->string('customer_email', 150)->nullable();
            $table->string('company_name', 150)->nullable();

            // Informasi pengiriman.
            $table->text('delivery_address');
            $table->date('delivery_date');
            $table->string('delivery_time', 30)->nullable();
            $table->unsignedInteger('total_pax')->default(1);

            // Ringkasan nilai transaksi.
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('delivery_fee', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            // Status pesanan dan pembayaran.
            $table->string('order_status', 30)->default('pending');
            $table->string('payment_status', 30)->default('unpaid');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['order_status', 'delivery_date']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['customer_phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_transactions');
    }
};

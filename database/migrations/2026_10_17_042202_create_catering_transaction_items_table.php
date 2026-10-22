
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_transaction_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('catering_transaction_id')
                ->constrained('catering_transactions')
                ->cascadeOnDelete();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->restrictOnDelete();

            // Snapshot menu pada saat transaksi dibuat.
            $table->string('menu_name', 150);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('menu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_transaction_items');
    }
};

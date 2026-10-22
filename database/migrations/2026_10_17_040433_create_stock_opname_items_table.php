
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_opname_id')
                ->constrained('stock_opnames')
                ->cascadeOnDelete();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            $table->decimal('system_stock', 15, 3);
            $table->decimal('physical_stock', 15, 3)->nullable();
            $table->decimal('difference', 15, 3)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'stock_opname_id',
                'inventory_item_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('category', 100)->nullable();

            // Contoh satuan: kg, gram, liter, ml, pcs
            $table->string('unit', 30);

            $table->decimal('current_stock', 15, 3)->default(0);
            $table->decimal('minimum_stock', 15, 3)->default(0);

            // Harga beli rata-rata per satuan stok
            $table->decimal('average_cost', 15, 2)->default(0);

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};

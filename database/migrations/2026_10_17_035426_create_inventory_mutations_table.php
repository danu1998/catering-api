
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_mutations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_item_id')
                ->constrained('inventory_items')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('type', 20);
            $table->decimal('quantity', 15, 3);
            $table->decimal('stock_before', 15, 3);
            $table->decimal('stock_after', 15, 3);

            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_mutations');
    }
};

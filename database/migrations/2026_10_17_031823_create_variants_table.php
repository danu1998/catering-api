
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->restrictOnDelete();

            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->decimal('additional_price', 15, 2)->default(0);
            $table->unsignedInteger('min_pax')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['menu_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};

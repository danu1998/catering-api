
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('menu_id')
                ->constrained('menus')
                ->restrictOnDelete();

            $table->date('schedule_date');

            // Jika null, gunakan harga dasar dari menu.
            $table->decimal('price_override', 15, 2)->nullable();

            // Jika null, tidak ada batas kuota yang ditetapkan.
            $table->unsignedInteger('quota')->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['menu_id', 'schedule_date'],
                'menu_schedules_menu_date_unique'
            );

            $table->index(['schedule_date', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_schedules');
    }
};

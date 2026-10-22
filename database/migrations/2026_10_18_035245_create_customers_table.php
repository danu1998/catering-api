
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('customer_type', 30)->default('company');
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('people_count')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['name', 'is_active'],
                'customers_name_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

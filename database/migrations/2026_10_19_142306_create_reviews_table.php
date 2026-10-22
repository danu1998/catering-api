<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    
public function up(): void
{
    Schema::create('reviews', function (Blueprint $table) {
        $table->id();

        $table->foreignId('catering_transaction_id')
            ->nullable()
            ->constrained('catering_transactions')
            ->nullOnDelete();

        $table->foreignId('customer_id')
            ->nullable()
            ->constrained('customers')
            ->nullOnDelete();

        $table->string('customer_name', 150);
        $table->unsignedTinyInteger('rating');
        $table->text('comment')->nullable();
        $table->boolean('is_visible')->default(true);

        $table->timestamps();

        $table->index(['rating', 'is_visible']);
        $table->index('created_at');
    });
}

};

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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('expense_number', 40)->unique();

            $table->unsignedBigInteger('expense_category_id');
            $table->foreign('expense_category_id', 'expense_category_fk')
                ->references('id')
                ->on('expense_categories')
                ->restrictOnDelete();

            $table->string('title', 150);
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);

            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->foreign('payment_method_id', 'expense_payment_method_fk')
                ->references('id')
                ->on('payment_methods')
                ->nullOnDelete();

            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by', 'expense_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['expense_date', 'expense_category_id'],
                'expenses_date_category_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

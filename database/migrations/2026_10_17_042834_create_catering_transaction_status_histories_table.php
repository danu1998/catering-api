
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catering_transaction_status_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('catering_transaction_id');

            $table->foreign('catering_transaction_id', 'ctsh_transaction_fk')
                ->references('id')
                ->on('catering_transactions')
                ->cascadeOnDelete();

            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('changed_by')->nullable();

            $table->foreign('changed_by', 'ctsh_changed_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['catering_transaction_id', 'created_at'],
                'ctsh_transaction_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catering_transaction_status_histories');
    }
};

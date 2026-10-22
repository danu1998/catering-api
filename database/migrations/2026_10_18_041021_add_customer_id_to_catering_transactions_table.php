
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catering_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->foreign('customer_id', 'ct_customer_fk')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->index('customer_id', 'ct_customer_idx');
        });
    }

    public function down(): void
    {
        Schema::table('catering_transactions', function (Blueprint $table) {
            $table->dropForeign('ct_customer_fk');
            $table->dropIndex('ct_customer_idx');
            $table->dropColumn('customer_id');
        });
    }
};

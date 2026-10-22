<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();

            $table->string('business_name', 150);
            $table->string('tagline', 200)->nullable();
            $table->text('description')->nullable();

            $table->string('logo')->nullable();

            $table->string('phone', 30)->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();

            $table->text('opening_hours')->nullable();

            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('website_url')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
<?php
// database/migrations/2025_06_16_create_locations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('category');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('address')->nullable();
            $table->string('image_url')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
            $table->text('opening_hours')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Add spatial index for location queries
            $table->index(['latitude', 'longitude']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
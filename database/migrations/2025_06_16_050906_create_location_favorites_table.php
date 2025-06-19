<?php
// database/migrations/2025_06_16_create_location_favorites_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate favorites
            $table->unique(['user_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_favorites');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus table lama jika ada
        Schema::dropIfExists('comics');
        
        // Buat table series
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image');
            $table->string('status')->default('ongoing');
            $table->timestamps();
        });

        // Buat table comics baru
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->onDelete('cascade');
            $table->string('chapter_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image');
            $table->json('pages');
            $table->integer('page_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comics');
        Schema::dropIfExists('series');
    }
};
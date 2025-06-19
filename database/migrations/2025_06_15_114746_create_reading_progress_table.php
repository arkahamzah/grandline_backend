<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('comic_id')->constrained()->onDelete('cascade');
            $table->foreignId('series_id')->constrained()->onDelete('cascade');
            $table->integer('current_page')->default(0);
            $table->integer('total_pages');
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->timestamp('last_read_at');
            $table->timestamps();
            $table->unique(['user_id', 'comic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_progress');
    }
};
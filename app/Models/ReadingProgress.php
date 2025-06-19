<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingProgress extends Model
{
    protected $table = 'reading_progress';
    
    protected $fillable = [
        'user_id', 'comic_id', 'series_id', 'current_page', 
        'total_pages', 'progress_percentage', 'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'current_page' => 'integer',
        'total_pages' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function comic() { return $this->belongsTo(Comic::class); }
    public function series() { return $this->belongsTo(Series::class); }
}
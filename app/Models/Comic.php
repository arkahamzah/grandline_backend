<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comic extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'chapter_number',
        'title',
        'description',
        'cover_image',
        'pages',
        'page_count'
    ];

    protected $casts = [
        'pages' => 'array'
    ];

    protected $appends = ['cover_url', 'pages_urls'];

    public function getCoverUrlAttribute()
    {
        return url('storage/comics/' . $this->cover_image);
    }

    public function getPagesUrlsAttribute()
    {
        if (!$this->pages) return [];
        
        return array_map(function($page) {
            return url('storage/comics/' . $page);
        }, $this->pages);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }
}
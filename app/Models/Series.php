<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cover_image',
        'status'
    ];

    protected $appends = ['cover_url'];

    public function getCoverUrlAttribute()
    {
        if (!$this->cover_image) return null;
        return url('storage/series/' . $this->cover_image);
    }

    public function comics()
    {
        return $this->hasMany(Comic::class)->orderBy('chapter_number', 'asc');
    }

        public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // Check if series is favorited by user
    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ENHANCED ACCESSOR DENGAN GIF SUPPORT DAN CACHE BUSTING
    protected $appends = ['profile_image_url'];

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            // Base URL tanpa cache busting
            $baseUrl = url('storage/profile_images/' . $this->profile_image);
            
            // Extract timestamp dari filename untuk cache busting
            $filename = $this->profile_image;
            $timestamp = null;
            
            // Coba extract timestamp dari filename format: timestamp_userid_random.ext
            if (preg_match('/^(\d+)_\d+_[a-z0-9]+\.\w+$/i', $filename, $matches)) {
                $timestamp = $matches[1];
            } else {
                // Fallback: gunakan file modification time
                $filePath = storage_path('app/public/profile_images/' . $filename);
                if (file_exists($filePath)) {
                    $timestamp = filemtime($filePath);
                } else {
                    // Last fallback: gunakan current timestamp
                    $timestamp = time();
                }
            }
            
            // CACHE BUSTING: tambahkan timestamp sebagai query parameter
            $cacheBustingUrl = $baseUrl . '?v=' . $timestamp;
            
            // Log untuk debugging
            \Log::debug('Profile image URL generated', [
                'user_id' => $this->id,
                'filename' => $this->profile_image,
                'base_url' => $baseUrl,
                'cache_busting_url' => $cacheBustingUrl,
                'timestamp' => $timestamp
            ]);
            
            return $cacheBustingUrl;
        }
        return null;
    }

    // Helper method untuk check apakah profile image adalah GIF
    public function getIsProfileImageGifAttribute()
    {
        if ($this->profile_image) {
            return strtolower(pathinfo($this->profile_image, PATHINFO_EXTENSION)) === 'gif';
        }
        return false;
    }

    // Helper method untuk get file extension
    public function getProfileImageExtensionAttribute()
    {
        if ($this->profile_image) {
            return strtolower(pathinfo($this->profile_image, PATHINFO_EXTENSION));
        }
        return null;
    }

    // Helper method untuk get file size
    public function getProfileImageSizeAttribute()
    {
        if ($this->profile_image) {
            $filePath = storage_path('app/public/profile_images/' . $this->profile_image);
            if (file_exists($filePath)) {
                return filesize($filePath);
            }
        }
        return null;
    }

    // Helper method untuk check if image file exists
    public function getProfileImageExistsAttribute()
    {
        if ($this->profile_image) {
            return Storage::exists('public/profile_images/' . $this->profile_image);
        }
        return false;
    }

    // Method untuk get raw URL tanpa cache busting (untuk internal use)
    public function getRawProfileImageUrl()
    {
        if ($this->profile_image) {
            return url('storage/profile_images/' . $this->profile_image);
        }
        return null;
    }

    // Method untuk force generate new cache busting URL
    public function getFreshProfileImageUrl()
    {
        if ($this->profile_image) {
            $baseUrl = url('storage/profile_images/' . $this->profile_image);
            $currentTimestamp = time();
            return $baseUrl . '?v=' . $currentTimestamp;
        }
        return null;
    }

        public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteSeries()
    {
        return $this->belongsToMany(Series::class, 'favorites');
    }
}
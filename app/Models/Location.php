<?php
// app/Models/Location.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'latitude',
        'longitude',
        'address',
        'image_url',
        'phone_number',
        'website',
        'opening_hours',
        'rating',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['full_image_url'];

    public function getFullImageUrlAttribute()
    {
        if ($this->image_url) {
            return url('storage/locations/' . $this->image_url);
        }
        return null;
    }

    // Relationship with users who favorited this location
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'location_favorites');
    }

    // Check if location is favorited by specific user
    public function isFavoritedBy($userId): bool
    {
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    // Scope for active locations
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for filtering by category
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Scope for nearby locations (within radius in kilometers)
    public function scopeNearby($query, $latitude, $longitude, $radiusKm = 10)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        return $query->selectRaw("
                *,
                (
                    {$earthRadius} * acos(
                        cos(radians(?))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(latitude))
                    )
                ) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    // Calculate distance from given coordinates
    public function distanceFrom($latitude, $longitude)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $latDelta = deg2rad($this->latitude - $latitude);
        $lonDelta = deg2rad($this->longitude - $longitude);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($latitude)) * cos(deg2rad($this->latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}

// app/Models/LocationFavorite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
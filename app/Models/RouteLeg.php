<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteLeg extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'route_id',
        'leg_number',
        'from_location',
        'to_location',
        'from_latitude',
        'from_longitude',
        'to_latitude',
        'to_longitude',
        'distance_km',
        'duration_minutes',
        'calculation_method',
        'calculated_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'leg_number' => 'integer',
        'from_latitude' => 'decimal:8',
        'from_longitude' => 'decimal:8',
        'to_latitude' => 'decimal:8',
        'to_longitude' => 'decimal:8',
        'distance_km' => 'decimal:2',
        'duration_minutes' => 'integer',
        'calculated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',

    ];

    /**
     * Get the route that owns the leg.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
    public function fromStop()
{
    return $this->belongsTo(RouteStop::class, 'from_stop_id');
}

public function toStop()
{
    return $this->belongsTo(RouteStop::class, 'to_stop_id');
}

    /**
     * Check if leg was calculated using Google Maps API.
     */
    public function isCalculatedByGoogleMaps(): bool
    {
        return $this->calculation_method === 'google_maps_api';
    }

    /**
     * Check if leg was manually entered.
     */
    public function isManualEntry(): bool
    {
        return $this->calculation_method === 'manual';
    }

    /**
     * Get the display name for this leg.
     */
    public function getDisplayNameAttribute(): string
    {
        return "Leg {$this->leg_number}: {$this->from_location} → {$this->to_location}";
    }

    /**
     * Get formatted distance with unit.
     */
    public function getFormattedDistanceAttribute(): string
    {
        return number_format($this->distance_km, 2) . ' km';
    }

    /**
     * Get formatted duration in hours and minutes.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }
}

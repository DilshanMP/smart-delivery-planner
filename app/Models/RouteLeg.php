<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteLeg extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'route_legs_new';

    protected $fillable = [
        'route_id',
        'leg_number',
        'from_stop_id',
        'to_stop_id',
        'from_location',
        'to_location',
        'from_latitude',
        'from_longitude',
        'to_latitude',
        'to_longitude',
        'distance_km',
        'duration_minutes',
        'calculation_method', // google_maps, haversine, manual
        'calculated_at',
        'google_maps_route_json',
    ];

    protected $casts = [
        'from_latitude' => 'decimal:8',
        'from_longitude' => 'decimal:8',
        'to_latitude' => 'decimal:8',
        'to_longitude' => 'decimal:8',
        'distance_km' => 'decimal:2',
        'duration_minutes' => 'integer',
        'calculated_at' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
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
     * ACCESSORS
     */

    public function getDisplayNameAttribute()
    {
        return "Leg {$this->leg_number}: {$this->from_location} → {$this->to_location}";
    }

    public function getFormattedDistanceAttribute()
    {
        return number_format($this->distance_km, 2) . ' km';
    }

    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    public function isCalculatedByGoogleMaps()
    {
        return $this->calculation_method === 'google_maps';
    }
}

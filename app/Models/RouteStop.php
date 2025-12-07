<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteStop extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'route_id',
        'sequence',
        'customer_name',
        'delivery_address',
        'latitude',
        'longitude',
        'sale_value',
        'special_instructions',
        'delivered_at',
        'delivery_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sequence' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'sale_value' => 'decimal:2',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',

    ];

    /**
     * Get the route that owns the stop.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }


    /**
     * Get the full address with GPS coordinates.
     */
    public function getFullLocationAttribute(): string
    {
        $location = $this->delivery_address;
        if ($this->latitude && $this->longitude) {
            $location .= " (GPS: {$this->latitude}, {$this->longitude})";
        }
        return $location;
    }

    /**
     * Check if stop has GPS coordinates.
     */
    public function hasGpsCoordinates(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Check if stop has been delivered.
     */
    public function isDelivered(): bool
    {
        return !is_null($this->delivered_at);
    }

    /**
     * Mark stop as delivered.
     */
    public function markAsDelivered(?string $notes = null): void
    {
        $this->update([
            'delivered_at' => now(),
            'delivery_notes' => $notes,
        ]);
    }

    /**
     * Get the display name for this stop.
     */
    public function getDisplayNameAttribute(): string
    {
        return "Stop {$this->sequence}: {$this->customer_name}";
    }
}

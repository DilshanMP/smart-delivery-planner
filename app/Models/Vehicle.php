<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'registration_number',
        'vehicle_type',
        'make_model',
        'fuel_efficiency_kmpl',
        'capacity_kg',
        'year',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fuel_efficiency_kmpl' => 'decimal:2',
        'capacity_kg' => 'decimal:2',
        'year' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the company that owns the vehicle.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the driver assigned to this vehicle (exclusive 1:1).
     */
    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    /**
     * Get the routes assigned to this vehicle.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the vehicle conditions for this vehicle.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(VehicleCondition::class);
    }

    /**
     * Get the latest condition record.
     */
    public function latestCondition(): ?VehicleCondition
    {
        return $this->conditions()->latest('inspection_date')->first();
    }

    /**
     * Scope a query to only include active vehicles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to filter by vehicle type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Get the vehicle's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->registration_number} ({$this->vehicle_type})";
    }

    /**
     * Check if vehicle has an assigned driver.
     */
    public function hasDriver(): bool
    {
        return !is_null($this->driver);
    }

    /**
     * Check if vehicle needs maintenance based on latest condition.
     */
    public function needsMaintenance(): bool
    {
        $latest = $this->latestCondition();
        return $latest && $latest->needs_maintenance;
    }
    public function activeRoutes()
{
    return $this->hasMany(Route::class)
                ->whereIn('status', ['pending', 'in_progress']);
}

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'vehicle_id',
        'name',
        'license_number',
        'contact_number',
        'email',
        'years_of_experience',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'years_of_experience' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the company that owns the driver.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the vehicle assigned to this driver (exclusive 1:1).
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the routes assigned to this driver.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the performance logs for this driver.
     */
    public function performanceLogs(): HasMany
    {
        return $this->hasMany(DriverPerformanceLog::class);
    }

    /**
     * Get the latest performance log.
     */
    public function latestPerformance(): ?DriverPerformanceLog
    {
        return $this->performanceLogs()->latest('year')->latest('month')->first();
    }

    /**
     * Scope a query to only include active drivers.
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
     * Get the driver's display name with vehicle.
     */
    public function getDisplayNameAttribute(): string
    {
        $display = $this->name;
        if ($this->vehicle) {
            $display .= " ({$this->vehicle->registration_number})";
        }
        return $display;
    }

    /**
     * Check if driver has an assigned vehicle.
     */
    public function hasVehicle(): bool
    {
        return !is_null($this->vehicle_id);
    }

public function activeRoutes()
{
    return $this->hasMany(Route::class)
                ->whereIn('status', ['pending', 'in_progress']);
}




    /**
     * Get driver's total completed routes.
     */
    public function getTotalCompletedRoutesAttribute(): int
    {
        return $this->routes()->where('status', 'completed')->count();
    }

    /**
     * Get driver's on-time delivery percentage.
     */
    public function getOnTimePercentageAttribute(): float
    {
        $latest = $this->latestPerformance();
        return $latest ? $latest->on_time_percentage : 0;
    }

}

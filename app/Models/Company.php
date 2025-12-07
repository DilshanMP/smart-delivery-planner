<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'address',
        'contact_person',
        'contact_number',
        'email',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the warehouses for this company.
     */
    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    /**
     * Get the vehicles for this company.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the drivers for this company.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    /**
     * Get the routes for this company.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the users for this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the company's active warehouses.
     */
    public function activeWarehouses()
    {
        return $this->warehouses()->where('is_active', true);
    }

    /**
     * Get the company's active vehicles.
     */
    public function activeVehicles()
    {
        return $this->vehicles()->where('is_active', true);
    }

    /**
     * Get the company's active drivers.
     */
    public function activeDrivers()
    {
        return $this->drivers()->where('is_active', true);
    }

    /**
     * Get total routes for this company.
     */
    public function getTotalRoutesAttribute(): int
    {
        return $this->routes()->count();
    }

    /**
     * Get completed routes for this company.
     */
    public function getCompletedRoutesAttribute(): int
    {
        return $this->routes()->where('status', 'completed')->count();
    }
}

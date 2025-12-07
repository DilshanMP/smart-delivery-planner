<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleCondition extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'condition_rating',
        'odometer_reading',
        'inspection_notes',
        'needs_maintenance',
        'maintenance_required',
        'next_service_due',
        'inspection_date',
        'inspected_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'odometer_reading' => 'integer',
        'needs_maintenance' => 'boolean',
        'next_service_due' => 'date',
        'inspection_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the condition record.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who inspected the vehicle.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Scope a query to filter by condition rating.
     */
    public function scopeByCondition($query, $rating)
    {
        return $query->where('condition_rating', $rating);
    }

    /**
     * Scope a query to only include vehicles needing maintenance.
     */
    public function scopeNeedingMaintenance($query)
    {
        return $query->where('needs_maintenance', true);
    }

    /**
     * Get formatted condition rating label.
     */
    public function getConditionRatingLabelAttribute(): string
    {
        return match($this->condition_rating) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
            default => 'Unknown',
        };
    }

    /**
     * Get condition rating color for UI.
     */
    public function getConditionColorAttribute(): string
    {
        return match($this->condition_rating) {
            'excellent' => 'success',
            'good' => 'primary',
            'fair' => 'warning',
            'poor' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if service is overdue.
     */
    public function isServiceOverdue(): bool
    {
        if (is_null($this->next_service_due)) {
            return false;
        }
        return $this->next_service_due->isPast();
    }

    /**
     * Get days until next service.
     */
    public function getDaysUntilServiceAttribute(): ?int
    {
        if (is_null($this->next_service_due)) {
            return null;
        }
        return now()->diffInDays($this->next_service_due, false);
    }
}

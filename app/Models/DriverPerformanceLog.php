<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverPerformanceLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'driver_id',
        'year',
        'month',
        'total_routes',
        'total_distance_km',
        'total_deliveries',
        'on_time_deliveries',
        'on_time_percentage',
        'average_cost_variance',
        'incidents',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_routes' => 'integer',
        'total_distance_km' => 'decimal:2',
        'total_deliveries' => 'integer',
        'on_time_deliveries' => 'integer',
        'on_time_percentage' => 'decimal:2',
        'average_cost_variance' => 'decimal:2',
        'incidents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the driver that owns the performance log.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Calculate and update on-time percentage.
     */
    public function calculateOnTimePercentage(): void
    {
        if ($this->total_deliveries == 0) {
            $this->update(['on_time_percentage' => 0]);
            return;
        }

        $percentage = ($this->on_time_deliveries / $this->total_deliveries) * 100;
        $this->update(['on_time_percentage' => $percentage]);
    }

    /**
     * Scope a query to filter by driver.
     */
    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Get the month name.
     */
    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    /**
     * Get the period label (e.g., "January 2024").
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }

    /**
     * Get performance rating based on on-time percentage.
     */
    public function getPerformanceRatingAttribute(): string
    {
        if ($this->on_time_percentage >= 95) {
            return 'Excellent';
        } elseif ($this->on_time_percentage >= 85) {
            return 'Good';
        } elseif ($this->on_time_percentage >= 75) {
            return 'Fair';
        } else {
            return 'Needs Improvement';
        }
    }

    /**
     * Get performance color for UI.
     */
    public function getPerformanceColorAttribute(): string
    {
        if ($this->on_time_percentage >= 95) {
            return 'success';
        } elseif ($this->on_time_percentage >= 85) {
            return 'primary';
        } elseif ($this->on_time_percentage >= 75) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Check if driver had any incidents.
     */
    public function hasIncidents(): bool
    {
        return $this->incidents > 0;
    }
}

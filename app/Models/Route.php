<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'warehouse_id',
        'vehicle_id',
        'driver_id',
        'route_code',
        'delivery_date',
        'delivery_type',
        'status',
        'total_distance_km',
        'total_duration_minutes',
        'estimated_cost',
        'actual_cost',
        'cost_variance',
        'cost_variance_percentage',
          'estimated_distance_km',   // << add
            'actual_distance_km',
        'started_at',
        'completed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    'delivery_date' => 'date',
    'estimated_distance_km' => 'decimal:2',
    'actual_distance_km' => 'decimal:2',
    'total_distance_km' => 'decimal:2',
    'total_duration_minutes' => 'integer',
    'estimated_cost' => 'decimal:2',
    'actual_cost' => 'decimal:2',
    'cost_variance' => 'decimal:2',
    'cost_variance_percentage' => 'decimal:2',
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',

    ];

    /**
     * Get the company that owns the route.
     */
  public function company()
{
    return $this->belongsTo(Company::class);
}

public function driver()
{
    return $this->belongsTo(Driver::class);
}

public function vehicle()
{
    return $this->belongsTo(Vehicle::class);
}

public function stops()
{
    return $this->hasMany(RouteStop::class)->orderBy('sequence');
}
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }



public function legs()
{
    return $this->hasMany(RouteLeg::class);
}

    /**
     * Get the cost items for this route.
     */
    public function costItems(): HasMany
    {
        return $this->hasMany(CostItem::class);
    }

    /**
     * Get the AI predictions for this route.
     */
    public function aiPredictions(): HasMany
    {
        return $this->hasMany(AiPrediction::class);
    }

    /**
     * Scope a query to only include active routes.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'in_progress']);
    }

    /**
     * Scope a query to only include completed routes.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to filter by delivery type.
     */
    public function scopeByDeliveryType($query, $type)
    {
        return $query->where('delivery_type', $type);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('delivery_date', [$startDate, $endDate]);
    }

    /**
     * Calculate and update total distance from all legs.
     */
    public function calculateTotalDistance(): float
    {
        $totalDistance = $this->legs()->sum('distance_km');
        $this->update(['total_distance_km' => $totalDistance]);
        return $totalDistance;
    }

    /**
     * Calculate and update total duration from all legs.
     */
    public function calculateTotalDuration(): int
    {
        $totalDuration = $this->legs()->sum('duration_minutes');
        $this->update(['total_duration_minutes' => $totalDuration]);
        return $totalDuration;
    }

    /**
     * Calculate and update actual cost from all cost items.
     */
    public function calculateTotalActualCost(): float
    {
        $totalActualCost = $this->costItems()->sum('actual_cost');
        $this->update(['actual_cost' => $totalActualCost]);
        return $totalActualCost;
    }

    /**
     * Calculate and update cost variance.
     */
    public function calculateCostVariance(): void
    {
        $variance = $this->actual_cost - $this->estimated_cost;
        $variancePercentage = $this->estimated_cost > 0
            ? ($variance / $this->estimated_cost) * 100
            : 0;

        $this->update([
            'cost_variance' => $variance,
            'cost_variance_percentage' => $variancePercentage,
        ]);
    }

    /**
     * Mark route as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark route as completed.
     */
    public function markAsCompleted(): void
    {
        $this->calculateTotalActualCost();
        $this->calculateCostVariance();

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Get the total sale value from all stops.
     */
    public function getTotalSaleValueAttribute(): float
    {
        return $this->stops()->sum('sale_value') ?? 0;
    }

    /**
     * Get the number of stops.
     */
    public function getStopCountAttribute(): int
    {
        return $this->stops()->count();
    }

    /**
     * Get the latest AI prediction for this route.
     */
    public function latestPrediction(): ?AiPrediction
    {
        return $this->aiPredictions()->latest()->first();
    }

    /**
     * Check if route is overbudget.
     */
    public function isOverbudget(): bool
    {
        return $this->actual_cost > $this->estimated_cost;
    }

    /**
     * Get formatted route status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planned' => 'Planned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted delivery type.
     */
    public function getDeliveryTypeLabelAttribute(): string
    {
        return match($this->delivery_type) {
            'own' => 'Own Company',
            'outside' => 'External Client',
            default => 'Unknown',
        };
    }

}

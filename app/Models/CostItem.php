<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'route_id',
        'item_type',
        'description',
        'estimated_cost',
        'actual_cost',
        'variance',
        'receipt_number',
        'incurred_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'variance' => 'decimal:2',
        'incurred_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the route that owns the cost item.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Calculate and update variance.
     */
    public function calculateVariance(): void
    {
        $variance = $this->actual_cost - $this->estimated_cost;
        $this->update(['variance' => $variance]);
    }

    /**
     * Scope a query to filter by item type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    /**
     * Check if cost item is over budget.
     */
    public function isOverbudget(): bool
    {
        return $this->actual_cost > $this->estimated_cost;
    }

    /**
     * Get formatted item type label.
     */
    public function getItemTypeLabelAttribute(): string
    {
        return match($this->item_type) {
            'fuel' => 'Fuel',
            'meals' => 'Meals',
            'tolls' => 'Tolls',
            'lodging' => 'Lodging',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    /**
     * Get variance percentage.
     */
    public function getVariancePercentageAttribute(): float
    {
        if ($this->estimated_cost == 0) {
            return 0;
        }
        return ($this->variance / $this->estimated_cost) * 100;
    }
}

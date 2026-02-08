<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'route_id',
        'cost_type', // fuel, meal, accommodation, toll, parking, maintenance, other
        'description',
        'estimated_amount',
        'actual_amount',
        'receipt_number',
        'receipt_file_path', // For uploading bills (Step 4 optional)
        'expense_date',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    /**
     * RELATIONSHIPS
     */

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * ACCESSORS
     */

    public function getCostTypeLabelAttribute()
    {
        return match($this->cost_type) {
            'fuel' => 'Fuel',
            'meal' => 'Meal / Refreshment',
            'accommodation' => 'Accommodation',
            'toll' => 'Toll Charges',
            'parking' => 'Parking',
            'maintenance' => 'Maintenance',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    public function getVarianceAttribute()
    {
        if ($this->actual_amount && $this->estimated_amount) {
            return $this->actual_amount - $this->estimated_amount;
        }
        return null;
    }

    public function hasReceipt()
    {
        return !empty($this->receipt_file_path);
    }
}

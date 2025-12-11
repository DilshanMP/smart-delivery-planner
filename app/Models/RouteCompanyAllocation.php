<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteCompanyAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'company_id',
        'total_sales_value',
        'total_sales_qty',
        'number_of_stops',
        'allocated_cost',
        'allocation_percentage',
        'profit',
        'profit_margin_percentage',
    ];

    protected $casts = [
        'total_sales_value' => 'decimal:2',
        'total_sales_qty' => 'integer',
        'number_of_stops' => 'integer',
        'allocated_cost' => 'decimal:2',
        'allocation_percentage' => 'decimal:2',
        'profit' => 'decimal:2',
        'profit_margin_percentage' => 'decimal:2',
    ];

    /**
     * RELATIONSHIPS
     */

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * ACCESSORS
     */

    public function getIsProfitableAttribute()
    {
        return $this->profit > 0;
    }

    public function getFormattedAllocationAttribute()
    {
        return number_format($this->allocation_percentage, 1) . '%';
    }

    public function getFormattedProfitMarginAttribute()
    {
        return number_format($this->profit_margin_percentage, 1) . '%';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Related models (make sure these files/classes exist)
use App\Models\Company;
use App\Models\Warehouse;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\RouteStop;
use App\Models\RouteLeg;
use App\Models\RouteCostItem;
use App\Models\RouteCompanyAllocation;
use App\Models\RouteOptimization;
use App\Models\AIPredictionHistory;
use App\Services\AIService;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'routes_new'; // Using new table structure

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Planning (Step 1)
        'route_date',
        'company_id',
        'warehouse_id',
        'driver_id',
        'vehicle_id',
        'route_code',
        'start_time',
        'end_time',
        'delivery_type',

        // Estimated Calculations (Step 2)
        'estimated_distance_km',
        'estimated_fuel_rate_per_km',
        'estimated_fuel_rate_per_litre',
        'estimated_fuel_cost',
        'estimated_meal_cost',
        'estimated_accommodation_cost',
        'estimated_days',
        'estimated_total_cost',

        // Actual Completion (Step 4)
        'actual_start_km',
        'actual_end_km',
        'actual_distance_km',
        'actual_fuel_cost',
        'actual_meal_cost',
        'actual_accommodation_cost',
        'actual_other_costs',
        'actual_total_cost',

        // Variance Tracking (Step 6)
        'km_variance',
        'cost_variance',
        'cost_variance_percentage',

        // Returns & Closure (Step 5)
        'return_sales_value',
        'is_completed',
        'status',

        // Tracking
        'started_at',
        'completed_at',
        'notes',

        // AI Prediction Fields (NEW)
        'ai_predicted_cost',
        'ai_cost_percentage',
        'ai_confidence',
        'ai_lower_bound',
        'ai_upper_bound',
        'ai_recommendation',
        'ai_predicted_at',
        'actual_vs_predicted_difference',
        'prediction_accuracy_percentage',

        // Route Optimization Fields (NEW)
        'is_optimized',
        'optimization_algorithm',
        'distance_saved_km',
        'improvement_percentage',
        'optimization_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'route_date' => 'date',
        // stored as TIME in DB — casting to string is safe
        'start_time' => 'string',
        'end_time' => 'string',

        'estimated_distance_km' => 'decimal:2',
        'estimated_fuel_rate_per_km' => 'decimal:2',
        'estimated_fuel_rate_per_litre' => 'decimal:2',
        'estimated_fuel_cost' => 'decimal:2',
        'estimated_meal_cost' => 'decimal:2',
        'estimated_accommodation_cost' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',

        'actual_start_km' => 'decimal:2',
        'actual_end_km' => 'decimal:2',
        'actual_distance_km' => 'decimal:2',
        'actual_fuel_cost' => 'decimal:2',
        'actual_meal_cost' => 'decimal:2',
        'actual_accommodation_cost' => 'decimal:2',
        'actual_other_costs' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',

        'km_variance' => 'decimal:2',
        'cost_variance' => 'decimal:2',
        'cost_variance_percentage' => 'decimal:2',

        'return_sales_value' => 'decimal:2',
        'is_completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',

        // AI Prediction Casts (NEW)
        'ai_predicted_cost' => 'decimal:2',
        'ai_cost_percentage' => 'decimal:2',
        'ai_confidence' => 'decimal:2',
        'ai_lower_bound' => 'decimal:2',
        'ai_upper_bound' => 'decimal:2',
        'actual_vs_predicted_difference' => 'decimal:2',
        'prediction_accuracy_percentage' => 'decimal:2',
        'ai_predicted_at' => 'datetime',

        // Optimization Casts (NEW)
        'is_optimized' => 'boolean',
        'distance_saved_km' => 'decimal:2',
        'improvement_percentage' => 'decimal:2',
        'optimization_date' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Note: migration uses 'stop_sequence' so keep that column name
    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id')->orderBy('stop_sequence');
    }

    public function legs()
    {
        return $this->hasMany(RouteLeg::class, 'route_id')->orderBy('leg_number');
    }

    public function costItems()
    {
        return $this->hasMany(CostItem::class, 'route_id');
    }

    public function companyAllocations()
    {
        return $this->hasMany(RouteCompanyAllocation::class, 'route_id');
    }

    /**
     * AI Prediction Relationships (NEW)
     */
    public function aiPredictions()
    {
        return $this->hasMany(AIPredictionHistory::class);
    }

    public function latestAiPrediction()
    {
        return $this->hasOne(AIPredictionHistory::class)->latestOfMany();
    }

    /**
     * Route Optimization Relationships (NEW)
     */
    public function optimizations()
    {
        return $this->hasMany(RouteOptimization::class);
    }

    public function latestOptimization()
    {
        return $this->hasOne(RouteOptimization::class)->latestOfMany();
    }

    /**
     * SCOPES
     */

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('route_date', [$startDate, $endDate]);
    }

    /**
     * AI Prediction Scopes (NEW)
     */
    public function scopeWithAiPrediction($query)
    {
        return $query->whereNotNull('ai_predicted_cost');
    }

    public function scopeOptimized($query)
    {
        return $query->where('is_optimized', true);
    }

    /**
     * CALCULATION METHODS - Step 2: Estimated Calculations
     */
    public function calculateEstimatedCosts()
    {
        // defensive check: vehicle may be null
        $fuelEfficiency = optional($this->vehicle)->fuel_efficiency ?? 10; // km per litre

        // Fuel cost based on distance
        if ($this->estimated_distance_km) {
            $litresNeeded = $this->estimated_distance_km / max(1, $fuelEfficiency);
            $this->estimated_fuel_cost = $litresNeeded * ($this->estimated_fuel_rate_per_litre ?? 350);
        }

        // Meal cost per day
        if (!$this->estimated_meal_cost && $this->estimated_days) {
            $this->estimated_meal_cost = 1500 * $this->estimated_days; // LKR 1500/day default
        }

        // Accommodation cost per night (if multi-day)
        if (($this->estimated_days ?? 0) > 1 && !$this->estimated_accommodation_cost) {
            $this->estimated_accommodation_cost = 3000 * (max(0, $this->estimated_days - 1)); // LKR 3000/night
        }

        // Total estimated cost
        $this->estimated_total_cost =
            ($this->estimated_fuel_cost ?? 0) +
            ($this->estimated_meal_cost ?? 0) +
            ($this->estimated_accommodation_cost ?? 0);

        $this->save();

        return $this->estimated_total_cost;
    }

    /**
     * ACTUAL COMPLETION - Step 4
     */
    public function recordActualCompletion(array $data)
    {
        $this->actual_start_km = $data['actual_start_km'] ?? $this->actual_start_km;
        $this->actual_end_km = $data['actual_end_km'] ?? $this->actual_end_km;
        $this->actual_distance_km = null;
        if (is_numeric($this->actual_end_km) && is_numeric($this->actual_start_km)) {
            $this->actual_distance_km = $this->actual_end_km - $this->actual_start_km;
        }

        // If actual costs not provided, default to estimated
        $this->actual_fuel_cost = $data['actual_fuel_cost'] ?? $this->estimated_fuel_cost ?? 0;
        $this->actual_meal_cost = $data['actual_meal_cost'] ?? $this->estimated_meal_cost ?? 0;
        $this->actual_accommodation_cost = $data['actual_accommodation_cost'] ?? $this->estimated_accommodation_cost ?? 0;
        $this->actual_other_costs = $data['actual_other_costs'] ?? 0;

        $this->actual_total_cost =
            ($this->actual_fuel_cost ?? 0) +
            ($this->actual_meal_cost ?? 0) +
            ($this->actual_accommodation_cost ?? 0) +
            ($this->actual_other_costs ?? 0);

        $this->save();
        $this->calculateVariances();

        // Calculate AI prediction accuracy if prediction exists (NEW)
        if ($this->ai_predicted_cost) {
            $this->calculatePredictionAccuracy();
        }
    }

    /**
     * VARIANCE CALCULATION - Step 6
     */
    public function calculateVariances()
    {
        // KM Variance
        if (is_numeric($this->actual_distance_km) && is_numeric($this->estimated_distance_km)) {
            $this->km_variance = $this->actual_distance_km - $this->estimated_distance_km;
        } else {
            $this->km_variance = null;
        }

        // Cost Variance
        if (is_numeric($this->actual_total_cost) && is_numeric($this->estimated_total_cost)) {
            $this->cost_variance = $this->actual_total_cost - $this->estimated_total_cost;

            if ($this->estimated_total_cost > 0) {
                $this->cost_variance_percentage = ($this->cost_variance / $this->estimated_total_cost) * 100;
            } else {
                $this->cost_variance_percentage = null;
            }
        } else {
            $this->cost_variance = null;
            $this->cost_variance_percentage = null;
        }

        $this->save();
    }

    /**
     * AI PREDICTION ACCURACY CALCULATION (NEW)
     */
    public function calculatePredictionAccuracy()
    {
        if (!$this->ai_predicted_cost || !$this->actual_total_cost) {
            return null;
        }

        $this->actual_vs_predicted_difference = $this->actual_total_cost - $this->ai_predicted_cost;

        $this->prediction_accuracy_percentage =
            (1 - abs($this->actual_vs_predicted_difference / $this->ai_predicted_cost)) * 100;

        $this->save();

        // Update history table too
        $latestPrediction = $this->latestAiPrediction;
        if ($latestPrediction) {
            $latestPrediction->update([
                'actual_cost' => $this->actual_total_cost,
                'accuracy_percentage' => $this->prediction_accuracy_percentage
            ]);
        }

        return $this->prediction_accuracy_percentage;
    }

    /**
     * COMPANY ALLOCATIONS - Step 3: Multi-Company Sales
     */
    public function calculateCompanyAllocations()
    {
        // Get all stops grouped by company
        $companyStops = $this->stops()
            ->selectRaw('sales_company_id,
                         SUM(sales_value) as total_sales,
                         SUM(sales_qty) as total_qty,
                         COUNT(*) as stop_count')
            ->whereNotNull('sales_company_id')
            ->groupBy('sales_company_id')
            ->get();

        $totalSales = $companyStops->sum('total_sales');
        $totalCost = $this->actual_total_cost ?? $this->estimated_total_cost ?? 0;

        foreach ($companyStops as $companyData) {
            // Calculate allocation percentage based on sales value
            $allocationPercentage = $totalSales > 0
                ? ($companyData->total_sales / $totalSales) * 100
                : 0;

            $allocatedCost = ($allocationPercentage / 100) * $totalCost;
            $profit = $companyData->total_sales - $allocatedCost;
            $profitMargin = $companyData->total_sales > 0
                ? ($profit / $companyData->total_sales) * 100
                : 0;

            RouteCompanyAllocation::updateOrCreate(
                [
                    'route_id' => $this->id,
                    'company_id' => $companyData->sales_company_id
                ],
                [
                    'total_sales_value' => $companyData->total_sales,
                    'total_sales_qty' => $companyData->total_qty,
                    'number_of_stops' => $companyData->stop_count,
                    'allocated_cost' => $allocatedCost,
                    'allocation_percentage' => $allocationPercentage,
                    'profit' => $profit,
                    'profit_margin_percentage' => $profitMargin,
                ]
            );
        }
    }

    /**
     * ROUTE STATUS MANAGEMENT
     */

    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted($returnSalesValue = 0)
    {
        $this->update([
            'status' => 'completed',
            'is_completed' => true,
            'completed_at' => now(),
            'return_sales_value' => $returnSalesValue,
        ]);

        $this->calculateVariances();
        $this->calculateCompanyAllocations();

        // Calculate AI accuracy if prediction exists (NEW)
        if ($this->ai_predicted_cost) {
            $this->calculatePredictionAccuracy();
        }
    }

    /**
     * AI COST PREDICTION (NEW)
     */
    public function predictCost()
    {
        $aiService = app(AIService::class);

        // Get vehicle type safely
        $vehicleType = optional($this->vehicle)->vehicle_type ?? 'Small Lorry';

        // Get day of week
        $dayOfWeek = $this->route_date ? $this->route_date->format('l') : 'Monday';

        // Calculate total sales from stops
        $totalSales = $this->stops()->sum('sales_value');

        $prediction = $aiService->predictCost([
            'total_distance_km' => $this->estimated_distance_km ?? 0,
            'total_stops' => $this->stops()->count(),
            'num_companies' => $this->companyAllocations()->count(),
            'total_sales_value' => $totalSales,
            'vehicle_type' => $vehicleType,
            'day_of_week' => $dayOfWeek
        ]);

        $this->ai_predicted_cost = $prediction['cost'];
        $this->ai_cost_percentage = $prediction['cost_percentage'];
        $this->ai_confidence = $prediction['model_confidence'] ?? null;
        $this->ai_lower_bound = $prediction['confidence_interval']['lower'] ?? null;
        $this->ai_upper_bound = $prediction['confidence_interval']['upper'] ?? null;
        $this->ai_predicted_at = now();

        // Determine recommendation based on cost percentage
        $standardCost = 0.60; // 0.60% standard
        if ($this->ai_cost_percentage <= $standardCost) {
            $this->ai_recommendation = 'GOOD';
        } elseif ($this->ai_cost_percentage <= $standardCost * 1.5) {
            $this->ai_recommendation = 'WARNING';
        } else {
            $this->ai_recommendation = 'DANGER';
        }

        $this->save();

        return $prediction;
    }

    /**
     * POWER BI / REPORTING HELPERS - Step 6
     */

    public function getTotalSalesValue()
    {
        return $this->stops()->sum('sales_value');
    }

    public function getTotalSalesQty()
    {
        return $this->stops()->sum('sales_qty');
    }

    public function getNetSalesValue()
    {
        return $this->getTotalSalesValue() - ($this->return_sales_value ?? 0);
    }

    public function getRouteProfitability()
    {
        $netSales = $this->getNetSalesValue();
        $actualCost = $this->actual_total_cost ?? $this->estimated_total_cost ?? 0;

        return [
            'total_sales' => $this->getTotalSalesValue(),
            'return_sales' => $this->return_sales_value,
            'net_sales' => $netSales,
            'total_cost' => $actualCost,
            'profit' => $netSales - $actualCost,
            'profit_margin' => $netSales > 0 ? (($netSales - $actualCost) / $netSales) * 100 : 0,
        ];
    }

    /**
     * ACCESSORS / ATTRIBUTES
     */

    public function getEstimatedVsActualAttribute()
    {
        return [
            'distance' => [
                'estimated' => $this->estimated_distance_km,
                'actual' => $this->actual_distance_km,
                'variance' => $this->km_variance,
            ],
            'cost' => [
                'estimated' => $this->estimated_total_cost,
                'actual' => $this->actual_total_cost,
                'variance' => $this->cost_variance,
                'variance_percentage' => $this->cost_variance_percentage,
            ],
        ];
    }

    public function getIsOverbudgetAttribute()
    {
        return ($this->cost_variance ?? 0) > 0;
    }

    public function getStatusLabelAttribute()
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
     * AI Prediction Accessors (NEW)
     */
    public function getHasAiPredictionAttribute()
    {
        return !is_null($this->ai_predicted_cost);
    }

    public function getAiRecommendationBadgeAttribute()
    {
        return match($this->ai_recommendation) {
            'GOOD' => 'success',
            'WARNING' => 'warning',
            'DANGER' => 'danger',
            default => 'secondary',
        };
    }

    public function getIsAiAccurateAttribute()
    {
        if (!$this->prediction_accuracy_percentage) {
            return null;
        }
        return $this->prediction_accuracy_percentage >= 90;
    }
}

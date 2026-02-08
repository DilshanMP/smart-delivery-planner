<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIPredictionHistory extends Model
{
    use HasFactory;

    protected $table = 'ai_prediction_history';

    protected $fillable = [
        'route_id',
        'distance_km',
        'total_stops',
        'num_companies',
        'total_sales_value',
        'vehicle_type',
        'day_of_week',
        'predicted_cost',
        'cost_percentage',
        'confidence',
        'lower_bound',
        'upper_bound',
        'recommendation',
        'model_version',
        'full_response',
        'actual_cost',
        'accuracy_percentage',
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'total_sales_value' => 'decimal:2',
        'predicted_cost' => 'decimal:2',
        'cost_percentage' => 'decimal:2',
        'confidence' => 'decimal:2',
        'lower_bound' => 'decimal:2',
        'upper_bound' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'accuracy_percentage' => 'decimal:2',
        'full_response' => 'array',
    ];

    /**
     * Get the route
     */
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}

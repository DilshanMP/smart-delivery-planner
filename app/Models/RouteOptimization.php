<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteOptimization extends Model
{
    use HasFactory;

    protected $table = 'route_optimizations';

    protected $fillable = [
        'route_id',
        'algorithm',
        'baseline_distance',
        'optimized_distance',
        'distance_saved',
        'improvement_percentage',
        'original_route',
        'optimized_route',
    ];

    protected $casts = [
        'original_route' => 'array',
        'optimized_route' => 'array',
        'baseline_distance' => 'decimal:2',
        'optimized_distance' => 'decimal:2',
        'distance_saved' => 'decimal:2',
        'improvement_percentage' => 'decimal:2',
    ];

    /**
     * Get the route that owns the optimization
     */
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}

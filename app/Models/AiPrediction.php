<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiPrediction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'route_id',
        'prediction_type',
        'model_version',
        'input_features',
        'predicted_value',
        'actual_value',
        'prediction_accuracy',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'input_features' => 'array',
        'predicted_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'prediction_accuracy' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the route that owns the prediction.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Calculate and update prediction accuracy.
     */
    public function calculateAccuracy(): void
    {
        if (is_null($this->actual_value) || $this->actual_value == 0) {
            return;
        }

        $error = abs($this->predicted_value - $this->actual_value);
        $accuracy = 100 - (($error / $this->actual_value) * 100);

        // Ensure accuracy is between 0 and 100
        $accuracy = max(0, min(100, $accuracy));

        $this->update(['prediction_accuracy' => $accuracy]);
    }

    /**
     * Scope a query to filter by prediction type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('prediction_type', $type);
    }

    /**
     * Scope a query to filter by model version.
     */
    public function scopeByModelVersion($query, $version)
    {
        return $query->where('model_version', $version);
    }

    /**
     * Get formatted prediction type label.
     */
    public function getPredictionTypeLabelAttribute(): string
    {
        return match($this->prediction_type) {
            'cost' => 'Cost Prediction',
            'eta' => 'ETA Prediction',
            'both' => 'Cost & ETA Prediction',
            default => 'Unknown',
        };
    }

    /**
     * Get the prediction error amount.
     */
    public function getPredictionErrorAttribute(): float
    {
        if (is_null($this->actual_value)) {
            return 0;
        }
        return abs($this->predicted_value - $this->actual_value);
    }

    /**
     * Check if prediction was accurate (within threshold).
     */
    public function isAccurate($threshold = 90): bool
    {
        return $this->prediction_accuracy >= $threshold;
    }

    /**
     * Get input features as a readable string.
     */
    public function getReadableFeaturesAttribute(): string
    {
        if (empty($this->input_features)) {
            return 'No features';
        }

        $features = [];
        foreach ($this->input_features as $key => $value) {
            $features[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode(', ', $features);
    }
}

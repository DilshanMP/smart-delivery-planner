<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning auditable model (polymorphic).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to filter by auditable type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('auditable_type', $type);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'restore' => 'Restored',
            'predict' => 'Predicted',
            default => 'Unknown',
        };
    }

    /**
     * Get action color for UI.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'create' => 'success',
            'update' => 'primary',
            'delete' => 'danger',
            'restore' => 'warning',
            'predict' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get a summary of changes.
     */
    public function getChangesSummaryAttribute(): string
    {
        if ($this->action === 'create') {
            return 'New record created';
        }

        if ($this->action === 'delete') {
            return 'Record deleted';
        }

        if ($this->action === 'update' && !empty($this->old_values) && !empty($this->new_values)) {
            $changes = [];
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? 'N/A';
                if ($oldValue != $newValue) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key));
                }
            }
            return empty($changes) ? 'No changes' : implode(', ', $changes) . ' changed';
        }

        return 'Action performed';
    }

    /**
     * Get the model class name without namespace.
     */
    public function getModelNameAttribute(): string
    {
        return class_basename($this->auditable_type);
    }
}

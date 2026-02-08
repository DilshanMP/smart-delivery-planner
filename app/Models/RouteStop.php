<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Related models - ensure these classes exist
use App\Models\Route;
use App\Models\Company;

class RouteStop extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'route_stops_new';

    protected $fillable = [
        'route_id',
        'stop_sequence',
        'shop_name',
        'shop_address',
        'latitude',
        'longitude',
        'stop_type', // warehouse, shop, final
        'sales_value',
        'sales_qty',
        'sales_company_id', // Which company's sale (Step 3)
        'is_delivered',
        'delivered_at',
        'delivery_notes',
        'special_instructions',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'sales_value' => 'decimal:2',
        'sales_qty' => 'integer',
        'is_delivered' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    /**
     * RELATIONSHIPS
     */

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function salesCompany()
    {
        return $this->belongsTo(Company::class, 'sales_company_id');
    }

    /**
     * METHODS
     */

    public function markAsDelivered($notes = null)
    {
        $this->update([
            'is_delivered' => true,
            'delivered_at' => now(),
            'delivery_notes' => $notes,
        ]);
    }

    /**
     * ACCESSORS
     */

    public function getFullAddressAttribute()
    {
        $address = $this->shop_address;

        if (!is_null($this->latitude) && !is_null($this->longitude)) {
            $address .= " (GPS: {$this->latitude}, {$this->longitude})";
        }

        return $address;
    }

    public function getStopTypeLabelAttribute()
    {
        // Use switch for maximum compatibility
        switch ($this->stop_type) {
            case 'warehouse':
                return 'Warehouse (Start)';
            case 'shop':
                return 'Shop Visit';
            case 'final':
                return 'Final Stop';
            default:
                return 'Stop';
        }
    }
}

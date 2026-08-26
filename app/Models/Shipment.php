<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'order_id',
        'origin_warehouse_id',
        'destination_warehouse_id',
        'route_id',
        'shipment_number',
        'tracking_number',
        'carrier_type',
        'carrier_name',
        'temperature_controlled',
        'target_temp_celsius',
        'status',
        'estimated_departure',
        'actual_departure',
        'estimated_arrival',
        'actual_arrival',
        'special_instructions',
        'timeline_events',
    ];

    protected $casts = [
        'temperature_controlled' => 'boolean',
        'target_temp_celsius' => 'float',
        'estimated_departure' => 'datetime',
        'actual_departure' => 'datetime',
        'estimated_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
        'timeline_events' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}

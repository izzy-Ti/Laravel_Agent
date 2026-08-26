<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'origin_warehouse_id',
        'destination_warehouse_id',
        'route_code',
        'name',
        'origin_name',
        'destination_name',
        'origin_latitude',
        'origin_longitude',
        'destination_latitude',
        'destination_longitude',
        'distance_km',
        'estimated_duration_minutes',
        'toll_cost',
        'fuel_consumption_liters',
        'waypoints',
        'risk_level',
        'status',
    ];

    protected $casts = [
        'origin_latitude' => 'float',
        'origin_longitude' => 'float',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
        'distance_km' => 'float',
        'estimated_duration_minutes' => 'integer',
        'toll_cost' => 'decimal:2',
        'fuel_consumption_liters' => 'float',
        'waypoints' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}

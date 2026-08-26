<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'latitude',
        'longitude',
        'capacity_sqft',
        'current_utilization_pct',
        'type',
        'operating_hours',
        'manager_name',
        'manager_phone',
        'manager_email',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'capacity_sqft' => 'integer',
        'current_utilization_pct' => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function outboundShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_warehouse_id');
    }

    public function inboundShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_warehouse_id');
    }
}

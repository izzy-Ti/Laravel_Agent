<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'current_driver_id',
        'vehicle_code',
        'plate_number',
        'vin',
        'make',
        'model',
        'year',
        'type',
        'max_weight_kg',
        'max_volume_cbm',
        'odometer_km',
        'fuel_type',
        'fuel_level_pct',
        'current_latitude',
        'current_longitude',
        'status',
        'last_maintenance_at',
        'next_maintenance_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'max_weight_kg' => 'float',
        'max_volume_cbm' => 'float',
        'odometer_km' => 'float',
        'fuel_level_pct' => 'float',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'last_maintenance_at' => 'date',
        'next_maintenance_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currentDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'current_driver_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}

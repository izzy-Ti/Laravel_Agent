<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'driver_code',
        'first_name',
        'last_name',
        'license_number',
        'license_type',
        'license_expiry',
        'phone',
        'emergency_contact',
        'current_latitude',
        'current_longitude',
        'status',
        'safety_score',
        'rating',
        'total_trips',
        'total_distance_km',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'safety_score' => 'float',
        'rating' => 'float',
        'total_trips' => 'integer',
        'total_distance_km' => 'float',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentVehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'current_driver_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}

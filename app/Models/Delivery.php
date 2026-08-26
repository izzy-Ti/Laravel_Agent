<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'shipment_id',
        'driver_id',
        'vehicle_id',
        'route_id',
        'delivery_number',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'delivery_city',
        'delivery_latitude',
        'delivery_longitude',
        'scheduled_window_start',
        'scheduled_window_end',
        'delivered_at',
        'proof_of_delivery_signature',
        'proof_of_delivery_photo_url',
        'customer_feedback_rating',
        'status',
        'failure_reason',
        'notes',
    ];

    protected $casts = [
        'delivery_latitude' => 'float',
        'delivery_longitude' => 'float',
        'scheduled_window_start' => 'datetime',
        'scheduled_window_end' => 'datetime',
        'delivered_at' => 'datetime',
        'customer_feedback_rating' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}

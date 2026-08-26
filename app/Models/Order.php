<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'order_number',
        'order_date',
        'required_delivery_date',
        'priority',
        'total_amount',
        'currency',
        'payment_status',
        'status',
        'items_count',
        'total_weight_kg',
        'total_volume_cbm',
        'notes',
        'order_items',
    ];

    protected $casts = [
        'order_date' => 'date',
        'required_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'items_count' => 'integer',
        'total_weight_kg' => 'float',
        'total_volume_cbm' => 'float',
        'order_items' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}

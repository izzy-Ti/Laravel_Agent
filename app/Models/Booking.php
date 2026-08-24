<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'room_type',
        'date',
        'price',
        'status',
        'special_requests',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'price' => 'decimal:2',
    ];

    /**
     * Scope to only include active/confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to check room type.
     */
    public function scopeForRoomType($query, string $roomType)
    {
        return $query->where('room_type', strtolower($roomType));
    }
}

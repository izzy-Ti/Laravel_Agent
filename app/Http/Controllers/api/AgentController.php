<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class AgentToolController extends Controller
{
    // Tool 1: Check availability
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'room_type' => 'required|string',
        ]);

        $isAvailable = !Booking::where('date', $validated['date'])
            ->where('room_type', $validated['room_type'])
            ->exists();

        return response()->json([
            'available' => $isAvailable,
            'date' => $validated['date'],
            'price' => 120.00
        ]);
    }

    // Tool 2: Create a booking
    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'date' => 'required|date',
            'room_type' => 'required|string',
        ]);

        $booking = Booking::create($validated);

        return response()->json([
            'status' => 'success',
            'booking_id' => $booking->id,
            'message' => 'Reservation confirmed.'
        ]);
    }
}
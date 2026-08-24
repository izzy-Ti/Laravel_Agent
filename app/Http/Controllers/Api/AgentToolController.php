<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Validator;

class AgentToolController extends Controller
{
    /**
     * Standard Room Catalog with descriptions, capacities, and pricing.
     */
    protected array $roomCatalog = [
        'standard' => [
            'name' => 'Standard Queen Room',
            'price' => 99.00,
            'capacity' => 2,
            'amenities' => ['Queen Bed', 'Free Wi-Fi', 'Coffee Maker', 'Air Conditioning']
        ],
        'deluxe' => [
            'name' => 'Deluxe King Room',
            'price' => 150.00,
            'capacity' => 2,
            'amenities' => ['King Bed', 'City View', 'Mini Bar', 'Smart TV', 'Free Wi-Fi']
        ],
        'suite' => [
            'name' => 'Executive Suite',
            'price' => 250.00,
            'capacity' => 4,
            'amenities' => ['Master Bedroom', 'Living Area', 'Balcony', 'Jacuzzi', 'Complimentary Breakfast']
        ],
        'penthouse' => [
            'name' => 'Presidential Penthouse',
            'price' => 500.00,
            'capacity' => 6,
            'amenities' => ['Panoramic Skyline View', 'Private Terrace', 'Personal Concierge', 'Chef Kitchen']
        ]
    ];

    /**
     * Tool 1: Check Availability
     * Checks if a room type or any room is available on a specific date.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'room_type' => 'nullable|string',
        ]);

        $date = $validated['date'];
        $requestedType = isset($validated['room_type']) ? strtolower(trim($validated['room_type'])) : null;

        // If specific room type is requested
        if ($requestedType) {
            $isBooked = Booking::where('date', $date)
                ->where('room_type', $requestedType)
                ->where('status', 'confirmed')
                ->exists();

            $roomInfo = $this->roomCatalog[$requestedType] ?? [
                'name' => ucfirst($requestedType) . ' Room',
                'price' => 120.00,
                'capacity' => 2,
                'amenities' => ['Standard Amenities']
            ];

            return response()->json([
                'tool' => 'check_availability',
                'success' => true,
                'date' => $date,
                'room_type' => $requestedType,
                'available' => !$isBooked,
                'details' => $roomInfo,
                'message' => !$isBooked
                    ? "{$roomInfo['name']} is available on {$date} at \${$roomInfo['price']}/night."
                    : "{$roomInfo['name']} is already booked on {$date}."
            ]);
        }

        // Otherwise, return availability for all room catalog types
        $bookedRooms = Booking::where('date', $date)
            ->where('status', 'confirmed')
            ->pluck('room_type')
            ->map(fn($type) => strtolower($type))
            ->toArray();

        $availability = [];
        foreach ($this->roomCatalog as $type => $info) {
            $isAvailable = !in_array($type, $bookedRooms);
            $availability[] = [
                'room_type' => $type,
                'name' => $info['name'],
                'available' => $isAvailable,
                'price' => $info['price'],
                'capacity' => $info['capacity'],
                'amenities' => $info['amenities']
            ];
        }

        return response()->json([
            'tool' => 'check_availability',
            'success' => true,
            'date' => $date,
            'total_options' => count($availability),
            'available_options' => array_values(array_filter($availability, fn($r) => $r['available'])),
            'all_rooms' => $availability
        ]);
    }

    /**
     * Tool 2: Create a Booking
     * Creates and confirms a reservation in the database.
     */
    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:150',
            'date' => 'required|date_format:Y-m-d',
            'room_type' => 'required|string',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $roomType = strtolower(trim($validated['room_type']));
        $date = $validated['date'];

        // Double check availability to prevent conflicting bookings
        $isAlreadyBooked = Booking::where('date', $date)
            ->where('room_type', $roomType)
            ->where('status', 'confirmed')
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json([
                'tool' => 'create_booking',
                'success' => false,
                'error' => 'Conflict',
                'message' => "Sorry, the {$roomType} room is already booked on {$date}. Please select another date or room type."
            ], 409);
        }

        $price = $this->roomCatalog[$roomType]['price'] ?? 120.00;

        $booking = Booking::create([
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'room_type' => $roomType,
            'date' => $date,
            'price' => $price,
            'status' => 'confirmed',
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        return response()->json([
            'tool' => 'create_booking',
            'success' => true,
            'booking_id' => $booking->id,
            'confirmation_code' => 'RES-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
            'booking' => $booking,
            'message' => "Reservation #{$booking->id} confirmed for {$booking->customer_name} on {$booking->date}."
        ], 201);
    }

    /**
     * Tool 3: Get Booking Details
     * Retrieves full booking information by Booking ID or Customer Email.
     */
    public function getBookingDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'nullable|integer',
            'customer_email' => 'nullable|email',
        ]);

        if ($validator->fails() || (!$request->has('booking_id') && !$request->has('customer_email'))) {
            return response()->json([
                'tool' => 'get_booking_details',
                'success' => false,
                'error' => 'Validation error',
                'message' => 'Please provide either booking_id (integer) or customer_email (email) to look up details.'
            ], 422);
        }

        $query = Booking::query();

        if ($request->filled('booking_id')) {
            $query->where('id', $request->input('booking_id'));
        }

        if ($request->filled('customer_email')) {
            $query->where('customer_email', $request->input('customer_email'));
        }

        $bookings = $query->orderByDesc('created_at')->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'tool' => 'get_booking_details',
                'success' => false,
                'found' => false,
                'message' => 'No reservations found matching your criteria.'
            ], 404);
        }

        return response()->json([
            'tool' => 'get_booking_details',
            'success' => true,
            'found' => true,
            'count' => $bookings->count(),
            'bookings' => $bookings
        ]);
    }

    /**
     * Tool 4: Cancel a Booking
     * Cancels an existing booking and updates its status.
     */
    public function cancelBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'reason' => 'nullable|string|max:300',
        ]);

        $booking = Booking::find($validated['booking_id']);

        if (!$booking) {
            return response()->json([
                'tool' => 'cancel_booking',
                'success' => false,
                'error' => 'Not found',
                'message' => "Booking with ID #{$validated['booking_id']} does not exist."
            ], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'tool' => 'cancel_booking',
                'success' => false,
                'message' => "Booking #{$booking->id} has already been cancelled."
            ], 400);
        }

        $booking->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'tool' => 'cancel_booking',
            'success' => true,
            'booking_id' => $booking->id,
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'] ?? 'Customer request',
            'refund_status' => 'Full refund of $' . number_format($booking->price, 2) . ' initiated.',
            'message' => "Booking #{$booking->id} for {$booking->customer_name} on {$booking->date} was successfully cancelled."
        ]);
    }

    /**
     * Agent Tool Schema Catalog
     * Returns OpenAI / Gemini / Anthropic compatible function definitions.
     */
    public function getToolsSchema()
    {
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_availability',
                    'description' => 'Check room availability and pricing for a specific date or room type.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'date' => [
                                'type' => 'string',
                                'description' => 'Target reservation date in YYYY-MM-DD format (e.g., 2026-09-01).'
                            ],
                            'room_type' => [
                                'type' => 'string',
                                'enum' => ['standard', 'deluxe', 'suite', 'penthouse'],
                                'description' => 'Optional specific room category to inspect.'
                            ]
                        ],
                        'required' => ['date']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_booking',
                    'description' => 'Reserve a hotel room for a guest on a specific date.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_name' => [
                                'type' => 'string',
                                'description' => 'Full name of the guest.'
                            ],
                            'customer_email' => [
                                'type' => 'string',
                                'description' => 'Contact email address of the guest.'
                            ],
                            'room_type' => [
                                'type' => 'string',
                                'enum' => ['standard', 'deluxe', 'suite', 'penthouse'],
                                'description' => 'Selected room category.'
                            ],
                            'date' => [
                                'type' => 'string',
                                'description' => 'Booking date in YYYY-MM-DD format.'
                            ],
                            'special_requests' => [
                                'type' => 'string',
                                'description' => 'Optional guest preferences (e.g., high floor, extra bed, early check-in).'
                            ]
                        ],
                        'required' => ['customer_name', 'customer_email', 'room_type', 'date']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_booking_details',
                    'description' => 'Look up existing reservation details by booking ID or customer email.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_id' => [
                                'type' => 'integer',
                                'description' => 'The numeric ID of the booking.'
                            ],
                            'customer_email' => [
                                'type' => 'string',
                                'description' => 'Email address associated with the booking.'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cancel_booking',
                    'description' => 'Cancel an existing hotel reservation.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_id' => [
                                'type' => 'integer',
                                'description' => 'The numeric ID of the booking to cancel.'
                            ],
                            'reason' => [
                                'type' => 'string',
                                'description' => 'Optional reason for cancellation.'
                            ]
                        ],
                        'required' => ['booking_id']
                    ]
                ]
            ]
        ];

        return response()->json([
            'service' => 'Hotel Booking Agent Toolset',
            'version' => '1.0.0',
            'tools_count' => count($tools),
            'tools' => $tools
        ]);
    }

    /**
     * Unified Tool Dispatcher
     * Executes any tool by name with provided arguments.
     */
    public function executeTool(Request $request)
    {
        $validated = $request->validate([
            'tool' => 'required|string',
            'arguments' => 'nullable|array',
        ]);

        $toolName = strtolower(str_replace(['-', '_'], '', $validated['tool']));
        $args = $validated['arguments'] ?? [];
        $subRequest = new Request($args);

        switch ($toolName) {
            case 'checkavailability':
                return $this->checkAvailability($subRequest);

            case 'createbooking':
                return $this->createBooking($subRequest);

            case 'getbookingdetails':
            case 'getbooking':
                return $this->getBookingDetails($subRequest);

            case 'cancelbooking':
                return $this->cancelBooking($subRequest);

            default:
                return response()->json([
                    'success' => false,
                    'error' => 'Unknown Tool',
                    'message' => "Tool '{$validated['tool']}' not recognized. Available tools: check_availability, create_booking, get_booking_details, cancel_booking."
                ], 400);
        }
    }

    /**
     * Agent Chat Simulator
     * Demonstrates an AI Agent reasoning loop: User Prompt -> Intent / Tool Selection -> Execution -> Agent Final Response.
     */
    public function simulateAgentChat(Request $request)
    {
        $message = trim($request->input('message', ''));
        if (empty($message)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a message prompt.'
            ], 422);
        }

        $lowerMsg = strtolower($message);
        $toolDecision = null;
        $toolArgs = [];
        $thought = '';

        // Extract dates (e.g. 2026-09-01 or 2026-09-10)
        preg_match('/\b\d{4}-\d{2}-\d{2}\b/', $message, $dateMatches);
        $foundDate = $dateMatches[0] ?? date('Y-m-d', strtotime('+3 days'));

        // Detect room type
        $foundRoom = 'deluxe';
        foreach (['penthouse', 'suite', 'deluxe', 'standard'] as $r) {
            if (str_contains($lowerMsg, $r)) {
                $foundRoom = $r;
                break;
            }
        }

        // Detect email
        preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $message, $emailMatches);
        $foundEmail = $emailMatches[0] ?? null;

        // Detect booking ID (e.g. "#1", "booking 2", "id 3", or solitary number)
        preg_match('/(?:#|id\s*|booking\s*)(\d+)/i', $message, $idMatches);
        $foundId = isset($idMatches[1]) ? (int)$idMatches[1] : null;

        // 1. Check if user wants to cancel
        if (str_contains($lowerMsg, 'cancel')) {
            $bookingId = $foundId ?? 1;
            $toolDecision = 'cancel_booking';
            $toolArgs = ['booking_id' => $bookingId, 'reason' => 'User requested cancellation'];
            $thought = "User wants to cancel a booking. Identified booking ID #{$bookingId}. Executing cancel_booking tool.";
        }
        // 2. Check if user wants to get/search booking details
        elseif (str_contains($lowerMsg, 'my booking') || str_contains($lowerMsg, 'search') || str_contains($lowerMsg, 'find') || str_contains($lowerMsg, 'status of') || str_contains($lowerMsg, 'lookup') || $foundId !== null) {
            $toolDecision = 'get_booking_details';
            $toolArgs = $foundId ? ['booking_id' => $foundId] : ['customer_email' => $foundEmail ?? 'alice@example.com'];
            $thought = "User wants to check existing booking details. Querying via get_booking_details tool.";
        }
        // 3. Check if user wants to book/reserve
        elseif (str_contains($lowerMsg, 'book') || str_contains($lowerMsg, 'reserve')) {
            $toolDecision = 'create_booking';
            $toolArgs = [
                'customer_name' => 'Agent Explorer',
                'customer_email' => $foundEmail ?? 'explorer@example.com',
                'room_type' => $foundRoom,
                'date' => $foundDate,
                'special_requests' => 'Booking placed via AI Agent'
            ];
            $thought = "User wants to make a new hotel reservation for a {$foundRoom} room on {$foundDate}. Executing create_booking tool.";
        }
        // 4. Default to check availability
        else {
            $toolDecision = 'check_availability';
            $toolArgs = ['date' => $foundDate, 'room_type' => $foundRoom];
            $thought = "User is asking about hotel availability or room options. Checking availability for {$foundRoom} on {$foundDate}.";
        }

        // Execute the tool
        $subRequest = new Request($toolArgs);
        $toolResponse = match ($toolDecision) {
            'check_availability' => $this->checkAvailability($subRequest),
            'create_booking' => $this->createBooking($subRequest),
            'get_booking_details' => $this->getBookingDetails($subRequest),
            'cancel_booking' => $this->cancelBooking($subRequest),
        };

        $toolData = json_decode($toolResponse->getContent(), true);

        // Synthesize agent conversational response
        $agentReply = match ($toolDecision) {
            'check_availability' => isset($toolData['message']) ? $toolData['message'] : "Here are the available options for {$foundDate}.",
            'create_booking' => ($toolData['success'] ?? false)
                ? "🎉 Fantastic news! Your reservation is confirmed. Confirmation Code: **{$toolData['confirmation_code']}** (Booking ID: #{$toolData['booking_id']})."
                : "⚠️ " . ($toolData['message'] ?? 'Unable to complete booking.'),
            'get_booking_details' => ($toolData['success'] ?? false)
                ? "📋 Found {$toolData['count']} reservation(s). First booking #{$toolData['bookings'][0]['id']} ({$toolData['bookings'][0]['room_type']}) status is: **{$toolData['bookings'][0]['status']}**."
                : "I couldn't find any reservations with those details.",
            'cancel_booking' => ($toolData['success'] ?? false)
                ? "✅ {$toolData['message']} {$toolData['refund_status']}"
                : "⚠️ " . ($toolData['message'] ?? 'Could not cancel booking.'),
        };

        return response()->json([
            'prompt' => $message,
            'agent_reasoning' => [
                'thought' => $thought,
                'selected_tool' => $toolDecision,
                'tool_arguments' => $toolArgs,
            ],
            'tool_output' => $toolData,
            'agent_response' => $agentReply
        ]);
    }
}
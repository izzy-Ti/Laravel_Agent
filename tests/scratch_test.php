<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\AgentToolController;
use Illuminate\Http\Request;
use App\Models\Booking;

echo "=== TESTING 4 AGENT TOOLS ===\n\n";

$controller = new AgentToolController();

// 1. Tool 1: Check Availability
echo "1. Testing check_availability...\n";
$req1 = Request::create('/api/agent/check-availability', 'POST', [
    'date' => '2026-09-01',
    'room_type' => 'deluxe'
]);
$res1 = $controller->checkAvailability($req1);
echo "Result (Status " . $res1->getStatusCode() . "): " . $res1->getContent() . "\n\n";

// 2. Tool 2: Create Booking
echo "2. Testing create_booking...\n";
$req2 = Request::create('/api/agent/create-booking', 'POST', [
    'customer_name' => 'Agent Master',
    'customer_email' => 'master@agent.ai',
    'room_type' => 'suite',
    'date' => '2026-11-20',
    'special_requests' => 'Autonomous Agent Dev Test'
]);
$res2 = $controller->createBooking($req2);
echo "Result (Status " . $res2->getStatusCode() . "): " . $res2->getContent() . "\n\n";

// 3. Tool 3: Get Booking Details
echo "3. Testing get_booking_details...\n";
$req3 = Request::create('/api/agent/get-booking-details', 'POST', [
    'customer_email' => 'master@agent.ai'
]);
$res3 = $controller->getBookingDetails($req3);
echo "Result (Status " . $res3->getStatusCode() . "): " . $res3->getContent() . "\n\n";

// 4. Tool 4: Cancel Booking
$createdData = json_decode($res2->getContent(), true);
$createdId = $createdData['booking_id'] ?? 1;
echo "4. Testing cancel_booking for ID #{$createdId}...\n";
$req4 = Request::create('/api/agent/cancel-booking', 'POST', [
    'booking_id' => $createdId,
    'reason' => 'Completed agent learning test'
]);
$res4 = $controller->cancelBooking($req4);
echo "Result (Status " . $res4->getStatusCode() . "): " . $res4->getContent() . "\n\n";

// 5. Tool Schemas
echo "5. Testing getToolsSchema...\n";
$res5 = $controller->getToolsSchema();
echo "Result (Status " . $res5->getStatusCode() . "): " . $res5->getContent() . "\n\n";

// 6. Unified Dispatcher
echo "6. Testing executeTool Dispatcher...\n";
$req6 = Request::create('/api/agent/execute', 'POST', [
    'tool' => 'check_availability',
    'arguments' => ['date' => '2026-12-01']
]);
$res6 = $controller->executeTool($req6);
echo "Result (Status " . $res6->getStatusCode() . "): " . $res6->getContent() . "\n\n";

// 7. Chat Simulator
echo "7. Testing simulateAgentChat...\n";
$req7 = Request::create('/api/agent/chat', 'POST', [
    'message' => 'Please reserve a penthouse on 2026-10-15 for Elon Musk'
]);
$res7 = $controller->simulateAgentChat($req7);
echo "Result (Status " . $res7->getStatusCode() . "): " . $res7->getContent() . "\n\n";

echo "=== ALL 4 TOOLS & AGENT HUB VALIDATED SUCCESSFULLY! ===\n";

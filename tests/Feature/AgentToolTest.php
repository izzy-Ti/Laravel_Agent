<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgentToolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Tool 1: Check Availability
     */
    public function test_tool_check_availability()
    {
        $response = $this->postJson('/api/agent/check-availability', [
            'date' => '2026-10-01',
            'room_type' => 'deluxe'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'tool' => 'check_availability',
                'success' => true,
                'available' => true,
                'room_type' => 'deluxe'
            ]);
    }

    /**
     * Test Tool 2: Create Booking
     */
    public function test_tool_create_booking()
    {
        $response = $this->postJson('/api/agent/create-booking', [
            'customer_name' => 'Charlie Brown',
            'customer_email' => 'charlie@example.com',
            'room_type' => 'deluxe',
            'date' => '2026-10-01',
            'special_requests' => 'Quiet room'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'tool' => 'create_booking',
                'success' => true,
            ])
            ->assertJsonStructure(['booking_id', 'confirmation_code']);

        $this->assertDatabaseHas('bookings', [
            'customer_email' => 'charlie@example.com',
            'status' => 'confirmed'
        ]);
    }

    /**
     * Test Tool 3: Get Booking Details
     */
    public function test_tool_get_booking_details()
    {
        $booking = Booking::create([
            'customer_name' => 'Dana Scully',
            'customer_email' => 'scully@fbi.gov',
            'room_type' => 'suite',
            'date' => '2026-10-05',
            'price' => 250.00,
            'status' => 'confirmed'
        ]);

        $response = $this->postJson('/api/agent/get-booking-details', [
            'booking_id' => $booking->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'tool' => 'get_booking_details',
                'found' => true,
                'count' => 1
            ]);
    }

    /**
     * Test Tool 4: Cancel Booking
     */
    public function test_tool_cancel_booking()
    {
        $booking = Booking::create([
            'customer_name' => 'Fox Mulder',
            'customer_email' => 'mulder@fbi.gov',
            'room_type' => 'standard',
            'date' => '2026-10-10',
            'price' => 99.00,
            'status' => 'confirmed'
        ]);

        $response = $this->postJson('/api/agent/cancel-booking', [
            'booking_id' => $booking->id,
            'reason' => 'Mission rescheduled'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'tool' => 'cancel_booking',
                'success' => true,
                'status' => 'cancelled'
            ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled'
        ]);
    }

    /**
     * Test Tools Schema Catalog
     */
    public function test_get_tools_schema()
    {
        $response = $this->getJson('/api/agent/tools');

        $response->assertStatus(200)
            ->assertJson([
                'tools_count' => 4,
            ])
            ->assertJsonStructure(['tools']);
    }

    /**
     * Test Universal Tool Dispatcher
     */
    public function test_execute_tool_dispatcher()
    {
        $response = $this->postJson('/api/agent/execute', [
            'tool' => 'check_availability',
            'arguments' => [
                'date' => '2026-11-15'
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'tool' => 'check_availability',
                'success' => true,
            ]);
    }

    /**
     * Test Agent Chat Simulation Loop
     */
    public function test_simulate_agent_chat()
    {
        $response = $this->postJson('/api/agent/chat', [
            'message' => 'Can you check if a suite is available on 2026-12-25?'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'prompt',
                'agent_reasoning' => ['thought', 'selected_tool', 'tool_arguments'],
                'tool_output',
                'agent_response'
            ]);
    }
}

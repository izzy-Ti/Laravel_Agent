<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test CEO Executive KPIs endpoint
     */
    public function test_can_fetch_ceo_executive_kpis(): void
    {
        $response = $this->getJson('/api/agent/ceo-kpis');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'company' => ['id', 'name', 'code', 'currency'],
                'kpis' => [
                    'revenue' => ['total_gross_usd', 'active_order_pipeline_count', 'total_orders'],
                    'freight_operations' => ['active_shipments', 'delivered_shipments', 'on_time_delivery_pct'],
                    'fleet_telematics' => ['total_vehicles', 'active_in_transit_vehicles', 'fleet_utilization_pct'],
                    'network_infrastructure' => ['total_warehouses', 'total_capacity_sqft', 'avg_utilization_pct'],
                ],
                'timestamp',
            ]);
    }

    /**
     * Test Agent Tools schema discovery
     */
    public function test_can_fetch_tools_schema(): void
    {
        $response = $this->getJson('/api/agent/tools');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'version',
                'agent_role',
                'source_of_truth',
                'total_tools',
                'tools' => [
                    '*' => [
                        'type',
                        'function' => ['name', 'description', 'parameters'],
                    ],
                ],
            ]);
    }

    /**
     * Test Universal Tool Dispatcher execution
     */
    public function test_can_execute_registered_tool(): void
    {
        $response = $this->postJson('/api/agent/execute', [
            'tool_name' => 'query_fleet_status',
            'parameters' => [],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'total_vehicles',
                'vehicles',
                'summary',
            ]);
    }

    /**
     * Test CEO Autonomous Chat Simulator
     */
    public function test_ceo_agent_chat_reasoning(): void
    {
        $response = $this->postJson('/api/agent/chat', [
            'message' => 'Where is shipment TRK-9832-7491-01 right now?',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'agent',
                'prompt',
                'reasoning_flow' => ['intent_classification', 'tool_called', 'tool_parameters'],
                'executive_briefing',
                'raw_tool_data',
            ]);
    }

    /**
     * Test CRUD for all 10 domain resources
     */
    public function test_all_10_logistics_domain_endpoints_return_data(): void
    {
        $endpoints = [
            '/api/companies',
            '/api/users',
            '/api/customers',
            '/api/warehouses',
            '/api/drivers',
            '/api/vehicles',
            '/api/orders',
            '/api/routes',
            '/api/shipments',
            '/api/deliveries',
        ];

        foreach ($endpoints as $endpoint) {
            $res = $this->getJson($endpoint);
            $res->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'pagination',
                ]);
            $this->assertNotEmpty($res->json('data'), "Expected {$endpoint} to have seeded data");
        }
    }

    /**
     * Test Creating a new Order and Tracking it
     */
    public function test_create_order_and_track(): void
    {
        $company = Company::first();
        $customer = Customer::first();

        $orderPayload = [
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-TEST-9999',
            'order_date' => '2026-08-26',
            'priority' => 'critical',
            'total_amount' => 50000.00,
            'status' => 'confirmed',
            'items_count' => 5,
        ];

        $res = $this->postJson('/api/orders', $orderPayload);
        $res->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', ['order_number' => 'ORD-TEST-9999']);
    }
}

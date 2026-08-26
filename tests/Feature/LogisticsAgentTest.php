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
     * Test Executive Intelligence: KPIs
     */
    public function test_can_fetch_ceo_executive_kpis(): void
    {
        $response = $this->getJson('/api/agent/kpis');

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
     * Test Executive Intelligence: Critical Exceptions
     */
    public function test_can_fetch_critical_exceptions(): void
    {
        $response = $this->getJson('/api/agent/critical-exceptions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'critical_exceptions_total',
                'exceptions' => [
                    'delayed_shipments',
                    'vehicle_maintenance_and_fuel_alerts',
                    'high_risk_routes',
                    'high_exposure_customers',
                ],
            ]);
    }

    /**
     * Test Read Logistics Data: Fleet Status & Consignment Tracking
     */
    public function test_can_query_fleet_status_and_track(): void
    {
        $fleetRes = $this->getJson('/api/agent/fleet-status?status=in_transit');
        $fleetRes->assertStatus(200)->assertJson(['success' => true]);

        $shipment = Shipment::first();
        $trackRes = $this->getJson("/api/agent/track?query_code={$shipment->tracking_number}");
        $trackRes->assertStatus(200)
            ->assertJson(['success' => true, 'type' => 'shipment']);
    }

    /**
     * Test Action Logistics Data: Assign Dispatch & Update Status
     */
    public function test_can_dispatch_and_update_shipment(): void
    {
        $shipment = Shipment::first();

        // 1. Dispatch
        $dispatchRes = $this->postJson('/api/agent/dispatch', [
            'shipment_id' => $shipment->id,
        ]);
        $dispatchRes->assertStatus(200)->assertJson(['success' => true]);

        // 2. Update status
        $updateRes = $this->postJson('/api/agent/update-shipment-status', [
            'shipment_id' => $shipment->id,
            'status' => 'delivered',
            'notes' => 'Confirmed delivery at receiving dock',
        ]);
        $updateRes->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals('delivered', $shipment->fresh()->status);
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
}

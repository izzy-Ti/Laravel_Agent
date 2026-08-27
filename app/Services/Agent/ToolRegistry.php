<?php

namespace App\Services\Agent;

use InvalidArgumentException;

class ToolRegistry
{
    protected LogisticsToolService $toolService;

    /**
     * @var array<string, array{schema: array, method: string}>
     */
    protected array $tools = [];

    public function __construct(LogisticsToolService $toolService)
    {
        $this->toolService = $toolService;
        $this->registerBuiltInTools();
    }

    /**
     * Register all built-in logistics tools with Anthropic Tool Schema (Draft-07 JSON Schema)
     */
    protected function registerBuiltInTools(): void
    {
        // 1. get_executive_kpis
        $this->registerTool([
            'name' => 'get_executive_kpis',
            'description' => 'Retrieve high-level CEO executive KPIs: gross revenue, active orders pipeline, active and delayed shipments, on-time delivery rate (OTD %), fleet utilization %, driver safety average, and warehouse capacity utilization.',
            'input_schema' => [
                'type' => 'object',
                'properties' => (object)[],
            ],
        ], 'getExecutiveKpis');

        // 2. get_critical_exceptions
        $this->registerTool([
            'name' => 'get_critical_exceptions',
            'description' => 'Scan entire operations for critical risks: delayed freight shipments, vehicle mechanical breakdowns or low fuel (<15%), severe weather routes, and high-exposure overdue customer balances (> $500k).',
            'input_schema' => [
                'type' => 'object',
                'properties' => (object)[],
            ],
        ], 'getCriticalExceptions');

        // 3. get_fleet_status
        $this->registerTool([
            'name' => 'get_fleet_status',
            'description' => 'Get real-time status of fleet vehicles, active driver pairings, in-transit telematics, and low-fuel alerts (<25%).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'in_transit', 'maintenance', 'inactive'],
                        'description' => 'Filter vehicles by operational status.',
                    ],
                    'low_fuel_only' => [
                        'type' => 'boolean',
                        'description' => 'If true, returns only vehicles with fuel level below 25%.',
                    ],
                ],
            ],
        ], 'getFleetStatus');

        // 4. track_consignment
        $this->registerTool([
            'name' => 'track_consignment',
            'description' => 'Universal consignment tracker: lookup any shipment, delivery, or order by tracking number (e.g. TRK-...), shipment code (e.g. SHP-...), order number (e.g. ORD-...), or delivery code (e.g. DEL-...).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'query_code' => [
                        'type' => 'string',
                        'description' => 'The tracking number, shipment code, order number, or delivery code.',
                    ],
                ],
                'required' => ['query_code'],
            ],
        ], 'trackConsignment');

        // 5. get_warehouse_capacity
        $this->registerTool([
            'name' => 'get_warehouse_capacity',
            'description' => 'Inspect warehouse square-footage capacity and utilization across regional distribution superhubs (LA, Chicago, Dallas, Atlanta, etc.) and flag capacity bottlenecks.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'threshold_pct' => [
                        'type' => 'number',
                        'description' => 'Utilization percentage threshold above which a warehouse is flagged as a bottleneck (default: 80.0).',
                    ],
                ],
            ],
        ], 'getWarehouseCapacity');

        // 6. get_customer_financials
        $this->registerTool([
            'name' => 'get_customer_financials',
            'description' => 'Analyze B2B customer credit limits, outstanding receivable balances, credit utilization percentage, and payment terms risk profiles.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'customer_id' => [
                        'type' => 'integer',
                        'description' => 'Optional specific customer ID to inspect.',
                    ],
                ],
            ],
        ], 'getCustomerFinancials');

        // 7. get_shipments
        $this->registerTool([
            'name' => 'get_shipments',
            'description' => 'List active line-haul shipments with origin warehouse, destination warehouse, freight items, route, and NIST cold chain requirements.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'picked_up', 'in_transit', 'delayed', 'delivered', 'cancelled'],
                        'description' => 'Filter by shipment status.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of records to return (default: 25, max: 100).',
                    ],
                ],
            ],
        ], 'getShipments');

        // 8. get_deliveries
        $this->registerTool([
            'name' => 'get_deliveries',
            'description' => 'List final-mile fulfillment delivery dispatches with assigned driver, vehicle, delivery window, and Proof of Delivery (POD) status.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'dispatched', 'en_route', 'arrived', 'completed', 'failed'],
                        'description' => 'Filter by delivery status.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of records to return (default: 25).',
                    ],
                ],
            ],
        ], 'getDeliveries');

        // 9. get_drivers
        $this->registerTool([
            'name' => 'get_drivers',
            'description' => 'List commercial CDL drivers with safety score ratings (0-100), duty status, CDL license class, and phone contacts.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['available', 'on_trip', 'off_duty', 'suspended'],
                        'description' => 'Filter by driver duty status.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum records to return (default: 25).',
                    ],
                ],
            ],
        ], 'getDrivers');

        // 10. get_vehicles
        $this->registerTool([
            'name' => 'get_vehicles',
            'description' => 'List fleet vehicles with make, model, type (Dry Van, Reefer, Flatbed, EV Sprinter), fuel/battery level %, odometer, and maintenance status.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['active', 'in_transit', 'maintenance', 'inactive'],
                        'description' => 'Filter vehicles by status.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum records to return (default: 25).',
                    ],
                ],
            ],
        ], 'getVehicles');

        // 11. assign_shipment_dispatch
        $this->registerTool([
            'name' => 'assign_shipment_dispatch',
            'description' => 'Autonomously assign or optimize dispatch: pairs an available top-safety commercial driver and high-fuel fleet vehicle to a shipment, generating final-mile delivery dispatch.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'shipment_id' => [
                        'type' => 'integer',
                        'description' => 'The ID of the shipment to dispatch.',
                    ],
                    'driver_id' => [
                        'type' => 'integer',
                        'description' => 'Optional specific driver ID. If omitted, the agent selects the available driver with highest safety score.',
                    ],
                    'vehicle_id' => [
                        'type' => 'integer',
                        'description' => 'Optional specific vehicle ID. If omitted, the agent selects the optimal active vehicle with highest fuel.',
                    ],
                ],
                'required' => ['shipment_id'],
            ],
        ], 'assignShipmentDispatch');

        // 12. update_shipment_status
        $this->registerTool([
            'name' => 'update_shipment_status',
            'description' => 'Update an active consignment status with audit instructions and executive reason notes.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'shipment_id' => [
                        'type' => 'integer',
                        'description' => 'The ID of the shipment to update.',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'picked_up', 'in_transit', 'delayed', 'delivered'],
                        'description' => 'New shipment status.',
                    ],
                    'notes' => [
                        'type' => 'string',
                        'description' => 'Executive audit explanation for the status modification.',
                    ],
                ],
                'required' => ['shipment_id', 'status'],
            ],
        ], 'updateShipmentStatus');

        // 13. cancel_shipment
        $this->registerTool([
            'name' => 'cancel_shipment',
            'description' => 'Safely cancel an active freight shipment and release or fail associated delivery dispatches with justification.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'shipment_id' => [
                        'type' => 'integer',
                        'description' => 'The ID of the shipment to cancel.',
                    ],
                    'reason' => [
                        'type' => 'string',
                        'description' => 'Detailed business or operational justification for cancellation.',
                    ],
                ],
                'required' => ['shipment_id', 'reason'],
            ],
        ], 'cancelShipment');

        // 14. update_delivery_status
        $this->registerTool([
            'name' => 'update_delivery_status',
            'description' => 'Update final-mile delivery status and record completion or proof-of-delivery timestamps.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'delivery_id' => [
                        'type' => 'integer',
                        'description' => 'The ID of the delivery dispatch.',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['pending', 'dispatched', 'en_route', 'arrived', 'completed', 'failed'],
                        'description' => 'New delivery status.',
                    ],
                    'notes' => [
                        'type' => 'string',
                        'description' => 'Delivery progress notes or recipient sign-off remarks.',
                    ],
                ],
                'required' => ['delivery_id', 'status'],
            ],
        ], 'updateDeliveryStatus');

        // 15. cancel_delivery
        $this->registerTool([
            'name' => 'cancel_delivery',
            'description' => 'Cancel a final-mile delivery dispatch with reason.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'delivery_id' => [
                        'type' => 'integer',
                        'description' => 'The ID of the delivery dispatch to cancel.',
                    ],
                    'reason' => [
                        'type' => 'string',
                        'description' => 'Reason for cancelling the delivery dispatch.',
                    ],
                ],
                'required' => ['delivery_id', 'reason'],
            ],
        ], 'cancelDelivery');
    }

    /**
     * Register a custom tool schema and execution method
     */
    public function registerTool(array $schema, string $method): void
    {
        $name = $schema['name'];
        $this->tools[$name] = [
            'schema' => $schema,
            'method' => $method,
        ];
    }

    /**
     * Get all tool definitions formatted for Claude Anthropic API
     *
     * @return array<int, array>
     */
    public function getAnthropicTools(): array
    {
        return array_values(array_map(fn($item) => $item['schema'], $this->tools));
    }

    /**
     * Check if tool exists
     */
    public function hasTool(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /**
     * Execute tool by name with arguments
     */
    public function executeTool(string $name, array $args, int $companyId = 1): array
    {
        if (!$this->hasTool($name)) {
            return [
                'success' => false,
                'error' => "Tool '{$name}' not found in registry.",
            ];
        }

        $method = $this->tools[$name]['method'];

        if (!method_exists($this->toolService, $method)) {
            return [
                'success' => false,
                'error' => "Execution handler method '{$method}' not found on LogisticsToolService.",
            ];
        }

        try {
            return $this->toolService->$method($args, $companyId);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Tool execution failed: ' . $e->getMessage(),
            ];
        }
    }
}

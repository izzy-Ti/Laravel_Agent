<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentToolController extends Controller
{
    /**
     * Get Executive CEO High-Level KPI Summary (Source of Truth Dashboard)
     */
    public function getExecutiveKpis(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id', env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
        $company = Company::find($companyId);

        $totalRevenue = Order::where('company_id', $companyId)->sum('total_amount');
        $activeOrders = Order::where('company_id', $companyId)->whereIn('status', ['confirmed', 'processing', 'manifested', 'shipped'])->count();
        $totalOrders = Order::where('company_id', $companyId)->count();

        $activeShipments = Shipment::where('company_id', $companyId)->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery'])->count();
        $delayedShipments = Shipment::where('company_id', $companyId)->where('status', 'delayed')->count();
        $deliveredShipments = Shipment::where('company_id', $companyId)->where('status', 'delivered')->count();
        $totalShipments = Shipment::where('company_id', $companyId)->count();

        // Calculate On-Time Delivery Rate
        $onTimeRate = $totalShipments > 0 ? round((($totalShipments - $delayedShipments) / $totalShipments) * 100, 1) : 100.0;

        $totalVehicles = Vehicle::where('company_id', $companyId)->count();
        $inTransitVehicles = Vehicle::where('company_id', $companyId)->where('status', 'in_transit')->count();
        $activeVehicles = Vehicle::where('company_id', $companyId)->whereIn('status', ['active', 'in_transit'])->count();
        $fleetUtilization = $totalVehicles > 0 ? round(($inTransitVehicles / $totalVehicles) * 100, 1) : 0.0;

        $totalDrivers = Driver::where('company_id', $companyId)->count();
        $activeDrivers = Driver::where('company_id', $companyId)->where('status', 'on_trip')->count();
        $avgDriverSafety = Driver::where('company_id', $companyId)->avg('safety_score') ?? 98.0;

        $warehouses = Warehouse::where('company_id', $companyId)->get();
        $avgWarehouseUtilization = $warehouses->avg('current_utilization_pct') ?? 0.0;
        $totalWarehouseCapacity = $warehouses->sum('capacity_sqft');

        $activeDeliveries = Delivery::where('company_id', $companyId)->whereIn('status', ['dispatched', 'en_route', 'arrived'])->count();
        $completedDeliveries = Delivery::where('company_id', $companyId)->where('status', 'completed')->count();

        $criticalExceptions = $delayedShipments + Vehicle::where('company_id', $companyId)->where('status', 'maintenance')->count();

        return response()->json([
            'success' => true,
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
                'currency' => $company->currency,
            ] : null,
            'kpis' => [
                'revenue' => [
                    'total_gross_usd' => (float)$totalRevenue,
                    'active_order_pipeline_count' => $activeOrders,
                    'total_orders' => $totalOrders,
                ],
                'freight_operations' => [
                    'active_shipments' => $activeShipments,
                    'delivered_shipments' => $deliveredShipments,
                    'delayed_shipments' => $delayedShipments,
                    'on_time_delivery_pct' => $onTimeRate,
                    'active_deliveries' => $activeDeliveries,
                    'completed_deliveries' => $completedDeliveries,
                ],
                'fleet_telematics' => [
                    'total_vehicles' => $totalVehicles,
                    'active_in_transit_vehicles' => $inTransitVehicles,
                    'fleet_utilization_pct' => $fleetUtilization,
                    'total_drivers' => $totalDrivers,
                    'active_drivers_on_trip' => $activeDrivers,
                    'avg_safety_score' => round((float)$avgDriverSafety, 1),
                ],
                'network_infrastructure' => [
                    'total_warehouses' => $warehouses->count(),
                    'total_capacity_sqft' => $totalWarehouseCapacity,
                    'avg_utilization_pct' => round((float)$avgWarehouseUtilization, 1),
                    'critical_exceptions_count' => $criticalExceptions,
                ],
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Tool 1: Query Live Fleet Status & GPS Telematics
     */
    public function queryFleetStatus(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id', env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
        $status = $request->input('status');
        $fuelAlertOnly = $request->boolean('low_fuel_only');

        $query = Vehicle::with(['currentDriver:id,driver_code,first_name,last_name,phone,status,safety_score'])
            ->where('company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        if ($fuelAlertOnly) {
            $query->where('fuel_level_pct', '<', 25.0);
        }

        $vehicles = $query->get();

        $drivers = Driver::where('company_id', $companyId)->get();

        return response()->json([
            'success' => true,
            'total_vehicles' => $vehicles->count(),
            'vehicles' => $vehicles,
            'summary' => [
                'in_transit' => $vehicles->where('status', 'in_transit')->count(),
                'active_idle' => $vehicles->where('status', 'active')->count(),
                'in_maintenance' => $vehicles->where('status', 'maintenance')->count(),
                'drivers_available' => $drivers->where('status', 'available')->count(),
                'drivers_on_trip' => $drivers->where('status', 'on_trip')->count(),
            ],
        ]);
    }

    /**
     * Tool 2: Track Shipment or Final-Mile Delivery
     */
    public function trackShipmentOrDelivery(Request $request): JsonResponse
    {
        $queryCode = $request->input('query_code') ?? $request->input('tracking_number') ?? $request->input('shipment_number') ?? $request->input('order_number');

        if (!$queryCode) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a query_code, tracking_number, shipment_number, or order_number.',
            ], 422);
        }

        $shipment = Shipment::with([
            'order.customer',
            'originWarehouse',
            'destinationWarehouse',
            'route',
            'deliveries.driver',
            'deliveries.vehicle',
        ])
        ->where('tracking_number', $queryCode)
        ->orWhere('shipment_number', $queryCode)
        ->orWhereHas('order', function ($q) use ($queryCode) {
            $q->where('order_number', $queryCode);
        })
        ->first();

        if ($shipment) {
            return response()->json([
                'success' => true,
                'type' => 'shipment',
                'data' => $shipment,
            ]);
        }

        $delivery = Delivery::with([
            'shipment.order.customer',
            'driver',
            'vehicle',
            'route',
        ])->where('delivery_number', $queryCode)->first();

        if ($delivery) {
            return response()->json([
                'success' => true,
                'type' => 'delivery',
                'data' => $delivery,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "No shipment, delivery, or order found matching code '{$queryCode}'.",
        ], 404);
    }

    /**
     * Tool 3: Inspect Warehouse Capacity & Bottlenecks
     */
    public function inspectWarehouseCapacity(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id', env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
        $highUtilizationThreshold = (float)$request->input('threshold_pct', 80.0);

        $warehouses = Warehouse::where('company_id', $companyId)
            ->withCount(['outboundShipments' => function ($q) {
                $q->whereIn('status', ['picked_up', 'in_transit']);
            }, 'inboundShipments' => function ($q) {
                $q->whereIn('status', ['picked_up', 'in_transit']);
            }])
            ->get();

        $bottlenecks = $warehouses->filter(function ($wh) use ($highUtilizationThreshold) {
            return $wh->current_utilization_pct >= $highUtilizationThreshold;
        })->values();

        return response()->json([
            'success' => true,
            'total_warehouses' => $warehouses->count(),
            'threshold_pct_used' => $highUtilizationThreshold,
            'bottlenecks_detected' => $bottlenecks->count(),
            'bottleneck_hubs' => $bottlenecks,
            'all_warehouses' => $warehouses,
        ]);
    }

    /**
     * Tool 4: Optimize & Assign Dispatch
     */
    public function optimizeOrAssignDispatch(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id', env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
        $shipmentId = $request->input('shipment_id');
        $driverId = $request->input('driver_id');
        $vehicleId = $request->input('vehicle_id');

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        // Auto-assign best available driver and vehicle if not explicitly passed
        if (!$driverId) {
            $bestDriver = Driver::where('company_id', $companyId)
                ->where('status', 'available')
                ->orderByDesc('safety_score')
                ->first();
            $driverId = $bestDriver?->id;
        } else {
            $bestDriver = Driver::find($driverId);
        }

        if (!$vehicleId) {
            $bestVehicle = Vehicle::where('company_id', $companyId)
                ->where('status', 'active')
                ->orderByDesc('fuel_level_pct')
                ->first();
            $vehicleId = $bestVehicle?->id;
        } else {
            $bestVehicle = Vehicle::find($vehicleId);
        }

        if (!$bestDriver || !$bestVehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient available resources: Driver or Vehicle unavailable for assignment.',
            ], 422);
        }

        // Update driver and vehicle status
        $bestDriver->update(['status' => 'on_trip']);
        $bestVehicle->update(['status' => 'in_transit', 'current_driver_id' => $bestDriver->id]);

        // Create or update delivery assignment
        $delivery = Delivery::updateOrCreate(
            ['shipment_id' => $shipment->id],
            [
                'company_id' => $companyId,
                'driver_id' => $bestDriver->id,
                'vehicle_id' => $bestVehicle->id,
                'route_id' => $shipment->route_id,
                'delivery_number' => 'DEL-' . date('Y') . '-' . rand(1000, 9999),
                'recipient_name' => $shipment->order->customer->name ?? 'Recipient',
                'recipient_phone' => $shipment->order->customer->phone ?? '+1 (555) 000-0000',
                'delivery_address' => $shipment->order->customer->shipping_address ?? 'Enterprise Logistics Receiving Dock',
                'scheduled_window_start' => Carbon::now()->addHours(4),
                'scheduled_window_end' => Carbon::now()->addHours(8),
                'status' => 'en_route',
                'notes' => 'Autonomous CEO dispatch optimization executed successfully.',
            ]
        );

        $shipment->update(['status' => 'in_transit']);

        return response()->json([
            'success' => true,
            'message' => "Dispatch optimization executed. Assigned Driver {$bestDriver->full_name} and Vehicle {$bestVehicle->vehicle_code}.",
            'delivery' => $delivery->load(['driver', 'vehicle', 'shipment.order.customer']),
        ]);
    }

    /**
     * Tool 5: Flag Critical Operational Exceptions & Risk Alerts
     */
    public function flagCriticalExceptions(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id', env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));

        $delayedShipments = Shipment::where('company_id', $companyId)
            ->where('status', 'delayed')
            ->with(['order.customer', 'route', 'deliveries.driver'])
            ->get();

        $maintenanceVehicles = Vehicle::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('status', 'maintenance')
                  ->orWhere('fuel_level_pct', '<', 15.0)
                  ->orWhere('next_maintenance_at', '<=', Carbon::now()->addDays(5));
            })
            ->get();

        $highRiskRoutes = Route::where('company_id', $companyId)
            ->whereIn('risk_level', ['high', 'severe_weather'])
            ->get();

        $overdueCustomers = Customer::where('company_id', $companyId)
            ->where('outstanding_balance', '>', 500000)
            ->get();

        $criticalCount = $delayedShipments->count() + $maintenanceVehicles->count() + $highRiskRoutes->count();

        return response()->json([
            'success' => true,
            'critical_exceptions_total' => $criticalCount,
            'exceptions' => [
                'delayed_shipments' => $delayedShipments,
                'vehicle_maintenance_and_fuel_alerts' => $maintenanceVehicles,
                'high_risk_routes' => $highRiskRoutes,
                'high_exposure_customers' => $overdueCustomers,
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Tool 6: Customer Financial Risk & Receivables
     */
    public function getCustomerFinancials(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id', env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
        $customerId = $request->input('customer_id');

        $query = Customer::where('company_id', $companyId)->withCount('orders');

        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->orderByDesc('outstanding_balance')->get();

        $totalOutstanding = $customers->sum('outstanding_balance');
        $totalCreditLimit = $customers->sum('credit_limit');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_outstanding_receivables' => (float)$totalOutstanding,
                'total_credit_extended' => (float)$totalCreditLimit,
                'credit_utilization_pct' => $totalCreditLimit > 0 ? round(($totalOutstanding / $totalCreditLimit) * 100, 1) : 0,
            ],
            'customers' => $customers,
        ]);
    }

    /**
     * Universal Tool Calling Execution Endpoint
     */
    public function executeTool(Request $request): JsonResponse
    {
        $tool = $request->input('tool_name') ?? $request->input('name') ?? $request->input('tool');
        $params = $request->input('parameters') ?? $request->input('arguments') ?? $request->all();

        if (is_string($params)) {
            $params = json_decode($params, true) ?? [];
        }

        $syntheticRequest = new Request($params);

        switch ($tool) {
            case 'get_executive_kpis':
                return $this->getExecutiveKpis($syntheticRequest);

            case 'query_fleet_status':
                return $this->queryFleetStatus($syntheticRequest);

            case 'track_shipment_or_delivery':
                return $this->trackShipmentOrDelivery($syntheticRequest);

            case 'inspect_warehouse_capacity':
                return $this->inspectWarehouseCapacity($syntheticRequest);

            case 'optimize_or_assign_dispatch':
                return $this->optimizeOrAssignDispatch($syntheticRequest);

            case 'flag_critical_exceptions':
                return $this->flagCriticalExceptions($syntheticRequest);

            case 'get_customer_financials':
                return $this->getCustomerFinancials($syntheticRequest);

            default:
                return response()->json([
                    'success' => false,
                    'error' => "Unknown tool name '{$tool}'. Call /api/agent/tools to see the list of valid tools.",
                ], 400);
        }
    }

    /**
     * AI Agent Tool Discovery / Schema for LangChain, OpenAI, Claude, Gemini
     */
    public function getToolsSchema(): JsonResponse
    {
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_executive_kpis',
                    'description' => 'Retrieve high-level CEO executive KPIs: gross revenue, active shipments, fleet utilization %, on-time delivery rate (OTD %), and warehouse capacity.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'company_id' => [
                                'type' => 'integer',
                                'description' => 'Optional Company ID (default: 1)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'query_fleet_status',
                    'description' => 'Get real-time status of all fleet vehicles, drivers, in-transit telematics, fuel levels, and maintenance status.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => [
                                'type' => 'string',
                                'enum' => ['active', 'in_transit', 'maintenance', 'idle'],
                                'description' => 'Filter by vehicle operational status.',
                            ],
                            'low_fuel_only' => [
                                'type' => 'boolean',
                                'description' => 'Only return vehicles with fuel below 25%.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'track_shipment_or_delivery',
                    'description' => 'Track any shipment, order, or final-mile delivery by tracking number, shipment code, delivery number, or order number.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query_code' => [
                                'type' => 'string',
                                'description' => 'Tracking code, shipment number, delivery number, or order number (e.g. TRK-9832-7491-01, ORD-2026-TSLA-8821).',
                            ],
                        ],
                        'required' => ['query_code'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'inspect_warehouse_capacity',
                    'description' => 'Inspect warehouse utilization across all regional distribution superhubs and identify capacity bottlenecks.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'threshold_pct' => [
                                'type' => 'number',
                                'description' => 'Utilization threshold to flag bottlenecks (default: 80).',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'optimize_or_assign_dispatch',
                    'description' => 'Autonomously allocate and dispatch the optimal available driver and vehicle to an active shipment corridor.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'shipment_id' => [
                                'type' => 'integer',
                                'description' => 'ID of the shipment to dispatch.',
                            ],
                            'driver_id' => [
                                'type' => 'integer',
                                'description' => 'Optional specific driver ID (auto-selected if omitted).',
                            ],
                            'vehicle_id' => [
                                'type' => 'integer',
                                'description' => 'Optional specific vehicle ID (auto-selected if omitted).',
                            ],
                        ],
                        'required' => ['shipment_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'flag_critical_exceptions',
                    'description' => 'Scan entire operations for critical risks: delayed shipments, vehicle breakdowns, severe weather routes, and credit exposure.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'company_id' => [
                                'type' => 'integer',
                                'description' => 'Company ID to audit.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_customer_financials',
                    'description' => 'Analyze customer credit limits, outstanding balances, payment terms, and financial risk profiles.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'customer_id' => [
                                'type' => 'integer',
                                'description' => 'Optional specific Customer ID.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json([
            'version' => '1.0.0',
            'agent_role' => 'Logistics Chief Executive Officer (CEO) Agent',
            'source_of_truth' => 'Laravel Enterprise Logistics API & Supabase Postgres',
            'total_tools' => count($tools),
            'tools' => $tools,
        ]);
    }

}
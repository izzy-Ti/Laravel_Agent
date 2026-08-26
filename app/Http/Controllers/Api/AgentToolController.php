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

class AgentToolController extends Controller
{
    /**
     * Resolve Multi-Tenant Company ID safely from Authenticated Context or Header
     */
    protected function resolveCompanyId(Request $request): int
    {
        return $request->user()?->company_id 
            ?? (int)($request->header('X-Company-ID') ?: env('CEO_AGENT_DEFAULT_COMPANY_ID', 1));
    }

    // =========================================================================
    // SECTION 1: EXECUTIVE INTELLIGENCE TOOLS
    // =========================================================================

    /**
     * Tool: getExecutiveKpis
     * Retrieve high-level CEO executive KPIs: gross revenue, active shipments, fleet utilization %, on-time delivery rate (OTD %), and warehouse capacity.
     */
    public function getExecutiveKpis(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $company = Company::find($companyId);

        $totalRevenue = Order::where('company_id', $companyId)->sum('total_amount');
        $activeOrders = Order::where('company_id', $companyId)->whereIn('status', ['confirmed', 'processing', 'manifested', 'shipped'])->count();
        $totalOrders = Order::where('company_id', $companyId)->count();

        $activeShipments = Shipment::where('company_id', $companyId)->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery'])->count();
        $delayedShipments = Shipment::where('company_id', $companyId)->where('status', 'delayed')->count();
        $deliveredShipments = Shipment::where('company_id', $companyId)->where('status', 'delivered')->count();
        $totalShipments = Shipment::where('company_id', $companyId)->count();

        $onTimeRate = $totalShipments > 0 ? round((($totalShipments - $delayedShipments) / $totalShipments) * 100, 1) : 100.0;

        $totalVehicles = Vehicle::where('company_id', $companyId)->count();
        $inTransitVehicles = Vehicle::where('company_id', $companyId)->where('status', 'in_transit')->count();
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
     * Tool: getCriticalExceptions
     * Scan entire operations for critical risks: delayed shipments, vehicle breakdowns, severe weather routes, and overdue customer accounts.
     */
    public function flagCriticalExceptions(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);

        $delayedShipments = Shipment::where('company_id', $companyId)
            ->where('status', 'delayed')
            ->with(['order.customer:id,name,email', 'route:id,name,route_code', 'originWarehouse:id,name', 'destinationWarehouse:id,name'])
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

    // =========================================================================
    // SECTION 2: READ LOGISTICS DATA TOOLS
    // =========================================================================

    /**
     * Tool: getFleetStatus
     * Get real-time status of fleet vehicles, drivers, in-transit telematics, and fuel alerts.
     */
    public function queryFleetStatus(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $status = $request->query('status');
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
     * Tool: trackConsignment
     * Track any shipment, order, or final-mile delivery by tracking number, shipment code, delivery number, or order number.
     */
    public function trackShipmentOrDelivery(Request $request): JsonResponse
    {
        $queryCode = $request->query('query_code') ?? $request->query('tracking_number') ?? $request->query('shipment_number') ?? $request->query('order_number');

        if (!$queryCode) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide query_code (e.g. TRK-1000-9999-01, SHP-260001, ORD-2026-01001, or DEL-2026-8801).',
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
     * Tool: getWarehouseCapacity
     * Inspect warehouse utilization across regional distribution superhubs and flag capacity bottlenecks.
     */
    public function inspectWarehouseCapacity(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $highUtilizationThreshold = (float)$request->query('threshold_pct', 80.0);

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
            'bottleneck_facilities' => $bottlenecks,
            'all_facilities' => $warehouses,
        ]);
    }

    /**
     * Tool: getCustomerFinancials
     * Analyze customer credit limits, outstanding balances, payment terms, and financial risk profiles.
     */
    public function getCustomerFinancials(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $customerId = $request->query('customer_id');

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
     * Tool: getShipments
     * List active line-haul shipments with origin, destination, carrier, and temperature control status.
     */
    public function getShipments(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $status = $request->query('status');
        $perPage = (int)$request->query('per_page', 25);

        $query = Shipment::with(['order.customer:id,name', 'originWarehouse:id,name,city', 'destinationWarehouse:id,name,city', 'route:id,name'])
            ->where('company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        $shipments = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'pagination' => [
                'total' => $shipments->total(),
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
            ],
        ]);
    }

    /**
     * Tool: getDeliveries
     * List final-mile fulfillment delivery dispatches with assigned driver, vehicle, and POD status.
     */
    public function getDeliveries(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $status = $request->query('status');
        $perPage = (int)$request->query('per_page', 25);

        $query = Delivery::with(['driver:id,first_name,last_name,phone', 'vehicle:id,vehicle_code,plate_number', 'shipment:id,tracking_number,shipment_number'])
            ->where('company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        $deliveries = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'pagination' => [
                'total' => $deliveries->total(),
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
            ],
        ]);
    }

    /**
     * Tool: getDrivers
     * List commercial drivers with status, safety score, rating, and trip history.
     */
    public function getDrivers(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $status = $request->query('status');
        $perPage = (int)$request->query('per_page', 25);

        $query = Driver::where('company_id', $companyId);
        if ($status) {
            $query->where('status', $status);
        }

        $drivers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $drivers->items(),
            'pagination' => [
                'total' => $drivers->total(),
                'current_page' => $drivers->currentPage(),
                'last_page' => $drivers->lastPage(),
            ],
        ]);
    }

    /**
     * Tool: getVehicles
     * List fleet vehicles with make/model, fuel level %, odometer, and maintenance schedules.
     */
    public function getVehicles(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $status = $request->query('status');
        $perPage = (int)$request->query('per_page', 25);

        $query = Vehicle::with('currentDriver:id,first_name,last_name')->where('company_id', $companyId);
        if ($status) {
            $query->where('status', $status);
        }

        $vehicles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'pagination' => [
                'total' => $vehicles->total(),
                'current_page' => $vehicles->currentPage(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    // =========================================================================
    // SECTION 3: ACTION LOGISTICS DATA TOOLS (NARROW BUSINESS ACTIONS)
    // =========================================================================

    /**
     * Tool: assignShipmentDispatch
     * Assign a driver and vehicle to a shipment. If driver_id or vehicle_id is omitted, the system selects suitable available resources.
     */
    public function optimizeOrAssignDispatch(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $shipmentId = $request->input('shipment_id');
        $driverId = $request->input('driver_id');
        $vehicleId = $request->input('vehicle_id');

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => "Shipment ID #{$shipmentId} not found in company."], 404);
        }

        if (!$driverId) {
            $bestDriver = Driver::where('company_id', $companyId)
                ->where('status', 'available')
                ->orderByDesc('safety_score')
                ->first();
            $driverId = $bestDriver?->id;
        } else {
            $bestDriver = Driver::where('company_id', $companyId)->find($driverId);
        }

        if (!$vehicleId) {
            $bestVehicle = Vehicle::where('company_id', $companyId)
                ->where('status', 'active')
                ->orderByDesc('fuel_level_pct')
                ->first();
            $vehicleId = $bestVehicle?->id;
        } else {
            $bestVehicle = Vehicle::where('company_id', $companyId)->find($vehicleId);
        }

        if (!$bestDriver || !$bestVehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient available resources: Driver or Vehicle unavailable for assignment.',
            ], 422);
        }

        $bestDriver->update(['status' => 'on_trip']);
        $bestVehicle->update(['status' => 'in_transit', 'current_driver_id' => $bestDriver->id]);

        $delivery = Delivery::updateOrCreate(
            ['shipment_id' => $shipment->id],
            [
                'company_id' => $companyId,
                'driver_id' => $bestDriver->id,
                'vehicle_id' => $bestVehicle->id,
                'route_id' => $shipment->route_id,
                'delivery_number' => 'DEL-' . date('Y') . '-' . rand(1000, 9999),
                'recipient_name' => $shipment->order->customer->name ?? 'Receiving Dock',
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
            'message' => "Dispatch executed successfully: Assigned Driver {$bestDriver->first_name} {$bestDriver->last_name} ({$bestDriver->driver_code}) and Vehicle {$bestVehicle->vehicle_code}.",
            'shipment' => $shipment->fresh(),
            'delivery' => $delivery->load(['driver:id,first_name,last_name,phone', 'vehicle:id,vehicle_code,plate_number']),
        ]);
    }

    /**
     * Tool: updateShipmentStatus
     * Update an active shipment's status with audit notes.
     */
    public function updateShipmentStatus(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $shipmentId = $request->input('shipment_id');
        $status = $request->input('status');
        $notes = $request->input('notes', 'Status updated by CEO Agent Action');

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => "Shipment ID #{$shipmentId} not found."], 404);
        }

        $shipment->update([
            'status' => $status,
            'special_instructions' => $shipment->special_instructions ? ($shipment->special_instructions . " | " . $notes) : $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Shipment {$shipment->shipment_number} status updated to '{$status}'.",
            'shipment' => $shipment,
        ]);
    }

    /**
     * Tool: cancelShipment
     * Safely cancel an active shipment with justification.
     */
    public function cancelShipment(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $shipmentId = $request->input('shipment_id');
        $reason = $request->input('reason', 'Cancelled by CEO executive instruction.');

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => "Shipment ID #{$shipmentId} not found."], 404);
        }

        $shipment->update([
            'status' => 'cancelled',
            'special_instructions' => "CANCELLED: " . $reason,
        ]);

        // Release associated deliveries if any
        Delivery::where('shipment_id', $shipment->id)->update(['status' => 'failed', 'failure_reason' => $reason]);

        return response()->json([
            'success' => true,
            'message' => "Shipment {$shipment->shipment_number} was successfully cancelled.",
            'shipment' => $shipment,
        ]);
    }

    /**
     * Tool: updateDeliveryStatus
     * Update delivery progress or capture proof of delivery.
     */
    public function updateDeliveryStatus(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $deliveryId = $request->input('delivery_id');
        $status = $request->input('status');
        $notes = $request->input('notes');

        $delivery = Delivery::where('company_id', $companyId)->find($deliveryId);
        if (!$delivery) {
            return response()->json(['success' => false, 'message' => "Delivery ID #{$deliveryId} not found."], 404);
        }

        $delivery->update([
            'status' => $status,
            'delivered_at' => ($status === 'completed') ? Carbon::now() : $delivery->delivered_at,
            'notes' => $notes ?: $delivery->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Delivery {$delivery->delivery_number} status updated to '{$status}'.",
            'delivery' => $delivery,
        ]);
    }

    /**
     * Tool: cancelDelivery
     * Cancel a delivery dispatch with justification reason.
     */
    public function cancelDelivery(Request $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $deliveryId = $request->input('delivery_id');
        $reason = $request->input('reason', 'Cancelled by CEO executive action.');

        $delivery = Delivery::where('company_id', $companyId)->find($deliveryId);
        if (!$delivery) {
            return response()->json(['success' => false, 'message' => "Delivery ID #{$deliveryId} not found."], 404);
        }

        $delivery->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Delivery {$delivery->delivery_number} was cancelled.",
            'delivery' => $delivery,
        ]);
    }
}
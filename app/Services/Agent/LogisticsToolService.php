<?php

namespace App\Services\Agent;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Route;
use App\Models\Shipment;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LogisticsToolService
{
    /**
     * Tool: get_executive_kpis
     */
    public function getExecutiveKpis(array $args, int $companyId = 1): array
    {
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

        return [
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
        ];
    }

    /**
     * Tool: get_critical_exceptions
     */
    public function getCriticalExceptions(array $args, int $companyId = 1): array
    {
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

        return [
            'success' => true,
            'critical_exceptions_total' => $criticalCount,
            'exceptions' => [
                'delayed_shipments' => $delayedShipments->toArray(),
                'vehicle_maintenance_and_fuel_alerts' => $maintenanceVehicles->toArray(),
                'high_risk_routes' => $highRiskRoutes->toArray(),
                'high_exposure_customers' => $overdueCustomers->toArray(),
            ],
            'timestamp' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Tool: get_fleet_status
     */
    public function getFleetStatus(array $args, int $companyId = 1): array
    {
        $status = $args['status'] ?? null;
        $fuelAlertOnly = !empty($args['low_fuel_only']);

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

        return [
            'success' => true,
            'total_vehicles' => $vehicles->count(),
            'vehicles' => $vehicles->toArray(),
            'summary' => [
                'in_transit' => $vehicles->where('status', 'in_transit')->count(),
                'active_idle' => $vehicles->where('status', 'active')->count(),
                'in_maintenance' => $vehicles->where('status', 'maintenance')->count(),
                'drivers_available' => $drivers->where('status', 'available')->count(),
                'drivers_on_trip' => $drivers->where('status', 'on_trip')->count(),
            ],
        ];
    }

    /**
     * Tool: track_consignment
     */
    public function trackConsignment(array $args, int $companyId = 1): array
    {
        $queryCode = $args['query_code'] ?? $args['tracking_number'] ?? $args['shipment_number'] ?? $args['order_number'] ?? null;

        if (!$queryCode) {
            return [
                'success' => false,
                'message' => 'Please provide query_code (e.g. TRK-1000-9999-01, SHP-260001, ORD-2026-01001, or DEL-2026-8801).',
            ];
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
            return [
                'success' => true,
                'type' => 'shipment',
                'data' => $shipment->toArray(),
            ];
        }

        $delivery = Delivery::with([
            'shipment.order.customer',
            'driver',
            'vehicle',
            'route',
        ])->where('delivery_number', $queryCode)->first();

        if ($delivery) {
            return [
                'success' => true,
                'type' => 'delivery',
                'data' => $delivery->toArray(),
            ];
        }

        return [
            'success' => false,
            'message' => "No shipment, delivery, or order found matching code '{$queryCode}'.",
        ];
    }

    /**
     * Tool: get_warehouse_capacity
     */
    public function getWarehouseCapacity(array $args, int $companyId = 1): array
    {
        $highUtilizationThreshold = (float)($args['threshold_pct'] ?? 80.0);

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

        return [
            'success' => true,
            'total_warehouses' => $warehouses->count(),
            'threshold_pct_used' => $highUtilizationThreshold,
            'bottlenecks_detected' => $bottlenecks->count(),
            'bottleneck_facilities' => $bottlenecks->toArray(),
            'all_facilities' => $warehouses->toArray(),
        ];
    }

    /**
     * Tool: get_customer_financials
     */
    public function getCustomerFinancials(array $args, int $companyId = 1): array
    {
        $customerId = $args['customer_id'] ?? null;

        $query = Customer::where('company_id', $companyId)->withCount('orders');

        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->orderByDesc('outstanding_balance')->get();

        $totalOutstanding = $customers->sum('outstanding_balance');
        $totalCreditLimit = $customers->sum('credit_limit');

        return [
            'success' => true,
            'summary' => [
                'total_outstanding_receivables' => (float)$totalOutstanding,
                'total_credit_extended' => (float)$totalCreditLimit,
                'credit_utilization_pct' => $totalCreditLimit > 0 ? round(($totalOutstanding / $totalCreditLimit) * 100, 1) : 0,
            ],
            'customers' => $customers->toArray(),
        ];
    }

    /**
     * Tool: get_shipments
     */
    public function getShipments(array $args, int $companyId = 1): array
    {
        $status = $args['status'] ?? null;
        $limit = min((int)($args['limit'] ?? 25), 100);

        $query = Shipment::with(['order.customer:id,name', 'originWarehouse:id,name,city', 'destinationWarehouse:id,name,city', 'route:id,name'])
            ->where('company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        $shipments = $query->latest()->limit($limit)->get();

        return [
            'success' => true,
            'count' => $shipments->count(),
            'data' => $shipments->toArray(),
        ];
    }

    /**
     * Tool: get_deliveries
     */
    public function getDeliveries(array $args, int $companyId = 1): array
    {
        $status = $args['status'] ?? null;
        $limit = min((int)($args['limit'] ?? 25), 100);

        $query = Delivery::with(['driver:id,first_name,last_name,phone', 'vehicle:id,vehicle_code,plate_number', 'shipment:id,tracking_number,shipment_number'])
            ->where('company_id', $companyId);

        if ($status) {
            $query->where('status', $status);
        }

        $deliveries = $query->latest()->limit($limit)->get();

        return [
            'success' => true,
            'count' => $deliveries->count(),
            'data' => $deliveries->toArray(),
        ];
    }

    /**
     * Tool: get_drivers
     */
    public function getDrivers(array $args, int $companyId = 1): array
    {
        $status = $args['status'] ?? null;
        $limit = min((int)($args['limit'] ?? 25), 100);

        $query = Driver::where('company_id', $companyId);
        if ($status) {
            $query->where('status', $status);
        }

        $drivers = $query->limit($limit)->get();

        return [
            'success' => true,
            'count' => $drivers->count(),
            'data' => $drivers->toArray(),
        ];
    }

    /**
     * Tool: get_vehicles
     */
    public function getVehicles(array $args, int $companyId = 1): array
    {
        $status = $args['status'] ?? null;
        $limit = min((int)($args['limit'] ?? 25), 100);

        $query = Vehicle::with('currentDriver:id,first_name,last_name')->where('company_id', $companyId);
        if ($status) {
            $query->where('status', $status);
        }

        $vehicles = $query->limit($limit)->get();

        return [
            'success' => true,
            'count' => $vehicles->count(),
            'data' => $vehicles->toArray(),
        ];
    }

    /**
     * Tool: assign_shipment_dispatch
     */
    public function assignShipmentDispatch(array $args, int $companyId = 1): array
    {
        $shipmentId = $args['shipment_id'] ?? null;
        $driverId = $args['driver_id'] ?? null;
        $vehicleId = $args['vehicle_id'] ?? null;

        if (!$shipmentId) {
            return ['success' => false, 'message' => 'shipment_id is required.'];
        }

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return ['success' => false, 'message' => "Shipment ID #{$shipmentId} not found in company."];
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
            return [
                'success' => false,
                'message' => 'Insufficient available resources: Driver or Vehicle unavailable for assignment.',
            ];
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

        return [
            'success' => true,
            'message' => "Dispatch executed successfully: Assigned Driver {$bestDriver->first_name} {$bestDriver->last_name} ({$bestDriver->driver_code}) and Vehicle {$bestVehicle->vehicle_code}.",
            'shipment' => $shipment->fresh()->toArray(),
            'delivery' => $delivery->load(['driver:id,first_name,last_name,phone', 'vehicle:id,vehicle_code,plate_number'])->toArray(),
        ];
    }

    /**
     * Tool: update_shipment_status
     */
    public function updateShipmentStatus(array $args, int $companyId = 1): array
    {
        $shipmentId = $args['shipment_id'] ?? null;
        $status = $args['status'] ?? null;
        $notes = $args['notes'] ?? 'Status updated by CEO Agent Action';

        if (!$shipmentId || !$status) {
            return ['success' => false, 'message' => 'shipment_id and status are required.'];
        }

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return ['success' => false, 'message' => "Shipment ID #{$shipmentId} not found."];
        }

        $shipment->update([
            'status' => $status,
            'special_instructions' => $shipment->special_instructions ? ($shipment->special_instructions . " | " . $notes) : $notes,
        ]);

        return [
            'success' => true,
            'message' => "Shipment {$shipment->shipment_number} status updated to '{$status}'.",
            'shipment' => $shipment->toArray(),
        ];
    }

    /**
     * Tool: cancel_shipment
     */
    public function cancelShipment(array $args, int $companyId = 1): array
    {
        $shipmentId = $args['shipment_id'] ?? null;
        $reason = $args['reason'] ?? 'Cancelled by CEO executive instruction.';

        if (!$shipmentId) {
            return ['success' => false, 'message' => 'shipment_id is required.'];
        }

        $shipment = Shipment::where('company_id', $companyId)->find($shipmentId);
        if (!$shipment) {
            return ['success' => false, 'message' => "Shipment ID #{$shipmentId} not found."];
        }

        $shipment->update([
            'status' => 'cancelled',
            'special_instructions' => "CANCELLED: " . $reason,
        ]);

        Delivery::where('shipment_id', $shipment->id)->update(['status' => 'failed', 'failure_reason' => $reason]);

        return [
            'success' => true,
            'message' => "Shipment {$shipment->shipment_number} was successfully cancelled.",
            'shipment' => $shipment->toArray(),
        ];
    }

    /**
     * Tool: update_delivery_status
     */
    public function updateDeliveryStatus(array $args, int $companyId = 1): array
    {
        $deliveryId = $args['delivery_id'] ?? null;
        $status = $args['status'] ?? null;
        $notes = $args['notes'] ?? null;

        if (!$deliveryId || !$status) {
            return ['success' => false, 'message' => 'delivery_id and status are required.'];
        }

        $delivery = Delivery::where('company_id', $companyId)->find($deliveryId);
        if (!$delivery) {
            return ['success' => false, 'message' => "Delivery ID #{$deliveryId} not found."];
        }

        $delivery->update([
            'status' => $status,
            'delivered_at' => ($status === 'completed') ? Carbon::now() : $delivery->delivered_at,
            'notes' => $notes ?: $delivery->notes,
        ]);

        return [
            'success' => true,
            'message' => "Delivery {$delivery->delivery_number} status updated to '{$status}'.",
            'delivery' => $delivery->toArray(),
        ];
    }

    /**
     * Tool: cancel_delivery
     */
    public function cancelDelivery(array $args, int $companyId = 1): array
    {
        $deliveryId = $args['delivery_id'] ?? null;
        $reason = $args['reason'] ?? 'Cancelled by CEO executive action.';

        if (!$deliveryId) {
            return ['success' => false, 'message' => 'delivery_id is required.'];
        }

        $delivery = Delivery::where('company_id', $companyId)->find($deliveryId);
        if (!$delivery) {
            return ['success' => false, 'message' => "Delivery ID #{$deliveryId} not found."];
        }

        $delivery->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);

        return [
            'success' => true,
            'message' => "Delivery {$delivery->delivery_number} was cancelled.",
            'delivery' => $delivery->toArray(),
        ];
    }
}

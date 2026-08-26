<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Shipment::with([
            'company:id,name,code',
            'order:id,order_number,customer_id,total_amount,priority',
            'order.customer:id,name,customer_code',
            'originWarehouse:id,code,name,city',
            'destinationWarehouse:id,code,name,city',
            'route:id,route_code,name',
        ])->withCount('deliveries');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('carrier_type')) {
            $query->where('carrier_type', $request->query('carrier_type'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('carrier_name', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $shipments = $query->latest('id')->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'pagination' => [
                'total' => $shipments->total(),
                'per_page' => $shipments->perPage(),
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $shipment = Shipment::with([
            'company',
            'order.customer',
            'originWarehouse',
            'destinationWarehouse',
            'route',
            'deliveries.driver',
            'deliveries.vehicle',
        ])->find($id);

        if (!$shipment) {
            // Also attempt to find by tracking_number or shipment_number
            $shipment = Shipment::with([
                'company',
                'order.customer',
                'originWarehouse',
                'destinationWarehouse',
                'route',
                'deliveries.driver',
                'deliveries.vehicle',
            ])->where('tracking_number', $id)->orWhere('shipment_number', $id)->first();
        }

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $shipment]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'order_id' => 'required|exists:orders,id',
            'origin_warehouse_id' => 'nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'route_id' => 'nullable|exists:routes,id',
            'shipment_number' => 'required|string|max:50|unique:shipments,shipment_number',
            'tracking_number' => 'required|string|max:100|unique:shipments,tracking_number',
            'carrier_type' => 'nullable|in:in_house,third_party_3pl,partner_fleet',
            'carrier_name' => 'nullable|string|max:255',
            'temperature_controlled' => 'nullable|boolean',
            'target_temp_celsius' => 'nullable|numeric',
            'status' => 'nullable|in:manifested,picked_up,in_transit,out_for_delivery,delivered,delayed,exception',
            'estimated_departure' => 'nullable|date',
            'actual_departure' => 'nullable|date',
            'estimated_arrival' => 'nullable|date',
            'actual_arrival' => 'nullable|date',
            'special_instructions' => 'nullable|string',
            'timeline_events' => 'nullable|array',
        ]);

        $shipment = Shipment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully',
            'data' => $shipment,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'order_id' => 'sometimes|required|exists:orders,id',
            'origin_warehouse_id' => 'nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'route_id' => 'nullable|exists:routes,id',
            'shipment_number' => 'sometimes|required|string|max:50|unique:shipments,shipment_number,' . $id,
            'tracking_number' => 'sometimes|required|string|max:100|unique:shipments,tracking_number,' . $id,
            'carrier_type' => 'nullable|in:in_house,third_party_3pl,partner_fleet',
            'carrier_name' => 'nullable|string|max:255',
            'temperature_controlled' => 'nullable|boolean',
            'target_temp_celsius' => 'nullable|numeric',
            'status' => 'nullable|in:manifested,picked_up,in_transit,out_for_delivery,delivered,delayed,exception',
            'estimated_departure' => 'nullable|date',
            'actual_departure' => 'nullable|date',
            'estimated_arrival' => 'nullable|date',
            'actual_arrival' => 'nullable|date',
            'special_instructions' => 'nullable|string',
            'timeline_events' => 'nullable|array',
        ]);

        $shipment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully',
            'data' => $shipment,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found'], 404);
        }

        $shipment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shipment deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Delivery::with([
            'company:id,name,code',
            'shipment:id,shipment_number,tracking_number,status,order_id',
            'shipment.order.customer:id,name,customer_code',
            'driver:id,driver_code,first_name,last_name,phone',
            'vehicle:id,vehicle_code,plate_number,model',
            'route:id,route_code,name',
        ]);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }

        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->query('driver_id'));
        }

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->query('vehicle_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhere('delivery_city', 'like', "%{$search}%")
                  ->orWhereHas('driver', function ($dq) use ($search) {
                      $dq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('driver_code', 'like', "%{$search}%");
                  });
            });
        }

        $deliveries = $query->latest('id')->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'pagination' => [
                'total' => $deliveries->total(),
                'per_page' => $deliveries->perPage(),
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $delivery = Delivery::with([
            'company',
            'shipment.order.customer',
            'shipment.originWarehouse',
            'shipment.destinationWarehouse',
            'driver',
            'vehicle',
            'route',
        ])->find($id);

        if (!$delivery) {
            $delivery = Delivery::with([
                'company',
                'shipment.order.customer',
                'driver',
                'vehicle',
                'route',
            ])->where('delivery_number', $id)->first();
        }

        if (!$delivery) {
            return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $delivery]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'shipment_id' => 'required|exists:shipments,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_id' => 'nullable|exists:routes,id',
            'delivery_number' => 'required|string|max:50|unique:deliveries,delivery_number',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'delivery_address' => 'required|string',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
            'scheduled_window_start' => 'nullable|date',
            'scheduled_window_end' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'proof_of_delivery_signature' => 'nullable|string|max:255',
            'proof_of_delivery_photo_url' => 'nullable|url',
            'customer_feedback_rating' => 'nullable|integer|between:1,5',
            'status' => 'nullable|in:pending,dispatched,en_route,arrived,completed,failed,rescheduled',
            'failure_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $delivery = Delivery::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Delivery created successfully',
            'data' => $delivery,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $delivery = Delivery::find($id);

        if (!$delivery) {
            return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'shipment_id' => 'sometimes|required|exists:shipments,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'route_id' => 'nullable|exists:routes,id',
            'delivery_number' => 'sometimes|required|string|max:50|unique:deliveries,delivery_number,' . $id,
            'recipient_name' => 'sometimes|required|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'delivery_address' => 'sometimes|required|string',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
            'scheduled_window_start' => 'nullable|date',
            'scheduled_window_end' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'proof_of_delivery_signature' => 'nullable|string|max:255',
            'proof_of_delivery_photo_url' => 'nullable|url',
            'customer_feedback_rating' => 'nullable|integer|between:1,5',
            'status' => 'nullable|in:pending,dispatched,en_route,arrived,completed,failed,rescheduled',
            'failure_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $delivery->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Delivery updated successfully',
            'data' => $delivery,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $delivery = Delivery::find($id);

        if (!$delivery) {
            return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
        }

        $delivery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery deleted successfully',
        ]);
    }
}

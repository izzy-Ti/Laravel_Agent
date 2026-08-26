<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['company:id,name,code', 'customer:id,name,customer_code,company_name'])
            ->withCount('shipments');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest('id')->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $order = Order::with([
            'company',
            'customer',
            'shipments.originWarehouse',
            'shipments.destinationWarehouse',
            'shipments.deliveries.driver',
        ])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'customer_id' => 'required|exists:customers,id',
            'order_number' => 'required|string|max:50|unique:orders,order_number',
            'order_date' => 'required|date',
            'required_delivery_date' => 'nullable|date',
            'priority' => 'nullable|in:standard,express,critical,same_day',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'payment_status' => 'nullable|in:paid,pending,net_30,overdue,failed',
            'status' => 'nullable|in:draft,confirmed,processing,manifested,shipped,delivered,cancelled,returned',
            'items_count' => 'nullable|integer|min:1',
            'total_weight_kg' => 'nullable|numeric|min:0',
            'total_volume_cbm' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'order_items' => 'nullable|array',
        ]);

        $order = Order::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order->load('customer:id,name,customer_code'),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'customer_id' => 'sometimes|required|exists:customers,id',
            'order_number' => 'sometimes|required|string|max:50|unique:orders,order_number,' . $id,
            'order_date' => 'sometimes|required|date',
            'required_delivery_date' => 'nullable|date',
            'priority' => 'nullable|in:standard,express,critical,same_day',
            'total_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'payment_status' => 'nullable|in:paid,pending,net_30,overdue,failed',
            'status' => 'nullable|in:draft,confirmed,processing,manifested,shipped,delivered,cancelled,returned',
            'items_count' => 'nullable|integer|min:1',
            'total_weight_kg' => 'nullable|numeric|min:0',
            'total_volume_cbm' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'order_items' => 'nullable|array',
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order->load('customer:id,name,customer_code'),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully',
        ]);
    }
}

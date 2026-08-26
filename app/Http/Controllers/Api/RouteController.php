<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Route::with([
            'company:id,name,code',
            'originWarehouse:id,code,name,city',
            'destinationWarehouse:id,code,name,city',
        ])->withCount('shipments');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }

        if ($request->has('risk_level')) {
            $query->where('risk_level', $request->query('risk_level'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('route_code', 'like', "%{$search}%")
                  ->orWhere('origin_name', 'like', "%{$search}%")
                  ->orWhere('destination_name', 'like', "%{$search}%");
            });
        }

        $routes = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $routes->items(),
            'pagination' => [
                'total' => $routes->total(),
                'per_page' => $routes->perPage(),
                'current_page' => $routes->currentPage(),
                'last_page' => $routes->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $route = Route::with([
            'company',
            'originWarehouse',
            'destinationWarehouse',
            'shipments' => function ($q) {
                $q->latest()->limit(10);
            },
        ])->find($id);

        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Route not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $route]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'origin_warehouse_id' => 'nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'route_code' => 'required|string|max:50|unique:routes,route_code',
            'name' => 'required|string|max:255',
            'origin_name' => 'required|string|max:255',
            'destination_name' => 'required|string|max:255',
            'origin_latitude' => 'nullable|numeric|between:-90,90',
            'origin_longitude' => 'nullable|numeric|between:-180,180',
            'destination_latitude' => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'distance_km' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'toll_cost' => 'nullable|numeric|min:0',
            'fuel_consumption_liters' => 'nullable|numeric|min:0',
            'waypoints' => 'nullable|array',
            'risk_level' => 'nullable|in:low,medium,high,severe_weather',
            'status' => 'nullable|in:active,congested,closed,alternative',
        ]);

        $route = Route::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Route created successfully',
            'data' => $route,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Route not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'origin_warehouse_id' => 'nullable|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'route_code' => 'sometimes|required|string|max:50|unique:routes,route_code,' . $id,
            'name' => 'sometimes|required|string|max:255',
            'origin_name' => 'sometimes|required|string|max:255',
            'destination_name' => 'sometimes|required|string|max:255',
            'origin_latitude' => 'nullable|numeric|between:-90,90',
            'origin_longitude' => 'nullable|numeric|between:-180,180',
            'destination_latitude' => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'distance_km' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'toll_cost' => 'nullable|numeric|min:0',
            'fuel_consumption_liters' => 'nullable|numeric|min:0',
            'waypoints' => 'nullable|array',
            'risk_level' => 'nullable|in:low,medium,high,severe_weather',
            'status' => 'nullable|in:active,congested,closed,alternative',
        ]);

        $route->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Route updated successfully',
            'data' => $route,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Route not found'], 404);
        }

        $route->delete();

        return response()->json([
            'success' => true,
            'message' => 'Route deleted successfully',
        ]);
    }
}

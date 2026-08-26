<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::with('company:id,name,code')
            ->withCount(['outboundShipments', 'inboundShipments']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $warehouses->items(),
            'pagination' => [
                'total' => $warehouses->total(),
                'per_page' => $warehouses->perPage(),
                'current_page' => $warehouses->currentPage(),
                'last_page' => $warehouses->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $warehouse = Warehouse::with([
            'company',
            'outboundShipments' => function ($q) {
                $q->latest()->limit(5);
            },
            'inboundShipments' => function ($q) {
                $q->latest()->limit(5);
            },
        ])->find($id);

        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Warehouse not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $warehouse]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'capacity_sqft' => 'nullable|integer|min:0',
            'current_utilization_pct' => 'nullable|numeric|between:0,100',
            'type' => 'nullable|in:fulfillment,distribution,cross_dock,cold_storage,bonded',
            'operating_hours' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:50',
            'manager_email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,maintenance,closed',
        ]);

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => $warehouse,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Warehouse not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'code' => 'sometimes|required|string|max:50|unique:warehouses,code,' . $id,
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'capacity_sqft' => 'nullable|integer|min:0',
            'current_utilization_pct' => 'nullable|numeric|between:0,100',
            'type' => 'nullable|in:fulfillment,distribution,cross_dock,cold_storage,bonded',
            'operating_hours' => 'nullable|string|max:100',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:50',
            'manager_email' => 'nullable|email|max:255',
            'status' => 'nullable|in:active,maintenance,closed',
        ]);

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
            'data' => $warehouse,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $warehouse = Warehouse::find($id);

        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Warehouse not found'], 404);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
        ]);
    }
}

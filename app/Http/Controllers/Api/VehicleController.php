<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with(['company:id,name,code', 'currentDriver:id,driver_code,first_name,last_name,phone']);

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
                $q->where('vehicle_code', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'pagination' => [
                'total' => $vehicles->total(),
                'per_page' => $vehicles->perPage(),
                'current_page' => $vehicles->currentPage(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $vehicle = Vehicle::with(['company', 'currentDriver', 'deliveries' => function ($q) {
            $q->latest()->limit(10);
        }])->find($id);

        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Vehicle not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $vehicle]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'current_driver_id' => 'nullable|exists:drivers,id',
            'vehicle_code' => 'required|string|max:50|unique:vehicles,vehicle_code',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number',
            'vin' => 'required|string|max:100|unique:vehicles,vin',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 2),
            'type' => 'nullable|in:semi_truck,box_truck,cargo_van,flatbed,refrigerated,electric_van',
            'max_weight_kg' => 'nullable|numeric|min:0',
            'max_volume_cbm' => 'nullable|numeric|min:0',
            'odometer_km' => 'nullable|numeric|min:0',
            'fuel_type' => 'nullable|in:diesel,electric,hybrid,cng',
            'fuel_level_pct' => 'nullable|numeric|between:0,100',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:active,in_transit,maintenance,idle,decommissioned',
            'last_maintenance_at' => 'nullable|date',
            'next_maintenance_at' => 'nullable|date',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicle,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Vehicle not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'current_driver_id' => 'nullable|exists:drivers,id',
            'vehicle_code' => 'sometimes|required|string|max:50|unique:vehicles,vehicle_code,' . $id,
            'plate_number' => 'sometimes|required|string|max:50|unique:vehicles,plate_number,' . $id,
            'vin' => 'sometimes|required|string|max:100|unique:vehicles,vin,' . $id,
            'make' => 'sometimes|required|string|max:100',
            'model' => 'sometimes|required|string|max:100',
            'year' => 'sometimes|required|integer|min:1990|max:' . (date('Y') + 2),
            'type' => 'nullable|in:semi_truck,box_truck,cargo_van,flatbed,refrigerated,electric_van',
            'max_weight_kg' => 'nullable|numeric|min:0',
            'max_volume_cbm' => 'nullable|numeric|min:0',
            'odometer_km' => 'nullable|numeric|min:0',
            'fuel_type' => 'nullable|in:diesel,electric,hybrid,cng',
            'fuel_level_pct' => 'nullable|numeric|between:0,100',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:active,in_transit,maintenance,idle,decommissioned',
            'last_maintenance_at' => 'nullable|date',
            'next_maintenance_at' => 'nullable|date',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['success' => false, 'message' => 'Vehicle not found'], 404);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully',
        ]);
    }
}

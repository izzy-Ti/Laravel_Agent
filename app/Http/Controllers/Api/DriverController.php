<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Driver::with(['company:id,name,code', 'currentVehicle', 'user:id,name,email'])
            ->withCount('deliveries');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('driver_code', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $drivers = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $drivers->items(),
            'pagination' => [
                'total' => $drivers->total(),
                'per_page' => $drivers->perPage(),
                'current_page' => $drivers->currentPage(),
                'last_page' => $drivers->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $driver = Driver::with([
            'company',
            'user',
            'currentVehicle',
            'deliveries' => function ($q) {
                $q->latest()->limit(10);
            },
        ])->find($id);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $driver]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'driver_code' => 'required|string|max:50|unique:drivers,driver_code',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'license_number' => 'required|string|max:100|unique:drivers,license_number',
            'license_type' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'phone' => 'required|string|max:50',
            'emergency_contact' => 'nullable|string|max:255',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:available,on_trip,off_duty,on_break,suspended',
            'safety_score' => 'nullable|numeric|between:0,100',
            'rating' => 'nullable|numeric|between:0,5',
            'total_trips' => 'nullable|integer|min:0',
            'total_distance_km' => 'nullable|numeric|min:0',
        ]);

        $driver = Driver::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver created successfully',
            'data' => $driver,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $validated = $request->validate([
            'company_id' => 'sometimes|required|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'driver_code' => 'sometimes|required|string|max:50|unique:drivers,driver_code,' . $id,
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'license_number' => 'sometimes|required|string|max:100|unique:drivers,license_number,' . $id,
            'license_type' => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'phone' => 'sometimes|required|string|max:50',
            'emergency_contact' => 'nullable|string|max:255',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|in:available,on_trip,off_duty,on_break,suspended',
            'safety_score' => 'nullable|numeric|between:0,100',
            'rating' => 'nullable|numeric|between:0,5',
            'total_trips' => 'nullable|integer|min:0',
            'total_distance_km' => 'nullable|numeric|min:0',
        ]);

        $driver->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Driver updated successfully',
            'data' => $driver,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $driver = Driver::find($id);

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
        }

        $driver->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver deleted successfully',
        ]);
    }
}

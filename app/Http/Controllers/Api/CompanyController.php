<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Company::withCount(['warehouses', 'drivers', 'vehicles', 'orders', 'shipments']);

        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $companies = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $companies->items(),
            'pagination' => [
                'total' => $companies->total(),
                'per_page' => $companies->perPage(),
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $company = Company::with(['warehouses', 'drivers.currentVehicle', 'vehicles', 'customers'])
            ->withCount(['orders', 'shipments', 'deliveries'])
            ->find($id);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $company]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'headquarters_address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'fleet_size' => 'nullable|integer|min:0',
            'ceo_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,suspended',
            'metadata' => 'nullable|array',
        ]);

        $company = Company::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'data' => $company,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:companies,code,' . $id,
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'headquarters_address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:50',
            'currency' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:50',
            'fleet_size' => 'nullable|integer|min:0',
            'ceo_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,suspended',
            'metadata' => 'nullable|array',
        ]);

        $company->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => $company,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);
    }
}

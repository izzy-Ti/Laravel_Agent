# 🚛 Logistics CEO Agent — Enterprise Source of Truth REST API & Tool Suite

The definitive operational source of truth and autonomous tool-calling REST API for the **Logistics Chief Executive Officer (CEO) Agent**. Built on Laravel with full PostgreSQL / Supabase readiness.

---

## 🌟 The 10 Core Logistics Domain Entities

This API implements full relational database models, migrations, relationships, seeders, and RESTful CRUD endpoints for all 10 core logistics entities:

1. **`Users`** (`/api/users`) — Logistics staff, dispatchers, drivers, operations managers, and executive leadership.
2. **`Companies`** (`/api/companies`) — Enterprise multi-tenant freight carriers, headquarters, and fleet configuration.
3. **`Drivers`** (`/api/drivers`) — Commercial CDL drivers, live GPS coordinates, safety scores (e.g. 99.4/100), and duty status.
4. **`Vehicles`** (`/api/vehicles`) — Heavy trucks (Cascadia, Volvo VNL), Reefers, Electric Vans, VINs, fuel levels, and maintenance schedules.
5. **`Orders`** (`/api/orders`) — Commercial freight purchase orders, line items, cargo weights, volumes, and billing.
6. **`Shipments`** (`/api/shipments`) — Line-haul consignments, tracking numbers (`TRK-...`), origin/destination warehouses, and cold chain logs.
7. **`Deliveries`** (`/api/deliveries`) — Final-mile dispatch execution, proof of delivery (POD), driver assignment, and recipient signatures.
8. **`Routes`** (`/api/routes`) — Standard interstate corridors, waypoints, tolls, fuel estimates, and weather risk levels.
9. **`Warehouses`** (`/api/warehouses`) — Regional superhubs (Chicago, LA, Dallas, Atlanta), storage capacities, and utilization rates.
10. **`Customers`** (`/api/customers`) — Commercial enterprise accounts (Tesla, Apple, BioNova Pharma, Whole Foods) with credit limits & receivables.

---

## 🐘 Supabase PostgreSQL Integration

The API is pre-configured for Supabase PostgreSQL. Configure your `.env`:

```env
# ==============================================================================
# DATABASE CONFIGURATION: SUPABASE POSTGRESQL
# ==============================================================================
DB_CONNECTION=pgsql
DB_HOST=aws-0-us-east-1.pooler.supabase.com   # Or your project host
DB_PORT=5432                                  # 5432 (direct) or 6543 (pooler)
DB_DATABASE=postgres
DB_USERNAME=postgres.your-project-ref
DB_PASSWORD=your-supabase-db-password
DB_SSLMODE=require

# Supabase REST / Storage / Auth API Credentials
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsIn...
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsIn...
```

To run migrations and seed realistic logistics data on Supabase:
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 🤖 Microsoft Copilot Studio & CEO Agent AI Tool Suite

### 1. Executive Intelligence (`EXECUTIVE_INTELLIGENCE`)
- **`getExecutiveKpis`** (`GET /api/agent/kpis`): Gross revenue, active shipments, fleet utilization %, OTD %, warehouse capacity %, and driver safety score.
- **`getCriticalExceptions`** (`GET /api/agent/critical-exceptions`): Active delayed loads, breakdown/fuel alerts, severe weather routes, and credit exposure.

### 2. Read Logistics Data (`READ_LOGISTICS_DATA`)
- **`getFleetStatus`** (`GET /api/agent/fleet-status`): Live GPS telematics, truck operational status, and low-fuel alerts (<25%).
- **`trackConsignment`** (`GET /api/agent/track`): Universal consignment tracker by `TRK-...`, `SHP-...`, `ORD-...`, or `DEL-...`.
- **`getWarehouseCapacity`** (`GET /api/agent/warehouse-capacity`): Regional distribution superhub square-footage utilization and bottleneck detection (>80%).
- **`getCustomerFinancials`** (`GET /api/agent/customer-financials`): B2B receivables, outstanding balances, and credit facility limits.
- **`getShipments`** (`GET /api/agent/shipments`): Active line-haul shipments with origin/destination hubs and NIST cold chain logs.
- **`getDeliveries`** (`GET /api/agent/deliveries`): Final-mile deliveries, driver assignments, and POD capture.
- **`getDrivers`** (`GET /api/agent/drivers`): Commercial CDL-A drivers, safety scores, and status.
- **`getVehicles`** (`GET /api/agent/vehicles`): Fleet trucks, reefers, payload capacities, and maintenance schedules.

### 3. Action Logistics Data (`ACTION_LOGISTICS_DATA`)
- **`assignShipmentDispatch`** (`POST /api/agent/dispatch`): Assign driver and vehicle to shipment with automated resource selection if omitted.
- **`updateShipmentStatus`** (`POST /api/agent/update-shipment-status`): Update consignment status with audit instructions.
- **`cancelShipment`** (`POST /api/agent/cancel-shipment`): Safely cancel shipment and release final-mile deliveries.
- **`updateDeliveryStatus`** (`POST /api/agent/update-delivery-status`): Update delivery progression and record POD timestamps.
- **`cancelDelivery`** (`POST /api/agent/cancel-delivery`): Cancel delivery dispatches with reason.

---

## 🚀 Quick Start & Copilot Studio Integration

1. Start the server:
   ```bash
   php artisan serve
   ```
2. Import `http://localhost:8000/openapi.json` into **Microsoft Copilot Studio** (Actions / Custom Plugins).
3. Authenticate with your Bearer Token / API Key (`X-Company-ID`).

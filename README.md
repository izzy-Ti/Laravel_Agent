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

## 🤖 Copilot Studio & CEO Agent AI Tool Endpoints

| Endpoint | Method | Purpose |
| :--- | :--- | :--- |
| `/api/agent/ceo-kpis` | `GET` | High-level CEO Executive metrics (Gross revenue, Active loads, OTD %, Fleet utilization, Safety) |
| `/api/agent/tools` | `GET` | OpenAI, Claude, Gemini, and Microsoft Copilot Studio tool calling schema definition |
| `/api/agent/execute` | `POST` | Universal AI tool dispatcher (Execute any tool dynamically by name) |
| `/api/agent/fleet-status` | `GET/POST` | Live GPS telematics, driver status, and low-fuel truck alerts |
| `/api/agent/track` | `GET/POST` | Track any consignment by `tracking_number`, `shipment_number`, `order_number`, or `delivery_number` |
| `/api/agent/warehouse-capacity` | `GET/POST` | Inspect warehouse capacity bottlenecks (>80% utilization) |
| `/api/agent/critical-exceptions` | `GET/POST` | Audit delayed shipments, breakdown alerts, and credit risk |
| `/api/agent/optimize-dispatch` | `POST` | Autonomously allocate optimal driver & vehicle to active shipments |
| `/api/agent/customer-financials` | `GET/POST` | Inspect commercial accounts receivables and credit utilization |
| `/openapi.json` | `GET` | OpenAPI 3.1 Specification for Microsoft Copilot Studio, LangChain, Dify, or Cursor |

---

## 🚀 Quick Start

1. Start development server:
   ```bash
   php artisan serve
   ```
2. Open the Executive Command Center in your browser at `http://localhost:8000`.
3. Connect Microsoft Copilot Studio or any AI agent to `/openapi.json` to enable autonomous tool calling against the live Neon PostgreSQL database.

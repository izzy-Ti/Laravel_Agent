<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics ERP Source of Truth & CEO Agent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1e60f2;
            --primary-hover: #154ec4;
            --header-bg: #1e60f2;
            --sidebar-bg: #131926;
            --sidebar-dark: #0d121c;
            --sidebar-border: #222b3d;
            
            --page-bg: #eaedf2;
            --card-bg: #ffffff;
            --border-color: #cbd5e1;
            --border-light: #e2e8f0;
            
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --text-white: #ffffff;
            
            --emerald: #10b981;
            --rose: #ef4444;
            --amber: #f59e0b;
            --cyan: #0284c7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--page-bg);
            color: var(--text-dark);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 12.5px;
            line-height: 1.4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        code, pre, .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Top ERP Navbar */
        .top-navbar {
            background-color: var(--header-bg);
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            color: var(--text-white);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--text-white);
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .logo-icon {
            width: 28px;
            height: 28px;
            background: #ffffff;
            color: var(--primary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 15px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-link-btn {
            color: rgba(255, 255, 255, 0.85);
            background: transparent;
            border: none;
            font-weight: 500;
            font-size: 12.5px;
            padding: 6px 10px;
            border-radius: 4px;
            transition: all 0.15s;
            cursor: pointer;
            text-decoration: none;
        }

        .nav-link-btn:hover, .nav-link-btn.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
            font-weight: 600;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .db-status-pill {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            padding: 3px 10px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 8px #4ade80;
        }

        .user-pill {
            background: #ffffff;
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 11.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Main ERP Layout */
        .erp-layout {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 48px);
        }

        /* Left Dark Sidebar / Job Inspector */
        .left-sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: var(--text-white);
            border-right: 1px solid var(--sidebar-border);
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-shrink: 0;
        }

        .sidebar-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #ffffff;
        }

        .search-row {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-input {
            width: 100%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 7px 10px;
            font-size: 12.5px;
            font-weight: 600;
            color: #0f172a;
            outline: none;
        }

        .btn-proceed {
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 7px 0;
            width: 100%;
            font-weight: 600;
            font-size: 12.5px;
            cursor: pointer;
            transition: background 0.15s;
            text-align: center;
        }

        .btn-proceed:hover {
            background: var(--primary-hover);
        }

        .sidebar-card {
            background: var(--sidebar-dark);
            border: 1px solid var(--sidebar-border);
            border-radius: 4px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
        }

        .meta-label {
            color: var(--text-light);
        }

        .meta-val {
            color: #ffffff;
            font-weight: 600;
            text-align: right;
        }

        /* Right Main Content */
        .main-content {
            flex: 1;
            padding: 14px 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-x: auto;
        }

        /* Metric Summary Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        .metric-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
        }

        .metric-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .metric-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-top: 2px;
        }

        .metric-subtitle {
            font-size: 10.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Entity Subtabs Bar */
        .entity-tabs-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            gap: 10px;
            overflow-x: auto;
        }

        .tab-buttons {
            display: flex;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
        }

        .entity-tab {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
        }

        .entity-tab:hover {
            background: #f1f5f9;
            color: var(--text-dark);
        }

        .entity-tab.active {
            background: #eff6ff;
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Table Card */
        .table-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .search-input {
            width: 280px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 12px;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--primary);
        }

        .badge-count {
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border-light);
            border-radius: 4px;
            max-height: 480px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            padding: 8px 10px;
            border-bottom: 2px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 2;
            white-space: nowrap;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            vertical-align: middle;
            white-space: nowrap;
        }

        tr:hover td {
            background: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 12px;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-in_transit, .badge-en_route, .badge-shipped, .badge-active {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-delivered, .badge-completed, .badge-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-delayed, .badge-failed, .badge-maintenance, .badge-critical {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .badge-pending, .badge-processing, .badge-net_30 {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        /* CEO Agent AI Terminal Card */
        .ceo-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .terminal-box {
            background: #0f172a;
            color: #f8fafc;
            border-radius: 4px;
            padding: 12px;
            height: 220px;
            overflow-y: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11.5px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-input-row {
            display: flex;
            gap: 8px;
        }

        .chat-input {
            flex: 1;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 7px 12px;
            font-size: 12.5px;
            outline: none;
        }

        .chat-input:focus {
            border-color: var(--primary);
        }

        .btn {
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-outline {
            background: #ffffff;
            border-color: var(--border-color);
            color: var(--text-dark);
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }

        .chips-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .chip-btn {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            color: #475569;
            cursor: pointer;
        }

        .chip-btn:hover {
            background: #eff6ff;
            border-color: var(--primary);
            color: var(--primary);
        }
    </style>
</head>
<body>

    <!-- 1. Top ERP Royal Blue Navbar -->
    <header class="top-navbar">
        <div class="nav-left">
            <a href="/" class="logo-box">
                <div class="logo-icon">P</div>
                <span>LogisticsERP</span>
            </a>
            <nav class="nav-links">
                <button class="nav-link-btn active" onclick="switchEntity('shipments')">Shipments</button>
                <button class="nav-link-btn" onclick="switchEntity('orders')">Orders</button>
                <button class="nav-link-btn" onclick="switchEntity('deliveries')">Deliveries</button>
                <button class="nav-link-btn" onclick="switchEntity('vehicles')">Vehicles</button>
                <button class="nav-link-btn" onclick="switchEntity('drivers')">Drivers</button>
                <button class="nav-link-btn" onclick="switchEntity('warehouses')">Warehouses</button>
                <button class="nav-link-btn" onclick="switchEntity('customers')">Customers</button>
                <button class="nav-link-btn" onclick="switchEntity('routes')">Routes</button>
                <button class="nav-link-btn" onclick="switchEntity('companies')">Companies</button>
                <button class="nav-link-btn" onclick="switchEntity('users')">Users</button>
                <a href="/openapi.json" target="_blank" class="nav-link-btn">OpenAPI Spec</a>
            </nav>
        </div>
        <div class="nav-right">
            <div class="db-status-pill">
                <span class="status-dot"></span>
                <span>Neon PostgreSQL (Live DB)</span>
            </div>
            <div class="user-pill">
                <span>Admin</span>
                <span style="font-size: 14px; line-height: 1;">&bull;&bull;&bull;</span>
            </div>
        </div>
    </header>

    <!-- 2. Main ERP Frame Layout -->
    <div class="erp-layout">

        <!-- 2.1 Left Dark Sidebar (Consignment & Job Inspector) -->
        <aside class="left-sidebar">
            <div class="sidebar-title">Job Inspector</div>
            
            <div class="search-row">
                <input type="text" id="jobSearchCode" class="sidebar-input" value="SHP-ORD-DFW-2601" placeholder="Tracking / Shipment Code...">
                <button class="btn-proceed" onclick="inspectJobCode()">Proceed / Lookup</button>
            </div>

            <!-- Live Database Consignment Metadata -->
            <div class="sidebar-card">
                <div class="meta-row">
                    <span class="meta-label">Consignment</span>
                    <span class="meta-val mono" id="sideShpNumber" style="color:#38bdf8;">SHP-ORD-DFW-2601</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Tracking Code</span>
                    <span class="meta-val mono" id="sideTracking">TRK-9832-7491-01</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Order #</span>
                    <span class="meta-val" id="sideOrder">ORD-2026-1001</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Customer</span>
                    <span class="meta-val" id="sideCustomer">Tesla Gigafactory</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Status</span>
                    <span class="meta-val" id="sideStatus"><span class="badge badge-in_transit">in_transit</span></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Carrier</span>
                    <span class="meta-val" id="sideCarrier">Dedicated Fleet</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Temp Controlled</span>
                    <span class="meta-val" id="sideTemp">Standard (Ambient)</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Total Value</span>
                    <span class="meta-val mono" id="sideAmount" style="color:#4ade80;">$184,500.00</span>
                </div>
            </div>

            <!-- Quick Database Summary in Sidebar -->
            <div class="sidebar-card">
                <div style="font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:4px;">NEON DB TELEMATICS</div>
                <div class="meta-row">
                    <span class="meta-label">Total Shipments</span>
                    <span class="meta-val mono" id="sideTotalShipments">55</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Active Orders</span>
                    <span class="meta-val mono" id="sideTotalOrders">55</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Fleet Vehicles</span>
                    <span class="meta-val mono" id="sideTotalVehicles">52</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Active CDL Drivers</span>
                    <span class="meta-val mono" id="sideTotalDrivers">52</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Distribution Hubs</span>
                    <span class="meta-val mono" id="sideTotalWarehouses">52</span>
                </div>
            </div>
        </aside>

        <!-- 2.2 Right Main Content -->
        <main class="main-content">

            <!-- 1. Real-time Database KPI Metrics Grid -->
            <section class="metrics-grid">
                <div class="metric-card">
                    <span class="metric-title">Gross Revenue</span>
                    <div class="metric-value mono" id="kpiRevenue" style="color:var(--primary);">$0.00</div>
                    <span class="metric-subtitle" id="kpiOrdersCount">Live from Orders table</span>
                </div>
                <div class="metric-card">
                    <span class="metric-title">Active Freight Loads</span>
                    <div class="metric-value" id="kpiActiveShipments" style="color:var(--emerald);">0 Active</div>
                    <span class="metric-subtitle" id="kpiOnTime">On-Time Delivery Rate: 98.5%</span>
                </div>
                <div class="metric-card">
                    <span class="metric-title">Fleet Utilization</span>
                    <div class="metric-value" id="kpiFleetUtil" style="color:var(--cyan);">0.0%</div>
                    <span class="metric-subtitle" id="kpiVehiclesCount">Live from Vehicles table</span>
                </div>
                <div class="metric-card">
                    <span class="metric-title">Warehouse Capacity</span>
                    <div class="metric-value" id="kpiWarehouseUtil" style="color:#8b5cf6;">0.0%</div>
                    <span class="metric-subtitle" id="kpiHubsCount">Live from Warehouses table</span>
                </div>
                <div class="metric-card">
                    <span class="metric-title">Driver Safety Score</span>
                    <div class="metric-value" id="kpiDriverScore" style="color:var(--emerald);">98.8 / 100</div>
                    <span class="metric-subtitle" id="kpiDriversCount">Live from Drivers table</span>
                </div>
            </section>

            <!-- 2. Entity Tabs Bar -->
            <div class="entity-tabs-bar">
                <div class="tab-buttons" id="tabButtons">
                    <button class="entity-tab active" onclick="switchEntity('shipments')">Shipments</button>
                    <button class="entity-tab" onclick="switchEntity('orders')">Orders</button>
                    <button class="entity-tab" onclick="switchEntity('deliveries')">Deliveries</button>
                    <button class="entity-tab" onclick="switchEntity('vehicles')">Vehicles</button>
                    <button class="entity-tab" onclick="switchEntity('drivers')">Drivers</button>
                    <button class="entity-tab" onclick="switchEntity('warehouses')">Warehouses</button>
                    <button class="entity-tab" onclick="switchEntity('customers')">Customers</button>
                    <button class="entity-tab" onclick="switchEntity('routes')">Routes</button>
                    <button class="entity-tab" onclick="switchEntity('companies')">Companies</button>
                    <button class="entity-tab" onclick="switchEntity('users')">Users</button>
                </div>
                <button class="btn btn-outline" style="font-size:11.5px; padding:4px 10px;" onclick="loadEntityData(currentEntity)">
                    🔄 Refresh Data
                </button>
            </div>

            <!-- 3. Dynamic Database Records Table -->
            <div class="table-card">
                <div class="table-toolbar">
                    <input type="text" id="tableFilter" class="search-input" placeholder="Search records in real-time..." oninput="filterCurrentTable()">
                    <span id="recordsCount" class="badge-count mono">Loading records...</span>
                </div>

                <div class="table-wrap">
                    <table id="mainDataTable">
                        <thead id="mainTableHead">
                            <tr><th>Loading columns...</th></tr>
                        </thead>
                        <tbody id="mainTableBody">
                            <tr><td>Fetching database rows from Neon PostgreSQL...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Copilot Studio Tool Calling & Telematics Inspector -->
            <div class="ceo-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:13px; font-weight:700; color:var(--primary); display:flex; align-items:center; gap:6px;">
                        <span>⚡</span> Microsoft Copilot Studio & AI Tool Execution Console
                    </div>
                    <span class="mono" style="font-size:11px; color:var(--text-muted);">POST /api/agent/execute &bull; Backed by Neon DB</span>
                </div>

                <div id="ceoLogs" class="terminal-box">
                    <div style="color: #38bdf8;">[SYSTEM] Connected to Neon PostgreSQL Source of Truth (ep-dry-star-ayj98cwp.c-5.us-east-2.aws.neon.tech).</div>
                    <div style="color: #a7f3d0;">[COPILOT STUDIO READY] Tool dispatcher online. Microsoft Copilot Studio, LangChain, or GitHub Copilot can execute any tool directly against live telemetry.</div>
                </div>

                <div class="chat-input-row">
                    <select id="toolSelector" class="search-input" style="width:260px;" onchange="updateToolParams()">
                        <option value="get_executive_kpis">get_executive_kpis</option>
                        <option value="query_fleet_status">query_fleet_status</option>
                        <option value="track_shipment_or_delivery">track_shipment_or_delivery</option>
                        <option value="inspect_warehouse_capacity">inspect_warehouse_capacity</option>
                        <option value="flag_critical_exceptions">flag_critical_exceptions</option>
                        <option value="get_customer_financials">get_customer_financials</option>
                    </select>
                    <input type="text" id="toolParamsInput" class="chat-input" placeholder='Tool arguments JSON, e.g. {"query_code":"TRK-1000-9999-01"}' value='{}'>
                    <button class="btn btn-primary" onclick="runSelectedTool()">Execute Tool</button>
                </div>

                <div class="chips-row">
                    <button class="chip-btn" onclick="quickTool('get_executive_kpis', {})">📊 Executive KPIs</button>
                    <button class="chip-btn" onclick="quickTool('query_fleet_status', {status: 'in_transit'})">🚛 In-Transit Fleet</button>
                    <button class="chip-btn" onclick="quickTool('track_shipment_or_delivery', {query_code: 'TRK-1000-9999-01'})">📍 Track Consignment</button>
                    <button class="chip-btn" onclick="quickTool('inspect_warehouse_capacity', {threshold_pct: 80})">🏬 Warehouse Bottlenecks</button>
                    <button class="chip-btn" onclick="quickTool('flag_critical_exceptions', {})">⚠️ Critical Exceptions</button>
                    <button class="chip-btn" onclick="quickTool('get_customer_financials', {})">💰 Customer Receivables</button>
                </div>
            </div>

        </main>

    </div>

    <!-- Live Database Fetching JavaScript -->
    <script>
        let currentEntity = 'shipments';
        let cachedRows = [];

        document.addEventListener('DOMContentLoaded', () => {
            fetchLiveKpis();
            loadEntityData('shipments');
        });

        // 1. Fetch Real-time KPIs from Neon Database
        async function fetchLiveKpis() {
            try {
                const res = await fetch('/api/agent/ceo-kpis');
                const json = await res.json();
                if (json.success && json.kpis) {
                    const k = json.kpis;
                    document.getElementById('kpiRevenue').textContent = '$' + Number(k.revenue.total_gross_usd || 0).toLocaleString();
                    document.getElementById('kpiOrdersCount').textContent = `${k.revenue.total_orders || 0} Total Orders in DB`;
                    document.getElementById('kpiActiveShipments').textContent = `${k.freight_operations.active_shipments || 0} Active`;
                    document.getElementById('kpiOnTime').textContent = `On-Time Delivery Rate: ${k.freight_operations.on_time_delivery_pct || 98.5}%`;
                    document.getElementById('kpiFleetUtil').textContent = `${k.fleet_telematics.fleet_utilization_pct || 0}%`;
                    document.getElementById('kpiVehiclesCount').textContent = `${k.fleet_telematics.total_vehicles || 0} Total Fleet Vehicles`;
                    document.getElementById('kpiWarehouseUtil').textContent = `${k.network_infrastructure.avg_utilization_pct || 0}%`;
                    document.getElementById('kpiHubsCount').textContent = `${k.network_infrastructure.total_warehouses || 0} Distribution Hubs`;
                    document.getElementById('kpiDriverScore').textContent = `${k.fleet_telematics.avg_safety_score || 98.8} / 100`;
                    document.getElementById('kpiDriversCount').textContent = `${k.fleet_telematics.total_drivers || 0} CDL-A Drivers`;

                    document.getElementById('sideTotalShipments').textContent = k.freight_operations.active_shipments + k.freight_operations.delivered_shipments;
                    document.getElementById('sideTotalOrders').textContent = k.revenue.total_orders;
                    document.getElementById('sideTotalVehicles').textContent = k.fleet_telematics.total_vehicles;
                    document.getElementById('sideTotalDrivers').textContent = k.fleet_telematics.total_drivers;
                    document.getElementById('sideTotalWarehouses').textContent = k.network_infrastructure.total_warehouses;
                }
            } catch (err) {
                console.error('Failed to load KPIs:', err);
            }
        }

        // 2. Switch Entity Table
        function switchEntity(entityName) {
            currentEntity = entityName;
            document.querySelectorAll('.entity-tab').forEach(b => {
                b.classList.toggle('active', b.textContent.toLowerCase() === entityName.toLowerCase());
            });
            document.querySelectorAll('.nav-link-btn').forEach(b => {
                b.classList.toggle('active', b.textContent.toLowerCase() === entityName.toLowerCase());
            });
            loadEntityData(entityName);
        }

        // 3. Load Entity Data from Neon Database API
        async function loadEntityData(entityName) {
            const head = document.getElementById('mainTableHead');
            const body = document.getElementById('mainTableBody');
            const countLabel = document.getElementById('recordsCount');

            body.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:18px; color:var(--text-muted);">Fetching live records from /api/' + entityName + '...</td></tr>';

            try {
                const res = await fetch('/api/' + entityName + '?per_page=100');
                const json = await res.json();
                cachedRows = json.data || [];
                countLabel.textContent = `${cachedRows.length} database records loaded`;

                renderEntityTable(entityName, cachedRows);

                // Auto populate sidebar with first record if shipments
                if (entityName === 'shipments' && cachedRows.length > 0) {
                    populateSidebarRecord(cachedRows[0]);
                }
            } catch (err) {
                body.innerHTML = '<tr><td colspan="7" style="text-align:center; color:var(--rose); padding:18px;">Error connecting to Neon PostgreSQL.</td></tr>';
            }
        }

        // 4. Render Table by Entity Columns
        function renderEntityTable(entityName, rows) {
            const head = document.getElementById('mainTableHead');
            const body = document.getElementById('mainTableBody');

            if (!rows.length) {
                body.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:18px; color:var(--text-muted);">No records found in database table.</td></tr>';
                return;
            }

            let headers = [];
            let rowHtml = [];

            if (entityName === 'shipments') {
                headers = ['Shipment #', 'Tracking Number', 'Origin Hub', 'Destination Hub', 'Carrier', 'Temp Controlled', 'Status', 'Actions'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.shipment_number}</strong></td>
                        <td class="mono" style="color:var(--cyan); font-weight:600;">${r.tracking_number}</td>
                        <td>${r.origin_warehouse ? r.origin_warehouse.name : 'Origin Hub'}</td>
                        <td>${r.destination_warehouse ? r.destination_warehouse.name : 'Destination Hub'}</td>
                        <td>${r.carrier_name}</td>
                        <td>${r.temperature_controlled ? '<span class="badge badge-in_transit">❄️ ' + (r.target_temp_celsius || 4.0) + '°C</span>' : '<span style="color:var(--text-muted);">Ambient</span>'}</td>
                        <td><span class="badge badge-${r.status}">${r.status}</span></td>
                        <td><button class="btn btn-outline" style="padding:2px 8px; font-size:11px;" onclick='populateSidebarRecord(${JSON.stringify(r).replace(/'/g, "&apos;")})'>Inspect</button></td>
                    </tr>
                `);
            } else if (entityName === 'orders') {
                headers = ['Order #', 'Customer Account', 'Required Date', 'Items', 'Total Amount', 'Priority', 'Payment', 'Status'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.order_number}</strong></td>
                        <td>${r.customer ? r.customer.name : 'Commercial Account'}</td>
                        <td>${r.required_delivery_date || r.order_date}</td>
                        <td>${r.items_count} units</td>
                        <td class="mono" style="font-weight:600; color:#0f172a;">$${Number(r.total_amount).toLocaleString()}</td>
                        <td><span class="badge ${r.priority === 'critical' ? 'badge-critical' : 'badge-in_transit'}">${r.priority}</span></td>
                        <td><span class="badge badge-${r.payment_status}">${r.payment_status}</span></td>
                        <td><span class="badge badge-${r.status}">${r.status}</span></td>
                    </tr>
                `);
            } else if (entityName === 'deliveries') {
                headers = ['Delivery #', 'Recipient / Dock', 'Address / City', 'Driver', 'Vehicle', 'Status', 'Feedback'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.delivery_number}</strong></td>
                        <td>${r.recipient_name}</td>
                        <td>${r.delivery_address}</td>
                        <td>${r.driver ? r.driver.first_name + ' ' + r.driver.last_name : 'Assigned Driver'}</td>
                        <td>${r.vehicle ? r.vehicle.vehicle_code : 'Fleet Asset'}</td>
                        <td><span class="badge badge-${r.status}">${r.status}</span></td>
                        <td>${r.customer_feedback_rating ? '⭐ ' + r.customer_feedback_rating + '/5' : '<span style="color:var(--text-muted);">Pending POD</span>'}</td>
                    </tr>
                `);
            } else if (entityName === 'vehicles') {
                headers = ['Vehicle Code', 'Make & Model', 'Plate #', 'Type', 'Max Payload', 'Fuel Level', 'Status'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.vehicle_code}</strong></td>
                        <td>${r.make} ${r.model} (${r.year})</td>
                        <td class="mono">${r.plate_number}</td>
                        <td>${r.type}</td>
                        <td>${Number(r.max_weight_kg).toLocaleString()} kg</td>
                        <td><strong style="color: ${r.fuel_level_pct < 25 ? 'var(--rose)' : 'var(--emerald)'};">${r.fuel_level_pct}%</strong></td>
                        <td><span class="badge badge-${r.status}">${r.status}</span></td>
                    </tr>
                `);
            } else if (entityName === 'drivers') {
                headers = ['Driver Code', 'Name', 'License Type', 'Phone', 'Safety Score', 'Total Trips', 'Status'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.driver_code}</strong></td>
                        <td>${r.first_name} ${r.last_name}</td>
                        <td>${r.license_type}</td>
                        <td class="mono">${r.phone}</td>
                        <td><strong style="color:var(--emerald);">${r.safety_score} / 100</strong></td>
                        <td>${r.total_trips} trips</td>
                        <td><span class="badge badge-${r.status}">${r.status}</span></td>
                    </tr>
                `);
            } else if (entityName === 'warehouses') {
                headers = ['Code', 'Name', 'City & State', 'Capacity', 'Utilization', 'Type', 'Manager'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.code}</strong></td>
                        <td>${r.name}</td>
                        <td>${r.city}, ${r.state || ''}</td>
                        <td>${Number(r.capacity_sqft).toLocaleString()} sqft</td>
                        <td><strong style="color: ${r.current_utilization_pct > 80 ? 'var(--rose)' : 'var(--emerald)'};">${r.current_utilization_pct}%</strong></td>
                        <td><span class="badge badge-in_transit">${r.type}</span></td>
                        <td>${r.manager_name || 'Operations Lead'}</td>
                    </tr>
                `);
            } else if (entityName === 'customers') {
                headers = ['Customer Code', 'Commercial Enterprise', 'Contact Email', 'Phone', 'Credit Facility', 'Outstanding Balance', 'Tier'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.customer_code}</strong></td>
                        <td>${r.name}</td>
                        <td class="mono">${r.email}</td>
                        <td class="mono">${r.phone}</td>
                        <td class="mono">$${Number(r.credit_limit).toLocaleString()}</td>
                        <td class="mono" style="color:var(--amber); font-weight:600;">$${Number(r.outstanding_balance).toLocaleString()}</td>
                        <td><span class="badge badge-in_transit">${r.tier}</span></td>
                    </tr>
                `);
            } else if (entityName === 'routes') {
                headers = ['Route Code', 'Corridor Name', 'Origin Hub', 'Destination Hub', 'Distance', 'Est Duration', 'Risk Level'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.route_code}</strong></td>
                        <td>${r.name}</td>
                        <td>${r.origin_name}</td>
                        <td>${r.destination_name}</td>
                        <td class="mono">${Number(r.distance_km).toLocaleString()} km</td>
                        <td>${Math.round(r.estimated_duration_minutes / 60)} hrs</td>
                        <td><span class="badge ${r.risk_level === 'high' ? 'badge-critical' : 'badge-in_transit'}">${r.risk_level}</span></td>
                    </tr>
                `);
            } else if (entityName === 'companies') {
                headers = ['Company Code', 'Name', 'Headquarters Address', 'Fleet Size', 'CEO', 'Status'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td><strong>${r.code}</strong></td>
                        <td>${r.name}</td>
                        <td>${r.headquarters_address || 'USA'}</td>
                        <td>${r.fleet_size} vehicles</td>
                        <td>${r.ceo_name || 'Leadership'}</td>
                        <td><span class="badge badge-completed">${r.status}</span></td>
                    </tr>
                `);
            } else if (entityName === 'users') {
                headers = ['User ID', 'Name', 'Email Address', 'Role', 'Phone', 'Status'];
                rowHtml = rows.map(r => `
                    <tr>
                        <td class="mono">#${r.id}</td>
                        <td><strong>${r.name}</strong></td>
                        <td class="mono">${r.email}</td>
                        <td><span class="badge badge-in_transit">${r.role}</span></td>
                        <td class="mono">${r.phone || 'N/A'}</td>
                        <td><span class="badge badge-completed">${r.status}</span></td>
                    </tr>
                `);
            }

            head.innerHTML = '<tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
            body.innerHTML = rowHtml.join('');
        }

        // 5. Filter Current Table on Input
        function filterCurrentTable() {
            const query = document.getElementById('tableFilter').value.toLowerCase();
            const filtered = cachedRows.filter(r => JSON.stringify(r).toLowerCase().includes(query));
            renderEntityTable(currentEntity, filtered);
        }

        // 6. Populate Left Sidebar with Real Database Record
        function populateSidebarRecord(rec) {
            if (!rec) return;
            document.getElementById('jobSearchCode').value = rec.tracking_number || rec.shipment_number || rec.order_number || '';
            document.getElementById('sideShpNumber').textContent = rec.shipment_number || 'N/A';
            document.getElementById('sideTracking').textContent = rec.tracking_number || 'N/A';
            document.getElementById('sideOrder').textContent = rec.order ? rec.order.order_number : (rec.order_number || 'N/A');
            document.getElementById('sideCustomer').textContent = rec.order && rec.order.customer ? rec.order.customer.name : (rec.customer ? rec.customer.name : 'Enterprise Client');
            document.getElementById('sideStatus').innerHTML = `<span class="badge badge-${rec.status}">${rec.status}</span>`;
            document.getElementById('sideCarrier').textContent = rec.carrier_name || 'Dedicated Fleet';
            document.getElementById('sideTemp').textContent = rec.temperature_controlled ? `❄️ Controlled (${rec.target_temp_celsius || 4.0}°C)` : 'Standard (Ambient)';
            
            const amt = rec.order ? rec.order.total_amount : (rec.total_amount || 0);
            document.getElementById('sideAmount').textContent = '$' + Number(amt).toLocaleString();
        }

        // 7. Lookup by Code in Search Input
        async function inspectJobCode() {
            const code = document.getElementById('jobSearchCode').value.trim();
            if (!code) return;

            try {
                const res = await fetch(`/api/agent/track?query_code=${encodeURIComponent(code)}`);
                const json = await res.json();
                if (json.success && json.data) {
                    populateSidebarRecord(json.data);
                    alert(`Record loaded from Neon DB for ${code}`);
                } else {
                    alert(`No active consignment found for code: ${code}`);
                }
            } catch (err) {
                alert('Failed to lookup record.');
            }
        }

        // 8. Execute Tool via /api/agent/execute (Copilot Studio Dispatcher)
        function updateToolParams() {
            const tool = document.getElementById('toolSelector').value;
            const input = document.getElementById('toolParamsInput');
            if (tool === 'track_shipment_or_delivery') {
                input.value = '{"query_code": "TRK-1000-9999-01"}';
            } else if (tool === 'query_fleet_status') {
                input.value = '{"status": "in_transit"}';
            } else if (tool === 'inspect_warehouse_capacity') {
                input.value = '{"threshold_pct": 80}';
            } else {
                input.value = '{}';
            }
        }

        async function runSelectedTool() {
            const tool = document.getElementById('toolSelector').value;
            const paramText = document.getElementById('toolParamsInput').value.trim();
            let params = {};
            try {
                params = paramText ? JSON.parse(paramText) : {};
            } catch (e) {
                alert('Invalid JSON in tool parameters input');
                return;
            }
            await executeAgentTool(tool, params);
        }

        function quickTool(toolName, params) {
            document.getElementById('toolSelector').value = toolName;
            document.getElementById('toolParamsInput').value = JSON.stringify(params);
            executeAgentTool(toolName, params);
        }

        async function executeAgentTool(toolName, params) {
            const logs = document.getElementById('ceoLogs');

            const userMsg = document.createElement('div');
            userMsg.style.color = '#38bdf8';
            userMsg.textContent = `> COPILOT EXECUTE: ${toolName}(${JSON.stringify(params)})`;
            logs.appendChild(userMsg);

            const waitMsg = document.createElement('div');
            waitMsg.style.color = '#f59e0b';
            waitMsg.textContent = `[AGENT DISPATCHER] Executing tool against Neon PostgreSQL...`;
            logs.appendChild(waitMsg);
            logs.scrollTop = logs.scrollHeight;

            try {
                const res = await fetch('/api/agent/execute', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        tool_name: toolName,
                        parameters: params
                    })
                });
                const json = await res.json();
                waitMsg.remove();

                const agentMsg = document.createElement('div');
                agentMsg.style.color = '#a7f3d0';
                agentMsg.innerHTML = `<strong>[TOOL RESULT: 200 OK]</strong><pre style="margin-top:4px; color:#f1f5f9; background:#1e293b; padding:6px; border-radius:4px; overflow-x:auto;">${JSON.stringify(json, null, 2)}</pre>`;
                logs.appendChild(agentMsg);
            } catch (err) {
                waitMsg.style.color = '#ef4444';
                waitMsg.textContent = `[ERROR] Failed to execute tool: ${err.message}`;
            }
            logs.scrollTop = logs.scrollHeight;
        }
    </script>
</body>
</html>

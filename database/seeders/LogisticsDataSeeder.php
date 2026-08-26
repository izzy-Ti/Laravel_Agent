<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LogisticsDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Truncate tables for a clean 50+ record seed
        DB::statement('TRUNCATE TABLE deliveries, shipments, orders, routes, vehicles, drivers, warehouses, customers, users, companies RESTART IDENTITY CASCADE;');

        DB::transaction(function () use ($now) {
            // 1. Companies (5 Major Logistics Enterprises)
            $companies = [
                [
                    'name' => 'Titan Apex Global Logistics',
                    'code' => 'TITAN-APEX',
                    'email' => 'operations@titanapex.com',
                    'phone' => '+1 (800) 555-8482',
                    'headquarters_address' => '333 S Wabash Ave, Suite 4200, Chicago, IL 60604',
                    'country' => 'USA',
                    'currency' => 'USD',
                    'timezone' => 'America/Chicago',
                    'fleet_size' => 450,
                    'ceo_name' => 'Alexander Vance',
                    'status' => 'active',
                    'metadata' => json_encode(['scac_code' => 'TAPX', 'rating' => 'DOT Platinum Tier 1']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Vanguard Intermodal Freight',
                    'code' => 'VANGUARD-EXP',
                    'email' => 'dispatch@vanguardexpress.net',
                    'phone' => '+1 (888) 555-0199',
                    'headquarters_address' => '1000 Wilshire Blvd, Los Angeles, CA 90017',
                    'country' => 'USA',
                    'currency' => 'USD',
                    'timezone' => 'America/Los_Angeles',
                    'fleet_size' => 280,
                    'ceo_name' => 'Elena Rostova',
                    'status' => 'active',
                    'metadata' => json_encode(['scac_code' => 'VANG', 'rating' => 'SmartWay Partner']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'NorthStar Cold-Chain Express',
                    'code' => 'NORTHSTAR-COLD',
                    'email' => 'coldchain@northstarlogistics.com',
                    'phone' => '+1 (800) 555-3321',
                    'headquarters_address' => '500 Technology Sq, Cambridge, MA 02139',
                    'country' => 'USA',
                    'currency' => 'USD',
                    'timezone' => 'America/New_York',
                    'fleet_size' => 190,
                    'ceo_name' => 'Dr. Henrik Lindqvist',
                    'status' => 'active',
                    'metadata' => json_encode(['specialty' => 'Pharma Biologics']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'LoneStar Heavy Haul & Transload',
                    'code' => 'LONESTAR-HAUL',
                    'email' => 'dispatch@lonestarhaul.com',
                    'phone' => '+1 (800) 555-7890',
                    'headquarters_address' => '2400 E Airfield Dr, DFW Airport, TX 75261',
                    'country' => 'USA',
                    'currency' => 'USD',
                    'timezone' => 'America/Chicago',
                    'fleet_size' => 310,
                    'ceo_name' => 'J.R. Callahan',
                    'status' => 'active',
                    'metadata' => json_encode(['specialty' => 'Heavy Machinery']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Pacific Gateway Logistics',
                    'code' => 'PACIFIC-GATE',
                    'email' => 'ops@pacificgatewaylog.com',
                    'phone' => '+1 (888) 555-4400',
                    'headquarters_address' => '2001 E Pacific Coast Hwy, Long Beach, CA 90810',
                    'country' => 'USA',
                    'currency' => 'USD',
                    'timezone' => 'America/Los_Angeles',
                    'fleet_size' => 220,
                    'ceo_name' => 'David Kim',
                    'status' => 'active',
                    'metadata' => json_encode(['specialty' => 'Maritime Drayage']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];
            DB::table('companies')->insert($companies);
            $compIds = DB::table('companies')->pluck('id')->toArray();

            // 2. Users (55 Personnel)
            $firstNames = ['James', 'Robert', 'John', 'Michael', 'David', 'William', 'Richard', 'Joseph', 'Thomas', 'Charles', 'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Mark', 'Donald', 'Steven', 'Andrew', 'Paul', 'Joshua', 'Kenneth', 'Kevin', 'Brian', 'Timothy', 'Ronald', 'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen', 'Lisa', 'Nancy', 'Betty', 'Sandra', 'Margaret', 'Ashley', 'Kimberly', 'Emily', 'Donna', 'Michelle', 'Carol', 'Amanda', 'Melissa', 'Deborah', 'Stephanie', 'Rebecca', 'Sharon', 'Laura', 'Cynthia', 'Kathleen'];
            $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz'];
            $roles = ['operations_manager', 'dispatcher', 'warehouse_manager', 'driver', 'analyst', 'customer_admin'];
            $hashedPassword = Hash::make('Logistics2026!');

            $users = [
                [
                    'company_id' => $compIds[0],
                    'name' => 'Alexander Vance (CEO)',
                    'email' => 'alexander.vance@titanapex.com',
                    'role' => 'ceo',
                    'phone' => '+1 (312) 555-0100',
                    'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150',
                    'status' => 'active',
                    'password' => $hashedPassword,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ];

            for ($i = 0; $i < 54; $i++) {
                $fn = $firstNames[$i % count($firstNames)];
                $ln = $lastNames[$i % count($lastNames)];
                $cid = $compIds[$i % count($compIds)];
                $role = $roles[$i % count($roles)];
                $email = strtolower("{$fn}.{$ln}.{$i}@logistics.corp");

                $users[] = [
                    'company_id' => $cid,
                    'name' => "{$fn} {$ln}",
                    'email' => $email,
                    'role' => $role,
                    'phone' => '+1 (' . rand(200, 999) . ') 555-' . str_pad($i + 100, 4, '0', STR_PAD_LEFT),
                    'avatar_url' => null,
                    'status' => 'active',
                    'password' => $hashedPassword,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('users')->insert($users);
            $userIds = DB::table('users')->pluck('id')->toArray();

            // 3. Customers (52 Accounts)
            $customerNames = [
                'Tesla Gigafactory Supply Chain', 'Apple Global Logistics', 'Amazon Freight Systems', 'Target Supply Chain',
                'Walmart Freight Network', 'BioNova Biopharma Labs', 'Pfizer Global Cold Chain', 'General Motors Inbound',
                'Ford Motor Rouge Logistics', 'Boeing Aerospace Cargo', 'Intel Semiconductor Freight', 'Nike Distribution Ops',
                'Home Depot Regional Freight', 'Costco Wholesale Logistics', 'FedEx Freight Partner', 'DHL Global Forwarding',
                'Samsung Electronics America', 'Caterpillar Heavy Parts', 'John Deere Logistics', 'Procter & Gamble Freight',
                'Unilever North America', 'General Mills Foods', 'Kraft Heinz Distribution', 'Tyson Foods Cold Line',
                'Sysco Foodservice Logistics', 'Whole Foods Market Produce', 'Kroger Intermodal Cargo', 'PepsiCo Beverages Supply',
                'Coca-Cola Freight Division', 'Anheuser-Busch Logistics', 'BASF Chemical Transport', 'Dow Chemical Freight',
                'ExxonMobil Industrial Supply', 'Chevron Energy Cargo', 'Halliburton Oilfield Freight', 'Raytheon Defense Logistics',
                'Lockheed Martin Supply Net', 'Honeywell Industrial Logistics', 'Siemens Energy US', '3M Innovative Logistics',
                'Johnson & Johnson Medical Supply', 'Medtronic Devices Line', 'Abbott Diagnostics Cargo', 'Eli Lilly Biopharma',
                'Moderna mRNA Distribution', 'McKesson Health Mart', 'Cardinal Health Logistics', 'AmerisourceBergen Corp',
                'CVS Health Distribution', 'Walgreens Boots Supply', 'Wayfair Furniture Freight', 'IKEA North America Cargo'
            ];

            $customers = [];
            $tiers = ['enterprise', 'strategic', 'priority', 'standard'];
            foreach ($customerNames as $idx => $cname) {
                $cid = $compIds[$idx % count($compIds)];
                $tier = $tiers[$idx % count($tiers)];
                $customers[] = [
                    'company_id' => $cid,
                    'customer_code' => 'CUST-' . str_pad($idx + 101, 4, '0', STR_PAD_LEFT),
                    'name' => $cname,
                    'company_name' => $cname . ' Inc.',
                    'email' => 'logistics@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($cname, 0, 10))) . ($idx + 10) . '.corp',
                    'phone' => '+1 (' . rand(200, 999) . ') 555-' . str_pad($idx + 1000, 4, '0', STR_PAD_LEFT),
                    'tax_id' => 'US-' . str_pad($idx + 10, 2, '0', STR_PAD_LEFT) . '-' . str_pad($idx + 1000000, 7, '0', STR_PAD_LEFT),
                    'billing_address' => ($idx * 100 + 100) . ' Corporate Blvd, Suite ' . ($idx + 100),
                    'shipping_address' => ($idx * 100 + 100) . ' Logistics Parkway, Receiving Dock #' . (($idx % 16) + 1),
                    'tier' => $tier,
                    'credit_limit' => (($idx % 10) + 5) * 100000,
                    'outstanding_balance' => (($idx % 5) + 1) * 50000,
                    'status' => 'active',
                    'notes' => 'Contract SLA tier: ' . strtoupper($tier) . ' freight account.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('customers')->insert($customers);
            $customerIds = DB::table('customers')->pluck('id')->toArray();

            // 4. Warehouses (52 Hubs)
            $hubLocations = [
                ['code' => 'WH-ORD-01', 'name' => 'Chicago O\'Hare Midwest Super-Hub', 'city' => 'Chicago', 'state' => 'IL', 'lat' => 41.9803, 'lng' => -87.8860, 'type' => 'cross_dock'],
                ['code' => 'WH-DFW-02', 'name' => 'Dallas DFW Gateway Terminal', 'city' => 'Dallas', 'state' => 'TX', 'lat' => 32.8998, 'lng' => -97.0403, 'type' => 'fulfillment'],
                ['code' => 'WH-LAX-03', 'name' => 'Long Beach Pacific Intermodal Hub', 'city' => 'Long Beach', 'state' => 'CA', 'lat' => 33.7915, 'lng' => -118.2104, 'type' => 'bonded'],
                ['code' => 'WH-ATL-04', 'name' => 'Atlanta Southeast Cold Storage', 'city' => 'Atlanta', 'state' => 'GA', 'lat' => 33.6407, 'lng' => -84.4277, 'type' => 'cold_storage'],
                ['code' => 'WH-EWR-05', 'name' => 'Newark Northeast Air Cargo Hub', 'city' => 'Newark', 'state' => 'NJ', 'lat' => 40.6895, 'lng' => -74.1745, 'type' => 'distribution'],
                ['code' => 'WH-MIA-06', 'name' => 'Miami Latin America Gateway', 'city' => 'Miami', 'state' => 'FL', 'lat' => 25.7959, 'lng' => -80.2870, 'type' => 'bonded'],
                ['code' => 'WH-SEA-07', 'name' => 'Seattle Pacific Northwest Terminal', 'city' => 'Seattle', 'state' => 'WA', 'lat' => 47.4502, 'lng' => -122.3088, 'type' => 'cross_dock'],
                ['code' => 'WH-DEN-08', 'name' => 'Denver Rocky Mountain Hub', 'city' => 'Denver', 'state' => 'CO', 'lat' => 39.8561, 'lng' => -104.6737, 'type' => 'fulfillment'],
                ['code' => 'WH-MEM-09', 'name' => 'Memphis Mid-South Freight Super-Hub', 'city' => 'Memphis', 'state' => 'TN', 'lat' => 35.0421, 'lng' => -89.9792, 'type' => 'cross_dock'],
                ['code' => 'WH-IND-10', 'name' => 'Indianapolis Crossroads Facility', 'city' => 'Indianapolis', 'state' => 'IN', 'lat' => 39.7173, 'lng' => -86.2944, 'type' => 'distribution'],
                ['code' => 'WH-PHX-11', 'name' => 'Phoenix Desert Southwest Hub', 'city' => 'Phoenix', 'state' => 'AZ', 'lat' => 33.4373, 'lng' => -112.0078, 'type' => 'fulfillment'],
                ['code' => 'WH-HOU-12', 'name' => 'Houston Gulf Coast Transload', 'city' => 'Houston', 'state' => 'TX', 'lat' => 29.9902, 'lng' => -95.3368, 'type' => 'bonded'],
                ['code' => 'WH-DET-13', 'name' => 'Detroit Automotive Gateway', 'city' => 'Detroit', 'state' => 'MI', 'lat' => 42.2162, 'lng' => -83.3554, 'type' => 'cross_dock'],
                ['code' => 'WH-MSP-14', 'name' => 'Minneapolis North Central Hub', 'city' => 'Minneapolis', 'state' => 'MN', 'lat' => 44.8848, 'lng' => -93.2223, 'type' => 'cold_storage'],
                ['code' => 'WH-CLT-15', 'name' => 'Charlotte Carolinas Hub', 'city' => 'Charlotte', 'state' => 'NC', 'lat' => 35.2144, 'lng' => -80.9473, 'type' => 'distribution'],
                ['code' => 'WH-KCY-16', 'name' => 'Kansas City Central Transload', 'city' => 'Kansas City', 'state' => 'MO', 'lat' => 39.2976, 'lng' => -94.7139, 'type' => 'cross_dock'],
                ['code' => 'WH-SLC-17', 'name' => 'Salt Lake City Intermountain Hub', 'city' => 'Salt Lake City', 'state' => 'UT', 'lat' => 40.7899, 'lng' => -111.9791, 'type' => 'fulfillment'],
                ['code' => 'WH-BOS-18', 'name' => 'Boston New England Pharma Hub', 'city' => 'Boston', 'state' => 'MA', 'lat' => 42.3656, 'lng' => -71.0096, 'type' => 'cold_storage'],
                ['code' => 'WH-PHL-19', 'name' => 'Philadelphia Tri-State Depot', 'city' => 'Philadelphia', 'state' => 'PA', 'lat' => 39.8744, 'lng' => -75.2424, 'type' => 'distribution'],
                ['code' => 'WH-PIT-20', 'name' => 'Pittsburgh Keystone Terminal', 'city' => 'Pittsburgh', 'state' => 'PA', 'lat' => 40.4915, 'lng' => -80.2329, 'type' => 'cross_dock'],
                ['code' => 'WH-CVG-21', 'name' => 'Cincinnati Ohio Valley Hub', 'city' => 'Cincinnati', 'state' => 'OH', 'lat' => 39.0461, 'lng' => -84.6621, 'type' => 'distribution'],
                ['code' => 'WH-BNA-22', 'name' => 'Nashville Music City Freight Hub', 'city' => 'Nashville', 'state' => 'TN', 'lat' => 36.1263, 'lng' => -86.6774, 'type' => 'fulfillment'],
                ['code' => 'WH-STL-23', 'name' => 'St. Louis Gateway Arch Terminal', 'city' => 'St. Louis', 'state' => 'MO', 'lat' => 38.7499, 'lng' => -90.3748, 'type' => 'cross_dock'],
                ['code' => 'WH-TPA-24', 'name' => 'Tampa Bay Sunshine Depot', 'city' => 'Tampa', 'state' => 'FL', 'lat' => 27.9772, 'lng' => -82.5311, 'type' => 'distribution'],
                ['code' => 'WH-MKE-25', 'name' => 'Milwaukee Great Lakes Terminal', 'city' => 'Milwaukee', 'state' => 'WI', 'lat' => 42.9475, 'lng' => -87.8966, 'type' => 'cross_dock'],
                ['code' => 'WH-OAK-26', 'name' => 'Oakland Bay Area Maritime Hub', 'city' => 'Oakland', 'state' => 'CA', 'lat' => 37.7213, 'lng' => -122.2207, 'type' => 'bonded'],
                ['code' => 'WH-PDX-27', 'name' => 'Portland Columbia River Hub', 'city' => 'Portland', 'state' => 'OR', 'lat' => 45.5898, 'lng' => -122.5951, 'type' => 'distribution'],
                ['code' => 'WH-SAN-28', 'name' => 'San Diego Border Logistics Gate', 'city' => 'San Diego', 'state' => 'CA', 'lat' => 32.7338, 'lng' => -117.1933, 'type' => 'cross_dock'],
                ['code' => 'WH-SAT-29', 'name' => 'San Antonio Alamo Freight Hub', 'city' => 'San Antonio', 'state' => 'TX', 'lat' => 29.5337, 'lng' => -98.4698, 'type' => 'fulfillment'],
                ['code' => 'WH-AUS-30', 'name' => 'Austin Silicon Hills Freight Hub', 'city' => 'Austin', 'state' => 'TX', 'lat' => 30.1975, 'lng' => -97.6664, 'type' => 'distribution'],
                ['code' => 'WH-JAX-31', 'name' => 'Jacksonville Port Logistics Hub', 'city' => 'Jacksonville', 'state' => 'FL', 'lat' => 30.4941, 'lng' => -81.6879, 'type' => 'bonded'],
                ['code' => 'WH-COL-32', 'name' => 'Columbus Heartland Super-Hub', 'city' => 'Columbus', 'state' => 'OH', 'lat' => 39.9980, 'lng' => -82.8919, 'type' => 'cross_dock'],
                ['code' => 'WH-RDU-33', 'name' => 'Raleigh-Durham Triangle Hub', 'city' => 'Raleigh', 'state' => 'NC', 'lat' => 35.8801, 'lng' => -78.7880, 'type' => 'cold_storage'],
                ['code' => 'WH-OKC-34', 'name' => 'Oklahoma City Prairie Gateway', 'city' => 'Oklahoma City', 'state' => 'OK', 'lat' => 35.3931, 'lng' => -97.6007, 'type' => 'distribution'],
                ['code' => 'WH-MSY-35', 'name' => 'New Orleans Delta Transload', 'city' => 'New Orleans', 'state' => 'LA', 'lat' => 29.9934, 'lng' => -90.2580, 'type' => 'bonded'],
                ['code' => 'WH-LAS-36', 'name' => 'Las Vegas Nevada Crossroads', 'city' => 'Las Vegas', 'state' => 'NV', 'lat' => 36.0840, 'lng' => -115.1537, 'type' => 'fulfillment'],
                ['code' => 'WH-CLE-37', 'name' => 'Cleveland Lakefront Terminal', 'city' => 'Cleveland', 'state' => 'OH', 'lat' => 41.4107, 'lng' => -81.8494, 'type' => 'cross_dock'],
                ['code' => 'WH-ALB-38', 'name' => 'Albany Empire State Depot', 'city' => 'Albany', 'state' => 'NY', 'lat' => 42.7481, 'lng' => -73.8017, 'type' => 'distribution'],
                ['code' => 'WH-RIC-39', 'name' => 'Richmond Virginia Corridor Hub', 'city' => 'Richmond', 'state' => 'VA', 'lat' => 37.5052, 'lng' => -77.3197, 'type' => 'distribution'],
                ['code' => 'WH-BDL-40', 'name' => 'Hartford New England Depot', 'city' => 'Hartford', 'state' => 'CT', 'lat' => 41.9389, 'lng' => -72.6832, 'type' => 'fulfillment'],
                ['code' => 'WH-LOU-41', 'name' => 'Louisville WorldPort Hub', 'city' => 'Louisville', 'state' => 'KY', 'lat' => 38.1744, 'lng' => -85.7360, 'type' => 'cross_dock'],
                ['code' => 'WH-OMA-42', 'name' => 'Omaha Midwest Grain Hub', 'city' => 'Omaha', 'state' => 'NE', 'lat' => 41.3025, 'lng' => -95.8941, 'type' => 'cold_storage'],
                ['code' => 'WH-ABQ-43', 'name' => 'Albuquerque High Desert Depot', 'city' => 'Albuquerque', 'state' => 'NM', 'lat' => 35.0402, 'lng' => -106.6092, 'type' => 'distribution'],
                ['code' => 'WH-BOI-44', 'name' => 'Boise Northwest Hub', 'city' => 'Boise', 'state' => 'ID', 'lat' => 43.5644, 'lng' => -116.2228, 'type' => 'fulfillment'],
                ['code' => 'WH-LIT-45', 'name' => 'Little Rock River Port Hub', 'city' => 'Little Rock', 'state' => 'AR', 'lat' => 34.7294, 'lng' => -92.2243, 'type' => 'cross_dock'],
                ['code' => 'WH-DSM-46', 'name' => 'Des Moines Corn Belt Hub', 'city' => 'Des Moines', 'state' => 'IA', 'lat' => 41.5340, 'lng' => -93.6631, 'type' => 'distribution'],
                ['code' => 'WH-GSP-47', 'name' => 'Greenville Upstate Auto Hub', 'city' => 'Greenville', 'state' => 'SC', 'lat' => 34.8957, 'lng' => -82.2189, 'type' => 'cross_dock'],
                ['code' => 'WH-TUL-48', 'name' => 'Tulsa Green Country Terminal', 'city' => 'Tulsa', 'state' => 'OK', 'lat' => 36.1984, 'lng' => -95.8881, 'type' => 'distribution'],
                ['code' => 'WH-SAV-49', 'name' => 'Savannah Deepwater Port Gate', 'city' => 'Savannah', 'state' => 'GA', 'lat' => 32.1276, 'lng' => -81.2021, 'type' => 'bonded'],
                ['code' => 'WH-BUF-50', 'name' => 'Buffalo Niagara Frontier Hub', 'city' => 'Buffalo', 'state' => 'NY', 'lat' => 42.9405, 'lng' => -78.7322, 'type' => 'cross_dock'],
                ['code' => 'WH-ELP-51', 'name' => 'El Paso Border Transload Hub', 'city' => 'El Paso', 'state' => 'TX', 'lat' => 31.8072, 'lng' => -106.3778, 'type' => 'bonded'],
                ['code' => 'WH-ANC-52', 'name' => 'Anchorage Pacific Air Hub', 'city' => 'Anchorage', 'state' => 'AK', 'lat' => 61.1744, 'lng' => -149.9964, 'type' => 'cold_storage'],
            ];

            $warehouses = [];
            foreach ($hubLocations as $idx => $hub) {
                $cid = $compIds[$idx % count($compIds)];
                $warehouses[] = [
                    'company_id' => $cid,
                    'code' => $hub['code'],
                    'name' => $hub['name'],
                    'address' => rand(1000, 9999) . ' Logistics Airport Way',
                    'city' => $hub['city'],
                    'state' => $hub['state'],
                    'country' => 'USA',
                    'zip_code' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'latitude' => $hub['lat'],
                    'longitude' => $hub['lng'],
                    'capacity_sqft' => (($idx % 6) + 3) * 100000,
                    'current_utilization_pct' => 60 + ($idx % 35),
                    'type' => $hub['type'],
                    'operating_hours' => '24/7/365',
                    'manager_name' => $firstNames[$idx % count($firstNames)] . ' ' . $lastNames[($idx + 3) % count($lastNames)],
                    'manager_phone' => '+1 (' . rand(200, 999) . ') 555-' . str_pad($idx + 1000, 4, '0', STR_PAD_LEFT),
                    'manager_email' => 'manager.' . strtolower($hub['code']) . '@logistics.corp',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('warehouses')->insert($warehouses);
            $whRows = DB::table('warehouses')->get();

            // 5. Drivers (52 CDL-A Drivers)
            $drivers = [];
            $driverStatuses = ['available', 'on_trip', 'available', 'on_trip', 'off_duty'];
            for ($i = 0; $i < 52; $i++) {
                $cid = $compIds[$i % count($compIds)];
                $fn = $firstNames[$i % count($firstNames)];
                $ln = $lastNames[($i + 5) % count($lastNames)];
                $wh = $whRows[$i % count($whRows)];
                $st = $driverStatuses[$i % count($driverStatuses)];

                $drivers[] = [
                    'company_id' => $cid,
                    'user_id' => isset($userIds[$i]) ? $userIds[$i] : null,
                    'driver_code' => 'DRV-' . strtoupper(substr($wh->city, 0, 3)) . '-' . (100 + $i),
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'license_number' => $wh->state . '-CDL-' . str_pad($i + 100000, 6, '0', STR_PAD_LEFT) . 'X',
                    'license_type' => 'Class A CDL (Hazmat & Doubles)',
                    'license_expiry' => Carbon::now()->addMonths(($i % 36) + 12)->toDateString(),
                    'phone' => '+1 (' . rand(200, 999) . ') 555-' . str_pad($i + 2000, 4, '0', STR_PAD_LEFT),
                    'emergency_contact' => $firstNames[($i + 1) % count($firstNames)] . ' ' . $ln,
                    'current_latitude' => $wh->latitude + (($i % 10 - 5) / 100),
                    'current_longitude' => $wh->longitude + (($i % 10 - 5) / 100),
                    'status' => $st,
                    'safety_score' => 96.0 + (($i % 38) / 10),
                    'rating' => 4.80 + (($i % 20) / 100),
                    'total_trips' => 200 + ($i * 15),
                    'total_distance_km' => 50000 + ($i * 8000),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('drivers')->insert($drivers);
            $driverRows = DB::table('drivers')->get();

            // 6. Vehicles (52 Fleet Assets)
            $truckMakes = [
                ['make' => 'Freightliner', 'model' => 'Cascadia Evolution 126', 'type' => 'semi_truck', 'weight' => 24000, 'vol' => 95, 'fuel' => 'diesel'],
                ['make' => 'Volvo', 'model' => 'VNL 860 Globetrotter', 'type' => 'semi_truck', 'weight' => 24000, 'vol' => 92, 'fuel' => 'diesel'],
                ['make' => 'Peterbilt', 'model' => '579 Ultraloft Reefer', 'type' => 'refrigerated', 'weight' => 22000, 'vol' => 88, 'fuel' => 'diesel'],
                ['make' => 'Kenworth', 'model' => 'T680 Aerodynamic', 'type' => 'semi_truck', 'weight' => 25000, 'vol' => 96, 'fuel' => 'diesel'],
                ['make' => 'Mack', 'model' => 'Anthem 70-inch', 'type' => 'flatbed', 'weight' => 26000, 'vol' => 85, 'fuel' => 'diesel'],
                ['make' => 'Rivian', 'model' => 'Commercial Van 700 EV', 'type' => 'electric_van', 'weight' => 4500, 'vol' => 22, 'fuel' => 'electric'],
                ['make' => 'International', 'model' => 'LT Series Super-Haul', 'type' => 'semi_truck', 'weight' => 23000, 'vol' => 90, 'fuel' => 'diesel'],
                ['make' => 'Mercedes-Benz', 'model' => 'eActros LongHaul', 'type' => 'electric_van', 'weight' => 12000, 'vol' => 50, 'fuel' => 'electric'],
            ];

            $vehicles = [];
            $vehicleStatuses = ['in_transit', 'active', 'in_transit', 'idle', 'maintenance'];
            for ($i = 0; $i < 52; $i++) {
                $cid = $compIds[$i % count($compIds)];
                $drvr = $driverRows[$i % count($driverRows)];
                $tm = $truckMakes[$i % count($truckMakes)];
                $vStatus = $vehicleStatuses[$i % count($vehicleStatuses)];
                $vin = '1FUJ' . strtoupper(substr(md5('vin_' . $i), 0, 13));

                $vehicles[] = [
                    'company_id' => $cid,
                    'current_driver_id' => ($vStatus === 'in_transit' || $vStatus === 'active') ? $drvr->id : null,
                    'vehicle_code' => 'FLT-' . strtoupper(substr($tm['make'], 0, 4)) . '-' . (100 + $i),
                    'plate_number' => 'US-' . str_pad($i + 1001, 4, '0', STR_PAD_LEFT) . '-TR',
                    'vin' => $vin,
                    'make' => $tm['make'],
                    'model' => $tm['model'],
                    'year' => 2024 + ($i % 3),
                    'type' => $tm['type'],
                    'max_weight_kg' => $tm['weight'],
                    'max_volume_cbm' => $tm['vol'],
                    'odometer_km' => 20000 + ($i * 4500),
                    'fuel_type' => $tm['fuel'],
                    'fuel_level_pct' => 35 + ($i % 65),
                    'current_latitude' => $drvr->current_latitude,
                    'current_longitude' => $drvr->current_longitude,
                    'status' => $vStatus,
                    'last_maintenance_at' => Carbon::now()->subDays(($i % 30) + 5)->toDateString(),
                    'next_maintenance_at' => Carbon::now()->addDays(($i % 60) + 20)->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('vehicles')->insert($vehicles);
            $vehicleIds = DB::table('vehicles')->pluck('id')->toArray();

            // 7. Routes (52 Corridors)
            $routes = [];
            $routeRisks = ['low', 'low', 'medium', 'low', 'high'];
            for ($i = 0; $i < 52; $i++) {
                $cid = $compIds[$i % count($compIds)];
                $origWh = $whRows[$i % count($whRows)];
                $destWh = $whRows[($i + 7) % count($whRows)];
                $dist = 400 + ($i * 45);
                $mins = intval($dist / 1.1);

                $routes[] = [
                    'company_id' => $cid,
                    'origin_warehouse_id' => $origWh->id,
                    'destination_warehouse_id' => $destWh->id,
                    'route_code' => 'RT-' . strtoupper(substr($origWh->code, 3, 3)) . '-' . strtoupper(substr($destWh->code, 3, 3)) . '-' . (10 + $i),
                    'name' => "{$origWh->city} to {$destWh->city} Freight Corridor",
                    'origin_name' => "{$origWh->name} ({$origWh->code})",
                    'destination_name' => "{$destWh->name} ({$destWh->code})",
                    'origin_latitude' => $origWh->latitude,
                    'origin_longitude' => $origWh->longitude,
                    'destination_latitude' => $destWh->latitude,
                    'destination_longitude' => $destWh->longitude,
                    'distance_km' => $dist,
                    'estimated_duration_minutes' => $mins,
                    'toll_cost' => 15 + ($i * 2),
                    'fuel_consumption_liters' => round($dist * 0.35, 2),
                    'waypoints' => json_encode([
                        ['name' => "Weigh Station {$i}", 'lat' => ($origWh->latitude + $destWh->latitude) / 2, 'lng' => ($origWh->longitude + $destWh->longitude) / 2],
                    ]),
                    'risk_level' => $routeRisks[$i % count($routeRisks)],
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('routes')->insert($routes);
            $routeRows = DB::table('routes')->get();

            // 8. Orders (55 Commercial Freight Orders)
            $orders = [];
            $priorities = ['standard', 'express', 'critical', 'same_day'];
            $orderStatuses = ['shipped', 'delivered', 'processing', 'confirmed', 'shipped'];
            $itemCatalog = [
                ['sku' => 'EV-CELL-4680', 'desc' => '4680 Structural Battery Cell Trays', 'price' => 12500],
                ['sku' => 'CHIP-M5-PRO', 'desc' => 'High-Performance Silicon Wafers', 'price' => 45000],
                ['sku' => 'BIO-CRYO-VAX', 'desc' => 'Biopharma Active mRNA Biologics', 'price' => 28000],
                ['sku' => 'AUTO-POWERTRAIN', 'desc' => 'Dual Motor EV Powertrain Inverters', 'price' => 19500],
                ['sku' => 'SOLAR-INVERT-X', 'desc' => 'Commercial Solar Grid Inverters', 'price' => 8400],
                ['sku' => 'ROBOT-ARM-IND', 'desc' => '6-Axis Industrial Assembly Arms', 'price' => 36000],
                ['sku' => 'COLD-ORGANIC-B', 'desc' => 'Organic Fresh Produce Bulk Pallets', 'price' => 4200],
                ['sku' => 'AERO-TITANIUM-R', 'desc' => 'Aerospace Grade Titanium Fasteners', 'price' => 52000],
            ];

            for ($i = 0; $i < 55; $i++) {
                $cid = $compIds[$i % count($compIds)];
                $cust = $customerIds[$i % count($customerIds)];
                $prio = $priorities[$i % count($priorities)];
                $st = $orderStatuses[$i % count($orderStatuses)];
                $item = $itemCatalog[$i % count($itemCatalog)];
                $qty = 4 + ($i % 20);
                $totalAmt = $item['price'] * $qty;

                $orders[] = [
                    'company_id' => $cid,
                    'customer_id' => $cust,
                    'order_number' => 'ORD-2026-' . str_pad($i + 1001, 5, '0', STR_PAD_LEFT),
                    'order_date' => Carbon::now()->subDays(($i % 10) + 1)->toDateString(),
                    'required_delivery_date' => Carbon::now()->addDays(($i % 7) + 1)->toDateString(),
                    'priority' => $prio,
                    'total_amount' => $totalAmt,
                    'currency' => 'USD',
                    'payment_status' => ($st === 'delivered' ? 'paid' : 'net_30'),
                    'status' => $st,
                    'items_count' => $qty,
                    'total_weight_kg' => 2000 + ($i * 300),
                    'total_volume_cbm' => 15 + ($i % 60),
                    'notes' => "Commercial freight order: {$item['desc']}",
                    'order_items' => json_encode([
                        ['sku' => $item['sku'], 'description' => $item['desc'], 'qty' => $qty, 'unit_price' => $item['price'], 'total' => $totalAmt],
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('orders')->insert($orders);
            $orderRows = DB::table('orders')->get();

            // 9. Shipments (55 Line-Haul Consignments)
            $shipments = [];
            $shipmentStatuses = ['in_transit', 'delivered', 'out_for_delivery', 'picked_up', 'in_transit', 'delayed'];
            for ($i = 0; $i < 55; $i++) {
                $cid = $compIds[$i % count($compIds)];
                $ord = $orderRows[$i % count($orderRows)];
                $rt = $routeRows[$i % count($routeRows)];
                $sStatus = $shipmentStatuses[$i % count($shipmentStatuses)];
                $isCold = ($i % 4 === 0);

                $trkCode = 'TRK-' . str_pad($i + 1000, 4, '0', STR_PAD_LEFT) . '-' . str_pad(9999 - $i, 4, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                $shpCode = 'SHP-26' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

                $depTime = Carbon::now()->subHours(($i % 24) + 4);
                $arrTime = ($sStatus === 'delivered') ? Carbon::now()->subHours(($i % 6) + 1) : Carbon::now()->addHours(($i % 12) + 2);

                $shipments[] = [
                    'company_id' => $cid,
                    'order_id' => $ord->id,
                    'origin_warehouse_id' => $rt->origin_warehouse_id,
                    'destination_warehouse_id' => $rt->destination_warehouse_id,
                    'route_id' => $rt->id,
                    'shipment_number' => $shpCode,
                    'tracking_number' => $trkCode,
                    'carrier_type' => ($i % 5 === 0 ? 'third_party_3pl' : 'in_house'),
                    'carrier_name' => ($i % 5 === 0 ? 'Apex Partner 3PL' : 'Titan Apex Dedicated Fleet'),
                    'temperature_controlled' => $isCold,
                    'target_temp_celsius' => $isCold ? 4.0 : null,
                    'status' => $sStatus,
                    'estimated_departure' => $depTime,
                    'actual_departure' => $depTime->copy()->addMinutes(15),
                    'estimated_arrival' => $arrTime,
                    'actual_arrival' => ($sStatus === 'delivered') ? $arrTime : null,
                    'special_instructions' => $isCold ? 'Strict NIST 2°C to 8°C Cold Chain Active' : 'Maintain real-time GPS telemetry.',
                    'timeline_events' => json_encode([
                        ['time' => $depTime->toIso8601String(), 'event' => 'Consignment manifested and loaded'],
                        ['time' => $depTime->copy()->addHours(2)->toIso8601String(), 'event' => 'Departed origin hub; telemetry stream active'],
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('shipments')->insert($shipments);
            $shipmentRows = DB::table('shipments')->get();

            // 10. Deliveries (55 Final-Mile Fulfillment Executions)
            $deliveries = [];
            $delivStatuses = ['en_route', 'completed', 'en_route', 'arrived', 'completed', 'dispatched'];
            for ($i = 0; $i < 55; $i++) {
                $cid = $compIds[$i % count($compIds)];
                $shp = $shipmentRows[$i % count($shipmentRows)];
                $drvr = $driverRows[$i % count($driverRows)];
                $veh = $vehicleIds[$i % count($vehicleIds)];
                $dStatus = $delivStatuses[$i % count($delivStatuses)];

                $delivNumber = 'DEL-2026-' . str_pad($i + 8801, 5, '0', STR_PAD_LEFT);
                $delivTime = ($dStatus === 'completed') ? Carbon::now()->subHours(($i % 12) + 1) : null;

                $deliveries[] = [
                    'company_id' => $cid,
                    'shipment_id' => $shp->id,
                    'driver_id' => $drvr->id,
                    'vehicle_id' => $veh,
                    'route_id' => $shp->route_id,
                    'delivery_number' => $delivNumber,
                    'recipient_name' => 'Commercial Receiving Dock #' . (($i % 12) + 1),
                    'recipient_phone' => '+1 (800) 555-' . str_pad($i + 1000, 4, '0', STR_PAD_LEFT),
                    'delivery_address' => (($i * 120) + 1000) . ' Industrial Logistics Parkway',
                    'delivery_city' => 'Metropolitan Receiving Sector',
                    'delivery_latitude' => $drvr->current_latitude,
                    'delivery_longitude' => $drvr->current_longitude,
                    'scheduled_window_start' => Carbon::now()->subHours(($i % 6) + 1),
                    'scheduled_window_end' => Carbon::now()->addHours(($i % 8) + 2),
                    'delivered_at' => $delivTime,
                    'proof_of_delivery_signature' => ($dStatus === 'completed') ? 'Signed by Logistics Receiving Mgr' : null,
                    'proof_of_delivery_photo_url' => ($dStatus === 'completed') ? 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600' : null,
                    'customer_feedback_rating' => ($dStatus === 'completed') ? (($i % 2) + 4) : null,
                    'status' => $dStatus,
                    'failure_reason' => null,
                    'notes' => "Final-mile delivery for consignment {$shp->tracking_number}.",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('deliveries')->insert($deliveries);
        });
    }
}

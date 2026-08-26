<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('current_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('vehicle_code')->unique();
            $table->string('plate_number')->unique();
            $table->string('vin')->unique();
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->enum('type', ['semi_truck', 'box_truck', 'cargo_van', 'flatbed', 'refrigerated', 'electric_van'])->default('semi_truck');
            $table->decimal('max_weight_kg', 10, 2)->default(20000.00);
            $table->decimal('max_volume_cbm', 8, 2)->default(80.00);
            $table->decimal('odometer_km', 10, 2)->default(0.00);
            $table->enum('fuel_type', ['diesel', 'electric', 'hybrid', 'cng'])->default('diesel');
            $table->decimal('fuel_level_pct', 5, 2)->default(100.00);
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->enum('status', ['active', 'in_transit', 'maintenance', 'idle', 'decommissioned'])->default('active');
            $table->date('last_maintenance_at')->nullable();
            $table->date('next_maintenance_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('origin_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('route_code')->unique();
            $table->string('name');
            $table->string('origin_name');
            $table->string('destination_name');
            $table->decimal('origin_latitude', 10, 7)->nullable();
            $table->decimal('origin_longitude', 10, 7)->nullable();
            $table->decimal('destination_latitude', 10, 7)->nullable();
            $table->decimal('destination_longitude', 10, 7)->nullable();
            $table->decimal('distance_km', 8, 2)->default(0.00);
            $table->integer('estimated_duration_minutes')->default(0);
            $table->decimal('toll_cost', 8, 2)->default(0.00);
            $table->decimal('fuel_consumption_liters', 8, 2)->default(0.00);
            $table->json('waypoints')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'severe_weather'])->default('low');
            $table->enum('status', ['active', 'congested', 'closed', 'alternative'])->default('active');
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};

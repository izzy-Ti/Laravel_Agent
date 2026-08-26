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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('origin_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('routes')->nullOnDelete();
            $table->string('shipment_number')->unique();
            $table->string('tracking_number')->unique();
            $table->enum('carrier_type', ['in_house', 'third_party_3pl', 'partner_fleet'])->default('in_house');
            $table->string('carrier_name')->default('In-House Fleet');
            $table->boolean('temperature_controlled')->default(false);
            $table->decimal('target_temp_celsius', 4, 1)->nullable();
            $table->enum('status', ['manifested', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'delayed', 'exception'])->default('in_transit');
            $table->dateTime('estimated_departure')->nullable();
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('estimated_arrival')->nullable();
            $table->dateTime('actual_arrival')->nullable();
            $table->text('special_instructions')->nullable();
            $table->json('timeline_events')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['tracking_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

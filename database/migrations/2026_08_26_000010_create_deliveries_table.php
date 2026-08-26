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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('routes')->nullOnDelete();
            $table->string('delivery_number')->unique();
            $table->string('recipient_name');
            $table->string('recipient_phone')->nullable();
            $table->text('delivery_address');
            $table->string('delivery_city')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->dateTime('scheduled_window_start')->nullable();
            $table->dateTime('scheduled_window_end')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->string('proof_of_delivery_signature')->nullable();
            $table->string('proof_of_delivery_photo_url')->nullable();
            $table->integer('customer_feedback_rating')->nullable();
            $table->enum('status', ['pending', 'dispatched', 'en_route', 'arrived', 'completed', 'failed', 'rescheduled'])->default('en_route');
            $table->string('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['delivery_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};

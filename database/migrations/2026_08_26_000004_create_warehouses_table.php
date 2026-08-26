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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('USA');
            $table->string('zip_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('capacity_sqft')->default(50000);
            $table->decimal('current_utilization_pct', 5, 2)->default(0.00);
            $table->enum('type', ['fulfillment', 'distribution', 'cross_dock', 'cold_storage', 'bonded'])->default('fulfillment');
            $table->string('operating_hours')->default('24/7');
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_email')->nullable();
            $table->enum('status', ['active', 'maintenance', 'closed'])->default('active');
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};

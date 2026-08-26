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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('driver_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('license_number')->unique();
            $table->string('license_type')->default('Class A CDL');
            $table->date('license_expiry')->nullable();
            $table->string('phone');
            $table->string('emergency_contact')->nullable();
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->enum('status', ['available', 'on_trip', 'off_duty', 'on_break', 'suspended'])->default('available');
            $table->decimal('safety_score', 4, 1)->default(98.5); // e.g. 98.5 / 100
            $table->decimal('rating', 3, 2)->default(4.90); // e.g. 4.90 / 5.0
            $table->integer('total_trips')->default(0);
            $table->decimal('total_distance_km', 10, 2)->default(0.00);
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};

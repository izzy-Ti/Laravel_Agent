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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained('companies')->nullOnDelete();
            $table->string('role')->default('dispatcher')->after('email'); // 'ceo', 'operations_manager', 'dispatcher', 'driver', 'warehouse_manager', 'analyst', 'customer_admin'
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar_url')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('avatar_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'role', 'phone', 'avatar_url', 'status']);
        });
    }
};

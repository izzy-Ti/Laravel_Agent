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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->enum('tier', ['enterprise', 'strategic', 'priority', 'standard'])->default('standard');
            $table->decimal('credit_limit', 12, 2)->default(50000.00);
            $table->decimal('outstanding_balance', 12, 2)->default(0.00);
            $table->enum('status', ['active', 'credit_hold', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

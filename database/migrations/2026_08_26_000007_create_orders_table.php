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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('required_delivery_date')->nullable();
            $table->enum('priority', ['standard', 'express', 'critical', 'same_day'])->default('standard');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('currency')->default('USD');
            $table->enum('payment_status', ['paid', 'pending', 'net_30', 'overdue', 'failed'])->default('pending');
            $table->enum('status', ['draft', 'confirmed', 'processing', 'manifested', 'shipped', 'delivered', 'cancelled', 'returned'])->default('confirmed');
            $table->integer('items_count')->default(1);
            $table->decimal('total_weight_kg', 10, 2)->default(0.00);
            $table->decimal('total_volume_cbm', 8, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->json('order_items')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

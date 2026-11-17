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
        // 1. Stock Transfers (Inter-Warehouse Mutasi) Table
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // e.g. TRF-202611-0001
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('transfer_date');
            $table->date('expected_arrival_date')->nullable();
            $table->datetime('received_date')->nullable();
            $table->enum('status', ['draft', 'in_transit', 'received', 'cancelled'])->default('draft');
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate_number')->nullable();
            $table->string('expedition')->nullable(); // Armada Internal / Vendor (JNE Cargo, dll)
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_items_count')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->integer('total_received_quantity')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('dispatched_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Stock Transfer Items Table
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('source_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('destination_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('sku');
            $table->string('product_title');
            $table->integer('quantity'); // Jumlah dikirim
            $table->integer('received_quantity')->default(0); // Jumlah diterima
            $table->string('unit')->default('pcs');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};

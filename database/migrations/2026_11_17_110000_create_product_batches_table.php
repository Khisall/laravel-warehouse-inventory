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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('batch_number'); // e.g. BATCH-202611-001
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->enum('status', ['active', 'depleted', 'expired', 'quarantine'])->default('active');
            $table->string('location_code')->nullable(); // Rak / Bin location e.g. RAK-A-02
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index for high-performance FEFO queries
            $table->index(['product_id', 'status', 'expiry_date']);
            $table->index(['warehouse_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};

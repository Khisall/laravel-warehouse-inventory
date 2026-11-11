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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('warehouse_location')->default('Location A: 1');
            $table->integer('quantity')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('unit')->default('pcs');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('image')->nullable();
            $table->string('variation_text')->nullable(); // e.g. "Variation (4)"
            $table->integer('variations_count')->default(0);
            $table->string('status_code')->default('ACCO'); // as displayed in UI table (ACCO, 11SE, CACO, etc.)
            $table->string('status')->default('in_stock'); // in_stock, low_stock, out_of_stock
            $table->boolean('is_marketplace_listed')->default(false);
            $table->text('description')->nullable();
            $table->timestamp('last_modified')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

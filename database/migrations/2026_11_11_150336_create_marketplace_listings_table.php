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
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('channel'); // Shopee, Tokopedia, TikTok, Lazada, Amazon, eBay
            $table->string('channel_sku')->nullable();
            $table->string('external_product_id')->nullable();
            $table->string('channel_code')->default('ACCO');
            $table->decimal('channel_price', 12, 2)->default(0);
            $table->integer('synced_stock')->default(0);
            $table->string('sync_status')->default('synced'); // synced, pending, failed
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};

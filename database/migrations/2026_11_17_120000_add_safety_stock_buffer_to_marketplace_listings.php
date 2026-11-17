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
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->integer('safety_stock_buffer')->default(3)->after('synced_stock'); // Stok cadangan offline / safety buffer
            $table->string('store_name')->default('Official Store')->after('channel'); // e.g. Tokopedia Official, Shopee Mall
            $table->boolean('auto_sync')->default(true)->after('sync_status'); // Auto sync on inventory change
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn(['safety_stock_buffer', 'store_name', 'auto_sync']);
        });
    }
};

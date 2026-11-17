<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MarketplaceListing;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class MarketplaceSyncTest extends TestCase
{
    protected User $user;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Product $product;
    protected MarketplaceListing $shopeeListing;
    protected MarketplaceListing $tokopediaListing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            ['name' => 'Khisal (Super Admin)', 'role' => 'super_admin', 'password' => bcrypt('password123'), 'is_active' => true]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MKT-TEST'],
            ['name' => 'Gudang MKT Test']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-mkt-test'],
            ['name' => 'Kategori MKT']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-MKT-PROD'],
            [
                'title' => 'Produk Marketplace Uji',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 50,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 70000,
                'price' => 120000,
                'is_marketplace_listed' => true,
            ]
        );

        $this->product->update(['quantity' => 50]);

        // Clean and create listings for this product
        MarketplaceListing::where('product_id', $this->product->id)->delete();

        $this->shopeeListing = MarketplaceListing::create([
            'product_id' => $this->product->id,
            'channel' => 'Shopee',
            'store_name' => 'Shopee Official Store',
            'channel_sku' => 'MKT-SHP-TEST',
            'channel_price' => 120000,
            'safety_stock_buffer' => 5,
            'synced_stock' => 45, // 50 - 5
            'sync_status' => 'synced',
        ]);

        $this->tokopediaListing = MarketplaceListing::create([
            'product_id' => $this->product->id,
            'channel' => 'Tokopedia',
            'store_name' => 'Tokopedia Mall',
            'channel_sku' => 'MKT-TKP-TEST',
            'channel_price' => 125000,
            'safety_stock_buffer' => 3,
            'synced_stock' => 47, // 50 - 3
            'sync_status' => 'synced',
        ]);
    }

    public function test_user_can_view_marketplace_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('marketplace.index'));
        $response->assertStatus(200);
        $response->assertSee('Marketplace Multi-Channel Sync');
        $response->assertSee('Shopee');
        $response->assertSee('Tokopedia');
    }

    public function test_user_can_update_safety_stock_buffer(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('marketplace.update-buffer', $this->shopeeListing->id),
            ['safety_stock_buffer' => 10]
        );

        $response->assertRedirect();
        $freshShopee = $this->shopeeListing->fresh();
        $this->assertEquals(10, $freshShopee->safety_stock_buffer);
        // Synced stock should now be 50 - 10 = 40
        $this->assertEquals(40, $freshShopee->synced_stock);
    }

    public function test_user_can_simulate_marketplace_order_and_stocks_sync(): void
    {
        $payload = [
            'channel' => 'Shopee',
            'product_id' => $this->product->id,
            'quantity' => 15,
            'customer_name' => 'Rina Shopee Buyer',
        ];

        $response = $this->actingAs($this->user)->post(route('marketplace.simulate-order'), $payload);
        $response->assertRedirect(route('marketplace.index'));

        // Master stock should be 50 - 15 = 35
        $this->assertEquals(35, $this->product->fresh()->quantity);

        // Shopee listing: buffer 5 -> synced stock should be 35 - 5 = 30
        $this->assertEquals(30, $this->shopeeListing->fresh()->synced_stock);

        // Tokopedia listing: buffer 3 -> synced stock should be 35 - 3 = 32
        $this->assertEquals(32, $this->tokopediaListing->fresh()->synced_stock);

        // Verify order created
        $this->assertDatabaseHas('orders', [
            'marketplace' => 'Shopee',
            'customer_name' => 'Rina Shopee Buyer',
            'status' => 'completed',
        ]);
    }

    public function test_user_can_sync_all_marketplace_channels(): void
    {
        $response = $this->actingAs($this->user)->post(route('marketplace.sync-all'));
        $response->assertRedirect(route('marketplace.index'));
        $this->assertEquals(45, $this->shopeeListing->fresh()->synced_stock);
        $this->assertEquals(47, $this->tokopediaListing->fresh()->synced_stock);
    }
}

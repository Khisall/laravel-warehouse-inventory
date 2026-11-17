<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class ScannerTest extends TestCase
{
    protected User $user;
    protected Warehouse $warehouse;
    protected Warehouse $destWarehouse;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            ['name' => 'Khisal (Super Admin)', 'role' => 'super_admin', 'password' => bcrypt('password123'), 'is_active' => true]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-SCAN-TEST'],
            ['name' => 'Gudang Scanner Test']
        );

        $this->destWarehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-SCAN-DST'],
            ['name' => 'Gudang Scanner Dest']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-scan-test'],
            ['name' => 'Kategori Scan']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'SKU-SCAN-001'],
            [
                'title' => 'Produk Scanner Barcode Uji',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 20,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 40000,
                'price' => 75000,
            ]
        );

        $this->product->update(['quantity' => 20]);
    }

    public function test_scanner_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('scanner.index'));
        $response->assertStatus(200);
        $response->assertSee('Scanner Barcode');
    }

    public function test_scanner_lookup_returns_400_for_empty_code(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('scanner.lookup', ['code' => '']));
        $response->assertStatus(400);
    }

    public function test_scanner_lookup_returns_404_for_unknown_code(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('scanner.lookup', ['code' => 'NONEXISTENT-999']));
        $response->assertStatus(404);
    }

    public function test_scanner_lookup_finds_product_by_sku(): void
    {
        // Add a batch to test batch breakdown
        ProductBatch::firstOrCreate(
            ['batch_number' => 'LOT-SCAN-B1'],
            [
                'product_id' => $this->product->id,
                'warehouse_id' => $this->warehouse->id,
                'initial_quantity' => 10,
                'current_quantity' => 10,
                'expiry_date' => now()->addDays(60),
                'status' => 'active',
            ]
        );

        $response = $this->actingAs($this->user)->getJson(route('scanner.lookup', ['code' => $this->product->sku]));
        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'product',
            'data' => [
                'sku' => $this->product->sku,
                'title' => $this->product->title,
                'quantity' => 20,
            ]
        ]);
    }

    public function test_scanner_lookup_finds_transfer_by_transfer_number(): void
    {
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-SCAN-' . uniqid(),
            'source_warehouse_id' => $this->warehouse->id,
            'destination_warehouse_id' => $this->destWarehouse->id,
            'transfer_date' => now(),
            'status' => 'in_transit',
            'total_quantity' => 5,
            'total_items_count' => 1,
            'created_by' => $this->user->id,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'source_product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_title' => $this->product->title,
            'quantity' => 5,
            'unit' => 'pcs',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('scanner.lookup', ['code' => $transfer->transfer_number]));
        $response->assertStatus(200);
        $response->assertJson([
            'type' => 'transfer',
            'data' => [
                'transfer_number' => $transfer->transfer_number,
                'status' => 'in_transit',
            ]
        ]);
    }

    public function test_scanner_can_quick_adjust_stock(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'delta_quantity' => 5,
            'reason' => 'Quick adjustment dari scanner uji',
        ];

        $response = $this->actingAs($this->user)->postJson(route('scanner.quick-adjust'), $payload);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'new_quantity' => 25, // 20 + 5
        ]);

        $this->assertEquals(25, $this->product->fresh()->quantity);
    }
}

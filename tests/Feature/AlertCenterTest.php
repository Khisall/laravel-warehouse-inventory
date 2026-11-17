<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class AlertCenterTest extends TestCase
{
    protected User $admin;
    protected User $staff;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Product $lowStockProduct;
    protected Product $expiredProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            [
                'name' => 'Khisal (Owner / Super Admin)',
                'role' => 'super_admin',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        $this->staff = User::firstOrCreate(
            ['email' => 'staff@inventory.com'],
            [
                'name' => 'Andi Pratama (Operator Gudang)',
                'role' => 'warehouse_staff',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-ALERT-TEST'],
            ['name' => 'Gudang Alert Test', 'address' => 'Jl. Alert No. 1']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-alert-test'],
            ['name' => 'Kategori Alert']
        );

        // Product with Low Stock
        $this->lowStockProduct = Product::firstOrCreate(
            ['sku' => 'TEST-ALERT-LOW'],
            [
                'title' => 'Produk Menipis Uji',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 2,
                'min_stock' => 10,
                'unit' => 'pcs',
                'cost_price' => 25000,
                'price' => 45000,
            ]
        );
        $this->lowStockProduct->update(['quantity' => 2, 'min_stock' => 10]);

        // Product with Expired Date
        $this->expiredProduct = Product::firstOrCreate(
            ['sku' => 'TEST-ALERT-EXP'],
            [
                'title' => 'Produk Expired Uji',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 15,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 30000,
                'price' => 55000,
                'batch_number' => 'BATCH-EXP-001',
                'expiry_date' => now()->subDays(5)->format('Y-m-d'), // Expired 5 days ago
            ]
        );
        $this->expiredProduct->update(['expiry_date' => now()->subDays(5)->format('Y-m-d')]);
    }

    public function test_guest_cannot_access_alerts(): void
    {
        $response = $this->get(route('alerts.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_stock_alerts_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('alerts.index', ['tab' => 'stock']));
        $response->assertStatus(200);
        $response->assertSee('Pusat Peringatan');
        $response->assertSee($this->lowStockProduct->sku);
    }

    public function test_authenticated_user_can_view_expiry_alerts_tab(): void
    {
        $response = $this->actingAs($this->admin)->get(route('alerts.index', ['tab' => 'expiry']));
        $response->assertStatus(200);
        $response->assertSee('Monitoring Kedaluwarsa');
        $response->assertSee($this->expiredProduct->sku);
        $response->assertSee('Kadaluarsa');
    }

    public function test_alerts_summary_api_returns_json(): void
    {
        $response = $this->actingAs($this->staff)->getJson(route('alerts.summary'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_alerts',
            'out_of_stock',
            'low_stock',
            'expired',
            'expiring_soon',
            'urgent_stock',
            'urgent_expiry',
        ]);
        $this->assertGreaterThan(0, $response->json('total_alerts'));
    }

    public function test_user_can_write_off_expired_stock(): void
    {
        $initialStock = $this->expiredProduct->quantity;

        $response = $this->actingAs($this->staff)->post(route('alerts.write-off', $this->expiredProduct->id), [
            'quantity' => 5,
            'reason' => 'Pemusnahan barang kedaluwarsa uji',
        ]);

        $response->assertRedirect();
        $this->expiredProduct->refresh();
        $this->assertEquals($initialStock - 5, $this->expiredProduct->quantity);

        // Verify stock movement of type 'out'
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->expiredProduct->id,
            'type' => 'out',
            'quantity' => 5,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock - 5,
        ]);
    }

    public function test_artisan_command_check_alerts_runs_successfully(): void
    {
        $this->artisan('inventory:check-alerts')
            ->assertExitCode(0);
    }
}

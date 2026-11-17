<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class StockAnalyticsTest extends TestCase
{
    protected User $admin;
    protected User $manager;
    protected User $staff;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Product $topProduct;
    protected Product $deadProduct;

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

        $this->manager = User::firstOrCreate(
            ['email' => 'manager@inventory.com'],
            [
                'name' => 'Budi Santoso (Kepala Gudang)',
                'role' => 'warehouse_manager',
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
            ['code' => 'WH-ANALYTICS'],
            ['name' => 'Gudang Analitik', 'address' => 'Jl. Analitik No. 88']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-analytics'],
            ['name' => 'Kategori Analisis']
        );

        // Top Selling Product (Fast Moving / Class A)
        $this->topProduct = Product::firstOrCreate(
            ['sku' => 'TEST-ANL-TOP'],
            [
                'title' => 'Produk Terlaris Kelas A',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 25,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 100000,
                'price' => 200000,
            ]
        );

        // Dead Stock Product (No sales in > 90 days)
        $this->deadProduct = Product::firstOrCreate(
            ['sku' => 'TEST-ANL-DEAD'],
            [
                'title' => 'Produk Dead Stock Uji',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 40,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 50000,
                'price' => 80000,
            ]
        );

        // Seed an order for topProduct
        $order = Order::create([
            'order_number' => 'ORD-ANL-' . uniqid(),
            'customer_name' => 'Tester Pelanggan',
            'marketplace' => 'Shopee Official',
            'status' => 'completed',
            'total_amount' => 2000000,
            'created_at' => now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->topProduct->id,
            'product_title' => $this->topProduct->title,
            'sku' => $this->topProduct->sku,
            'quantity' => 10,
            'unit_price' => 200000,
            'subtotal' => 2000000,
            'created_at' => now()->subDays(5),
        ]);
    }

    public function test_guest_cannot_access_analytics(): void
    {
        $response = $this->get(route('analytics.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_denied_access_to_analytics(): void
    {
        $response = $this->actingAs($this->staff)->get(route('analytics.index'));
        $response->assertRedirect(route('inventory.index'));
    }

    public function test_manager_and_admin_can_access_analytics_dashboard(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('analytics.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Analisis Perputaran Stok & Klasifikasi ABC');
        $responseAdmin->assertSee('Total Omset Penjualan');
        $responseAdmin->assertSee($this->topProduct->sku);

        $responseManager = $this->actingAs($this->manager)->get(route('analytics.index'));
        $responseManager->assertStatus(200);
    }

    public function test_can_filter_analytics_by_abc_classification(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.index', ['classification' => 'A']));
        $response->assertStatus(200);
        $response->assertSee('KELAS A');
    }

    public function test_can_filter_analytics_by_velocity(): void
    {
        $responseDead = $this->actingAs($this->admin)->get(route('analytics.index', ['velocity' => 'dead']));
        $responseDead->assertStatus(200);
        $responseDead->assertSee('Dead Stock');
        $responseDead->assertSee($this->deadProduct->sku);

        $responseFast = $this->actingAs($this->admin)->get(route('analytics.index', ['velocity' => 'fast']));
        $responseFast->assertStatus(200);
        $responseFast->assertSee('Fast-Moving');
        $responseFast->assertSee($this->topProduct->sku);
    }

    public function test_analytics_csv_export_returns_streamed_file(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.export-csv'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('analisis_perputaran_stok_abc', $response->headers->get('Content-Disposition'));
    }

    public function test_analytics_printable_report_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('analytics.print'));
        $response->assertStatus(200);
        $response->assertSee('LAPORAN EKSEKUTIF ANALITIK GUDANG');
        $response->assertSee('ANALISIS PERPUTARAN STOK');
        $response->assertSee('KLASIFIKASI ABC');
    }
}

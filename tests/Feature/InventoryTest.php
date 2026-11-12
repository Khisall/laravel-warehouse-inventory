<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    protected User $admin;
    protected User $staff;

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
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get('/inventory');
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@inventory.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_root_redirects_to_inventory(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('inventory.index'));
    }

    public function test_inventory_dashboard_loads_seeded_data(): void
    {
        $response = $this->actingAs($this->admin)->get('/inventory');
        $response->assertStatus(200);
        $response->assertSee('Winter Jacket Leather');
        $response->assertSee('MRP400');
        $response->assertSee('MRP405');
        $response->assertSee('ACCO');
    }

    public function test_warehouses_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/warehouses');
        $response->assertStatus(200);
        $response->assertSee('Warehouses');
    }

    public function test_activity_log_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/activity');
        $response->assertStatus(200);
        $response->assertSee('Activity &amp; Inventory Log', false);
    }

    public function test_marketplace_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/marketplace');
        $response->assertStatus(200);
        $response->assertSee('Marketplace');
    }

    public function test_orders_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('Orders');
    }

    public function test_shipping_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/shipping');
        $response->assertStatus(200);
        $response->assertSee('Shipping');
    }

    public function test_reports_accessible_by_admin_but_denied_to_staff(): void
    {
        // Admin can access
        $adminResp = $this->actingAs($this->admin)->get('/reports');
        $adminResp->assertStatus(200);

        // Staff is denied
        $staffResp = $this->actingAs($this->staff)->get('/reports');
        $staffResp->assertRedirect(route('inventory.index'));
    }

    public function test_user_management_accessible_only_by_super_admin(): void
    {
        // Super Admin can access
        $adminResp = $this->actingAs($this->admin)->get('/users');
        $adminResp->assertStatus(200);
        $adminResp->assertSee('Manajemen Pengguna');

        // Staff is denied
        $staffResp = $this->actingAs($this->staff)->get('/users');
        $staffResp->assertRedirect(route('inventory.index'));
    }

    public function test_can_adjust_product_stock_quickly(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $initialQty = $product->quantity;

        $response = $this->actingAs($this->admin)->post(route('inventory.adjust-stock', $product), [
            'type' => 'in',
            'quantity' => 5,
            'reference_no' => 'TEST-RCV-001',
            'notes' => 'Testing quick intake',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => $initialQty + 5,
        ]);
    }

    public function test_can_lookup_sku_api(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $response = $this->actingAs($this->admin)->get('/inventory/lookup/' . $product->sku);
        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'product' => [
                'sku' => $product->sku,
                'title' => $product->title,
            ]
        ]);
    }

    public function test_can_render_print_label(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $response = $this->actingAs($this->admin)->get('/inventory/' . $product->id . '/print-label');
        $response->assertStatus(200);
        $response->assertSee($product->sku);
    }

    public function test_can_render_delivery_order(): void
    {
        $order = Order::first();
        $this->assertNotNull($order);

        $response = $this->actingAs($this->admin)->get('/orders/' . $order->id . '/delivery-order');
        $response->assertStatus(200);
        $response->assertSee('SURAT JALAN');
        $response->assertSee($order->order_number);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    public function test_root_redirects_to_inventory(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('inventory.index'));
    }

    public function test_inventory_dashboard_loads_seeded_data(): void
    {
        $response = $this->get('/inventory');
        $response->assertStatus(200);
        $response->assertSee('Winter Jacket Leather');
        $response->assertSee('MRP400');
        $response->assertSee('MRP405');
        $response->assertSee('ACCO');
    }

    public function test_warehouses_page_loads(): void
    {
        $response = $this->get('/warehouses');
        $response->assertStatus(200);
        $response->assertSee('Warehouses');
    }

    public function test_activity_log_page_loads(): void
    {
        $response = $this->get('/activity');
        $response->assertStatus(200);
        $response->assertSee('Activity &amp; Inventory Log', false);
    }

    public function test_marketplace_page_loads(): void
    {
        $response = $this->get('/marketplace');
        $response->assertStatus(200);
        $response->assertSee('Marketplace');
    }

    public function test_orders_page_loads(): void
    {
        $response = $this->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('Orders');
    }

    public function test_shipping_page_loads(): void
    {
        $response = $this->get('/shipping');
        $response->assertStatus(200);
        $response->assertSee('Shipping');
    }

    public function test_reports_page_loads(): void
    {
        $response = $this->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports');
    }

    public function test_can_adjust_product_stock_quickly(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $initialQty = $product->quantity;

        $response = $this->post(route('inventory.adjust-stock', $product), [
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
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'reference_no' => 'TEST-RCV-001',
        ]);
    }

    public function test_can_lookup_sku_api(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        $response = $this->get('/inventory/lookup/' . $product->sku);
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

        $response = $this->get('/inventory/' . $product->id . '/print-label');
        $response->assertStatus(200);
        $response->assertSee($product->sku);
    }

    public function test_can_render_delivery_order(): void
    {
        $order = \App\Models\Order::first();
        $this->assertNotNull($order);

        $response = $this->get('/orders/' . $order->id . '/delivery-order');
        $response->assertStatus(200);
        $response->assertSee('SURAT JALAN');
        $response->assertSee($order->order_number);
    }
}

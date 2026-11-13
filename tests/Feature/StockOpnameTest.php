<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    protected User $admin;
    protected User $manager;
    protected User $staff;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Product $product;

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
            ['code' => 'WH-TEST'],
            ['name' => 'Gudang Pengujian', 'address' => 'Jl. Test No. 1']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-test'],
            ['name' => 'Kategori Uji']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-OPN-01'],
            [
                'title' => 'Produk Uji Opname',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 50,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 100000,
                'price' => 150000,
            ]
        );
        $this->product->update(['quantity' => 50]);

        // Clean up test opnames to avoid unique constraint duplicates
        StockOpname::where('opname_number', 'like', 'OPN-TEST%')->delete();
    }

    public function test_guest_cannot_access_opname(): void
    {
        $response = $this->get(route('opname.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_opname_index(): void
    {
        $response = $this->actingAs($this->staff)->get(route('opname.index'));
        $response->assertStatus(200);
        $response->assertSee('Stock Opname Digital');
    }

    public function test_user_can_create_new_opname_session(): void
    {
        $uniqueTitle = 'Audit Akhir Pekan Test ' . uniqid();

        $response = $this->actingAs($this->manager)->post(route('opname.store'), [
            'title' => $uniqueTitle,
            'warehouse_id' => $this->warehouse->id,
            'category_id' => $this->category->id,
            'notes' => 'Catatan uji otomatis',
        ]);

        $this->assertDatabaseHas('stock_opnames', [
            'title' => $uniqueTitle,
            'status' => 'in_progress',
        ]);

        $opname = StockOpname::where('title', $uniqueTitle)->first();
        $this->assertNotNull($opname);
        $response->assertRedirect(route('opname.show', $opname->id));

        // Verify items were snapshotted
        $this->assertDatabaseHas('stock_opname_items', [
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
        ]);
    }

    public function test_barcode_scan_increments_physical_stock(): void
    {
        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-SCAN-' . uniqid(),
            'title' => 'Test Barcode Scan',
            'status' => 'in_progress',
            'created_by' => $this->staff->id,
            'total_items' => 1,
        ]);

        $item = StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'physical_stock' => 0,
            'variance' => -50,
            'cost_price' => 100000,
            'variance_value' => -5000000,
            'is_counted' => false,
        ]);

        // First scan sets to 1
        $response = $this->actingAs($this->staff)->postJson(route('opname.scan', $opname->id), [
            'barcode' => $this->product->sku,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'item' => [
                'physical_stock' => 1,
                'variance' => -49,
            ],
        ]);

        // Second scan increments to 2
        $response2 = $this->actingAs($this->staff)->postJson(route('opname.scan', $opname->id), [
            'barcode' => $this->product->sku,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'item' => [
                'physical_stock' => 2,
                'variance' => -48,
            ],
        ]);
    }

    public function test_manager_can_reconcile_stock_opname(): void
    {
        $this->product->update(['quantity' => 50]);

        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-RECON-' . uniqid(),
            'title' => 'Test Reconcile Session',
            'status' => 'in_progress',
            'created_by' => $this->manager->id,
            'total_items' => 1,
        ]);

        $item = StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'physical_stock' => 45, // Found 45 instead of 50 (-5 variance)
            'variance' => -5,
            'cost_price' => 100000,
            'variance_value' => -500000,
            'is_counted' => true,
        ]);

        $response = $this->actingAs($this->manager)->post(route('opname.reconcile', $opname->id));
        $response->assertRedirect(route('opname.show', $opname->id));

        // Verify product stock is updated to 45
        $this->product->refresh();
        $this->assertEquals(45, $this->product->quantity);

        // Verify stock movement adjustment was recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => -5,
            'stock_before' => 50,
            'stock_after' => 45,
            'reference_no' => $opname->opname_number,
        ]);

        // Verify opname status is completed
        $opname->refresh();
        $this->assertEquals('completed', $opname->status);
        $this->assertNotNull($opname->completed_at);
    }

    public function test_staff_cannot_reconcile_stock_opname(): void
    {
        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-STAFF-' . uniqid(),
            'title' => 'Test Staff Reconcile Block',
            'status' => 'in_progress',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->post(route('opname.reconcile', $opname->id));
        $response->assertSessionHas('error');

        $opname->refresh();
        $this->assertEquals('in_progress', $opname->status);
    }

    public function test_berita_acara_report_renders(): void
    {
        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-REP-' . uniqid(),
            'title' => 'Test Report Document',
            'status' => 'completed',
            'created_by' => $this->admin->id,
            'completed_by' => $this->admin->id,
            'total_items' => 1,
            'matched_items' => 1,
        ]);

        StockOpnameItem::create([
            'stock_opname_id' => $opname->id,
            'product_id' => $this->product->id,
            'system_stock' => 50,
            'physical_stock' => 50,
            'variance' => 0,
            'cost_price' => 100000,
            'variance_value' => 0,
            'is_counted' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('opname.report', $opname->id));
        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA HASIL STOCK OPNAME');
        $response->assertSee($opname->opname_number);
    }
}

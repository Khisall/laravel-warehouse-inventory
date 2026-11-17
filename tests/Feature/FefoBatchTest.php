<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\FefoService;
use Tests\TestCase;

class FefoBatchTest extends TestCase
{
    protected User $admin;
    protected User $staff;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Product $product;
    protected FefoService $fefoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fefoService = app(FefoService::class);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            ['name' => 'Khisal (Super Admin)', 'role' => 'super_admin', 'password' => bcrypt('password123'), 'is_active' => true]
        );

        $this->staff = User::firstOrCreate(
            ['email' => 'staff@inventory.com'],
            ['name' => 'Andi Pratama', 'role' => 'warehouse_staff', 'password' => bcrypt('password123'), 'is_active' => true]
        );

        $this->warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-FEFO-TEST'],
            ['name' => 'Gudang FEFO Test']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-fefo-test'],
            ['name' => 'Kategori FEFO']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-FEFO-SKU'],
            [
                'title' => 'Produk Uji FEFO',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 100,
                'min_stock' => 10,
                'unit' => 'pcs',
                'cost_price' => 50000,
                'price' => 80000,
            ]
        );

        $this->product->update(['quantity' => 100]);

        // Clean up any existing batches for this test product
        ProductBatch::where('product_id', $this->product->id)->delete();
    }

    public function test_guest_cannot_access_batches(): void
    {
        $response = $this->get(route('batches.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_batches_index(): void
    {
        $response = $this->actingAs($this->staff)->get(route('batches.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Batch');
        $response->assertSee('FEFO');
    }

    public function test_user_can_create_new_batch(): void
    {
        $payload = [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-TEST-NEW-01',
            'initial_quantity' => 25,
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'location_code' => 'RAK-FEFO-01',
            'notes' => 'Batch baru uji FEFO',
        ];

        $response = $this->actingAs($this->staff)->post(route('batches.store'), $payload);
        $response->assertRedirect(route('batches.index'));

        $this->assertDatabaseHas('product_batches', [
            'batch_number' => 'LOT-TEST-NEW-01',
            'current_quantity' => 25,
            'status' => 'active',
        ]);

        // Product master stock should increment (100 + 25 = 125)
        $this->assertEquals(125, $this->product->fresh()->quantity);
    }

    public function test_fefo_algorithm_picks_earliest_expiring_batch_first(): void
    {
        // Batch 1: Expiring in 20 days (Priority #1)
        $b1 = ProductBatch::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-PRIORITY-1',
            'initial_quantity' => 10,
            'current_quantity' => 10,
            'expiry_date' => now()->addDays(20),
            'status' => 'active',
        ]);

        // Batch 2: Expiring in 90 days (Priority #2)
        $b2 = ProductBatch::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-PRIORITY-2',
            'initial_quantity' => 20,
            'current_quantity' => 20,
            'expiry_date' => now()->addDays(90),
            'status' => 'active',
        ]);

        // Request 15 units: Should take 10 from Batch 1 and 5 from Batch 2
        $plan = $this->fefoService->getFefoPlan($this->product, 15);

        $this->assertTrue($plan['can_fulfill']);
        $this->assertCount(2, $plan['batches_to_pick']);
        $this->assertEquals('LOT-PRIORITY-1', $plan['batches_to_pick'][0]['batch_number']);
        $this->assertEquals(10, $plan['batches_to_pick'][0]['pick_qty']);
        $this->assertEquals('LOT-PRIORITY-2', $plan['batches_to_pick'][1]['batch_number']);
        $this->assertEquals(5, $plan['batches_to_pick'][1]['pick_qty']);
    }

    public function test_fefo_deduct_updates_batches_and_product_stock(): void
    {
        // Batch 1: 10 units
        $b1 = ProductBatch::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-DEDUCT-1',
            'initial_quantity' => 10,
            'current_quantity' => 10,
            'expiry_date' => now()->addDays(15),
            'status' => 'active',
        ]);

        // Batch 2: 15 units
        $b2 = ProductBatch::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-DEDUCT-2',
            'initial_quantity' => 15,
            'current_quantity' => 15,
            'expiry_date' => now()->addDays(60),
            'status' => 'active',
        ]);

        // Deduct 14 units
        $res = $this->fefoService->deductFefo($this->product, 14, 'REF-TEST-FEFO', 'Uji Deduct FEFO');

        $this->assertEquals(14, $res['total_deducted']);

        // Batch 1 should be completely depleted
        $this->assertEquals(0, $b1->fresh()->current_quantity);
        $this->assertEquals('depleted', $b1->fresh()->status);

        // Batch 2 should have 11 units left (15 - 4 = 11)
        $this->assertEquals(11, $b2->fresh()->current_quantity);
        $this->assertEquals('active', $b2->fresh()->status);

        // Master product quantity decremented
        $this->assertEquals(86, $this->product->fresh()->quantity);
    }

    public function test_quarantined_batch_is_ignored_by_fefo_and_can_be_toggled(): void
    {
        // Quarantined batch (even if expiring earliest)
        $quarantineBatch = ProductBatch::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-QUARANTINE-1',
            'initial_quantity' => 50,
            'current_quantity' => 50,
            'expiry_date' => now()->addDays(5),
            'status' => 'quarantine',
        ]);

        // Active batch
        $activeBatch = ProductBatch::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'batch_number' => 'LOT-ACTIVE-SAFE',
            'initial_quantity' => 30,
            'current_quantity' => 30,
            'expiry_date' => now()->addDays(40),
            'status' => 'active',
        ]);

        $plan = $this->fefoService->getFefoPlan($this->product, 10);
        $this->assertCount(1, $plan['batches_to_pick']);
        $this->assertEquals('LOT-ACTIVE-SAFE', $plan['batches_to_pick'][0]['batch_number']);

        // Toggle quarantine to active
        $response = $this->actingAs($this->admin)->post(route('batches.toggle-quarantine', $quarantineBatch->id));
        $response->assertRedirect();
        $this->assertEquals('active', $quarantineBatch->fresh()->status);
    }
}

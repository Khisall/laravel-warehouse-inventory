<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    protected User $admin;
    protected User $manager;
    protected User $staff;
    protected Warehouse $sourceWh;
    protected Warehouse $destWh;
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

        $this->sourceWh = Warehouse::firstOrCreate(
            ['code' => 'WH-SRC-TEST'],
            ['name' => 'Gudang Asal Uji', 'address' => 'Jl. Asal No. 10']
        );

        $this->destWh = Warehouse::firstOrCreate(
            ['code' => 'WH-DST-TEST'],
            ['name' => 'Gudang Tujuan Uji', 'address' => 'Jl. Tujuan No. 20']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-transfer-test'],
            ['name' => 'Kategori Transfer Uji']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'SKU-TRF-SRC-1'],
            [
                'title' => 'Produk Uji Mutasi Antar Gudang',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->sourceWh->id,
                'quantity' => 50,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 100000,
                'price' => 150000,
            ]
        );

        // Reset quantity to 50 for predictable testing
        $this->product->update(['quantity' => 50, 'warehouse_id' => $this->sourceWh->id]);

        // Clean up any test destination products or transfers between test warehouses
        Product::where('warehouse_id', $this->destWh->id)->delete();
        StockTransferItem::whereHas('transfer', function ($q) {
            $q->where('source_warehouse_id', $this->sourceWh->id);
        })->delete();
        StockTransfer::where('source_warehouse_id', $this->sourceWh->id)->delete();
    }

    public function test_guest_cannot_access_transfers(): void
    {
        $response = $this->get(route('transfers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_transfers_index(): void
    {
        $response = $this->actingAs($this->staff)->get(route('transfers.index'));
        $response->assertStatus(200);
        $response->assertSee('Transfer Stok Antar Gudang');
        $response->assertSee('Total Mutasi');
    }

    public function test_can_filter_transfers_by_status(): void
    {
        $response = $this->actingAs($this->manager)->get(route('transfers.index', ['status' => 'in_transit']));
        $response->assertStatus(200);
        $response->assertSee('Dalam Pengiriman');
    }

    public function test_can_fetch_products_by_warehouse_api(): void
    {
        $response = $this->actingAs($this->staff)->get(route('transfers.products-by-warehouse', $this->sourceWh->id));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'sku' => $this->product->sku,
            'title' => $this->product->title,
        ]);
    }

    public function test_cannot_create_transfer_with_same_source_and_destination(): void
    {
        $payload = [
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->sourceWh->id, // identical
            'transfer_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                ]
            ]
        ];

        $response = $this->actingAs($this->manager)->post(route('transfers.store'), $payload);
        $response->assertSessionHasErrors('source_warehouse_id');
    }

    public function test_cannot_create_transfer_exceeding_source_stock(): void
    {
        $payload = [
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'transfer_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 9999, // exceeds 50
                ]
            ]
        ];

        $response = $this->actingAs($this->manager)->post(route('transfers.store'), $payload);
        $response->assertSessionHasErrors('items');
    }

    public function test_user_can_create_draft_transfer(): void
    {
        $payload = [
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'transfer_date' => now()->toDateString(),
            'expected_arrival_date' => now()->addDays(2)->toDateString(),
            'driver_name' => 'Sopir Ekspedisi Uji',
            'vehicle_plate_number' => 'B 1234 TEST',
            'expedition' => 'Armada Internal Gudang',
            'notes' => 'Uji mutasi draft stok.',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'notes' => 'Koli 1'
                ]
            ]
        ];

        $response = $this->actingAs($this->staff)->post(route('transfers.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('stock_transfers', [
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'driver_name' => 'Sopir Ekspedisi Uji',
            'status' => 'draft',
            'total_quantity' => 10,
        ]);

        // Stock in source warehouse should NOT be decremented yet in draft
        $this->assertEquals(50, $this->product->fresh()->quantity);
    }

    public function test_manager_can_dispatch_transfer(): void
    {
        // 1. Create a draft transfer
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-TEST-' . uniqid(),
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'transfer_date' => now(),
            'status' => 'draft',
            'total_quantity' => 15,
            'total_items_count' => 1,
            'created_by' => $this->manager->id,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'source_product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_title' => $this->product->title,
            'quantity' => 15,
            'unit' => 'pcs',
        ]);

        // 2. Dispatch
        $response = $this->actingAs($this->manager)->post(route('transfers.dispatch', $transfer->id));
        $response->assertRedirect(route('transfers.show', $transfer->id));

        // 3. Verify status changed to in_transit
        $this->assertEquals('in_transit', $transfer->fresh()->status);
        $this->assertEquals($this->manager->id, $transfer->fresh()->dispatched_by);

        // 4. Verify source stock decremented (50 - 15 = 35)
        $this->assertEquals(35, $this->product->fresh()->quantity);

        // 5. Verify StockMovement was logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->sourceWh->id,
            'type' => 'transfer_out',
            'quantity' => -15,
            'stock_before' => 50,
            'stock_after' => 35,
            'reference_no' => $transfer->transfer_number,
        ]);
    }

    public function test_user_can_receive_transfer_at_destination(): void
    {
        // 1. Create an in-transit transfer
        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-TEST-' . uniqid(),
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'transfer_date' => now(),
            'status' => 'in_transit',
            'total_quantity' => 20,
            'total_items_count' => 1,
            'created_by' => $this->manager->id,
            'dispatched_by' => $this->manager->id,
            'dispatched_at' => now(),
        ]);

        $item = StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'source_product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_title' => $this->product->title,
            'quantity' => 20,
            'unit' => 'pcs',
        ]);

        // 2. Receive at destination
        $payload = [
            'received_quantities' => [
                $item->id => 20,
            ],
            'item_notes' => [
                $item->id => 'Barang diterima lengkap dan rapi.',
            ],
            'receiving_notes' => 'Semua koli lolos QC penerimaan gudang tujuan.',
        ];

        $response = $this->actingAs($this->staff)->post(route('transfers.receive', $transfer->id), $payload);
        $response->assertRedirect(route('transfers.show', $transfer->id));

        // 3. Verify transfer is marked received
        $freshTransfer = $transfer->fresh();
        $this->assertEquals('received', $freshTransfer->status);
        $this->assertEquals(20, $freshTransfer->total_received_quantity);
        $this->assertEquals($this->staff->id, $freshTransfer->received_by);

        // 4. Verify product exists or is updated in destination warehouse
        $destProduct = Product::where('warehouse_id', $this->destWh->id)
            ->where('title', $this->product->title)
            ->first();

        $this->assertNotNull($destProduct);
        $this->assertEquals(20, $destProduct->quantity);

        // 5. Verify StockMovement In was logged at destination warehouse
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $destProduct->id,
            'warehouse_id' => $this->destWh->id,
            'type' => 'transfer_in',
            'quantity' => 20,
            'reference_no' => $transfer->transfer_number,
        ]);
    }

    public function test_user_can_cancel_in_transit_transfer_and_stock_is_returned(): void
    {
        // 1. Manually simulate dispatched transfer: product stock was reduced
        $this->product->update(['quantity' => 40]);

        $transfer = StockTransfer::create([
            'transfer_number' => 'TRF-TEST-' . uniqid(),
            'source_warehouse_id' => $this->sourceWh->id,
            'destination_warehouse_id' => $this->destWh->id,
            'transfer_date' => now(),
            'status' => 'in_transit',
            'total_quantity' => 10,
            'total_items_count' => 1,
            'created_by' => $this->manager->id,
            'dispatched_by' => $this->manager->id,
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'source_product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_title' => $this->product->title,
            'quantity' => 10,
            'unit' => 'pcs',
        ]);

        // 2. Cancel transfer
        $response = $this->actingAs($this->manager)->post(route('transfers.cancel', $transfer->id));
        $response->assertRedirect(route('transfers.show', $transfer->id));

        // 3. Verify status changed to cancelled
        $this->assertEquals('cancelled', $transfer->fresh()->status);

        // 4. Verify stock was restored to source product (40 + 10 = 50)
        $this->assertEquals(50, $this->product->fresh()->quantity);

        // 5. Verify reverse movement was logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->sourceWh->id,
            'type' => 'transfer_cancelled',
            'quantity' => 10,
            'reference_no' => $transfer->transfer_number,
        ]);
    }

    public function test_surat_jalan_printable_renders_successfully(): void
    {
        $transfer = StockTransfer::where('status', 'received')->first();
        if (!$transfer) {
            $transfer = StockTransfer::create([
                'transfer_number' => 'TRF-TEST-' . uniqid(),
                'source_warehouse_id' => $this->sourceWh->id,
                'destination_warehouse_id' => $this->destWh->id,
                'transfer_date' => now(),
                'status' => 'draft',
                'total_quantity' => 5,
                'total_items_count' => 1,
                'created_by' => $this->admin->id,
            ]);
            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'source_product_id' => $this->product->id,
                'sku' => $this->product->sku,
                'product_title' => $this->product->title,
                'quantity' => 5,
                'unit' => 'pcs',
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('transfers.surat-jalan', $transfer->id));
        $response->assertStatus(200);
        $response->assertSee('SURAT JALAN TRANSFER');
        $response->assertSee($transfer->transfer_number);
        $response->assertSee($transfer->sourceWarehouse->name);
        $response->assertSee($transfer->destinationWarehouse->name);
    }
}

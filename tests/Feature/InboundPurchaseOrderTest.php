<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

class InboundPurchaseOrderTest extends TestCase
{
    protected User $admin;
    protected User $staff;
    protected Warehouse $warehouse;
    protected Category $category;
    protected Product $product;
    protected Supplier $supplier;

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
            ['code' => 'WH-TEST-INBOUND'],
            ['name' => 'Gudang Inbound Uji', 'address' => 'Jl. Inbound No. 9']
        );

        $this->category = Category::firstOrCreate(
            ['slug' => 'cat-inbound-test'],
            ['name' => 'Kategori Inbound']
        );

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-INB-01'],
            [
                'title' => 'Produk Inbound Test',
                'category_id' => $this->category->id,
                'warehouse_id' => $this->warehouse->id,
                'quantity' => 20,
                'min_stock' => 5,
                'unit' => 'pcs',
                'cost_price' => 50000,
                'price' => 75000,
            ]
        );

        $this->supplier = Supplier::firstOrCreate(
            ['code' => 'SUP-TEST-01'],
            [
                'name' => 'PT. Supplier Uji Mandiri',
                'contact_person' => 'Bambang',
                'phone' => '+62 811-9988-7711',
                'email' => 'bambang@supplieruji.com',
                'payment_terms' => 'NET 30',
                'is_active' => true,
            ]
        );

        // Clean up test POs and GRNs
        PurchaseOrder::where('po_number', 'like', 'PO-TEST%')->orWhere('notes', 'like', '%PO Uji%')->delete();
        Supplier::where('code', 'like', 'SUP-MB%')->delete();
    }

    public function test_guest_cannot_access_purchase_orders(): void
    {
        $response = $this->get(route('purchase-orders.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_suppliers_and_create_supplier(): void
    {
        $response = $this->actingAs($this->admin)->get(route('suppliers.index'));
        $response->assertStatus(200);
        $response->assertSee('PT. Supplier Uji Mandiri');

        $code = 'SUP-MB-' . uniqid();
        $createResponse = $this->actingAs($this->admin)->post(route('suppliers.store'), [
            'name' => 'CV. Mitra Baru Testing',
            'code' => $code,
            'contact_person' => 'Rudy',
            'phone' => '+62 812-3344-5566',
            'email' => 'rudy@mitrabaru.com',
            'payment_terms' => 'NET 14',
        ]);

        $createResponse->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['code' => $code, 'name' => 'CV. Mitra Baru Testing']);
    }

    public function test_user_can_create_purchase_order(): void
    {
        $uniqueNote = 'PO Uji Inbound ' . uniqid();
        $postData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_delivery_date' => now()->addDays(3)->format('Y-m-d'),
            'payment_status' => 'unpaid',
            'notes' => $uniqueNote,
            'tax_percent' => 11,
            'shipping_cost' => 50000,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity_ordered' => 30,
                    'unit_cost' => 50000,
                    'notes' => 'Batch baru',
                ]
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.store'), $postData);

        $po = PurchaseOrder::where('notes', $uniqueNote)->latest()->first();
        $this->assertNotNull($po);
        $response->assertRedirect(route('purchase-orders.show', $po->id));

        $this->assertEquals(1500000, $po->subtotal); // 30 * 50,000
        $this->assertEquals(165000, $po->tax_amount); // 11% of 1,500,000
        $this->assertEquals(1715000, $po->total_amount); // 1,500,000 + 165,000 + 50,000
        $this->assertEquals('ordered', $po->status);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity_ordered' => 30,
            'quantity_received' => 0,
        ]);
    }

    public function test_user_can_receive_goods_against_po(): void
    {
        $poNumber = 'PO-TEST-RCV-' . uniqid();
        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'ordered',
            'payment_status' => 'unpaid',
            'subtotal' => 1000000,
            'total_amount' => 1000000,
            'created_by' => $this->admin->id,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity_ordered' => 20,
            'quantity_received' => 0,
            'unit_cost' => 50000,
            'subtotal' => 1000000,
        ]);

        $initialStock = $this->product->quantity;

        // Operator performs inbound receiving (Goods Receipt)
        $sjNumber = 'SJ-VENDOR-' . uniqid();
        $receiveData = [
            'vendor_do_number' => $sjNumber,
            'received_date' => now()->format('Y-m-d\TH:i'),
            'notes' => 'Barang diterima lengkap di dock 2',
            'items' => [
                [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $this->product->id,
                    'quantity_received' => 20,
                    'quantity_rejected' => 0,
                    'batch_number' => 'BATCH-TEST-2026',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'shelf_location' => 'Rak B-09',
                ]
            ],
        ];

        $response = $this->actingAs($this->staff)->post(route('goods-receipts.store', $po->id), $receiveData);

        // 1. Verify GoodsReceipt record created
        $grn = GoodsReceipt::where('vendor_do_number', $sjNumber)->first();
        $this->assertNotNull($grn);
        $response->assertRedirect(route('goods-receipts.show', $grn->id));

        // 2. Verify Product stock incremented
        $this->product->refresh();
        $this->assertEquals($initialStock + 20, $this->product->quantity);
        $this->assertEquals('BATCH-TEST-2026', $this->product->batch_number);
        $this->assertEquals('Rak B-09', $this->product->warehouse_location);

        // 3. Verify StockMovement (in) was recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 20,
            'stock_before' => $initialStock,
            'stock_after' => $initialStock + 20,
            'reference_no' => $grn->receipt_number,
        ]);

        // 4. Verify PO Item quantity_received is now 20
        $poItem->refresh();
        $this->assertEquals(20, $poItem->quantity_received);

        // 5. Verify PO status updated to received
        $po->refresh();
        $this->assertEquals('received', $po->status);
    }

    public function test_user_can_cancel_unreceived_po(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-CANCEL-' . uniqid(),
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'ordered',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchase-orders.cancel', $po->id));
        $response->assertRedirect(route('purchase-orders.show', $po->id));

        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
    }

    public function test_printable_po_and_grn_render_successfully(): void
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-PRINT-' . uniqid(),
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'ordered',
            'created_by' => $this->admin->id,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity_ordered' => 10,
            'unit_cost' => 50000,
            'subtotal' => 500000,
        ]);

        $grn = GoodsReceipt::create([
            'receipt_number' => 'GRN-TEST-PRINT-' . uniqid(),
            'purchase_order_id' => $po->id,
            'warehouse_id' => $this->warehouse->id,
            'vendor_do_number' => 'SJ-PRINT-123',
            'received_date' => now(),
            'received_by' => $this->admin->id,
            'status' => 'completed',
        ]);

        // Test Print PO
        $responsePo = $this->actingAs($this->admin)->get(route('purchase-orders.print', $po->id));
        $responsePo->assertStatus(200);
        $responsePo->assertSee('PURCHASE ORDER');
        $responsePo->assertSee($po->po_number);

        // Test Print GRN
        $responseGrn = $this->actingAs($this->admin)->get(route('goods-receipts.print', $grn->id));
        $responseGrn->assertStatus(200);
        $responseGrn->assertSee('BUKTI PENERIMAAN BARANG');
        $responseGrn->assertSee($grn->receipt_number);
    }
}

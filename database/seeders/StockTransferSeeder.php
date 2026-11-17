<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class StockTransferSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $manager = User::where('role', 'warehouse_manager')->first() ?? $admin;
        $staff = User::where('role', 'warehouse_staff')->first() ?? $admin;

        $whCentral = Warehouse::where('code', 'WH-CENTRAL')->first() ?? Warehouse::first();
        $whBlokA = Warehouse::where('code', 'WH-BLOK-A')->first() ?? Warehouse::skip(1)->first();
        $whBlokC = Warehouse::where('code', 'WH-BLOK-C')->first() ?? Warehouse::skip(2)->first();

        if (!$whCentral || !$whBlokA) {
            return;
        }

        // Get products from Central Warehouse
        $productsCentral = Product::where('warehouse_id', $whCentral->id)->take(3)->get();
        if ($productsCentral->count() < 2) {
            // Pick any available products
            $productsCentral = Product::take(2)->get();
        }

        // 1. Transfer Selesai (RECEIVED)
        if (!StockTransfer::where('transfer_number', 'TRF-202611-0001')->exists() && $productsCentral->isNotEmpty()) {
            $item1 = $productsCentral[0];
            $item2 = $productsCentral->count() > 1 ? $productsCentral[1] : $item1;

            $transfer1 = StockTransfer::create([
                'transfer_number' => 'TRF-202611-0001',
                'source_warehouse_id' => $whCentral->id,
                'destination_warehouse_id' => $whBlokA->id,
                'transfer_date' => now()->subDays(5),
                'expected_arrival_date' => now()->subDays(4),
                'received_date' => now()->subDays(4)->addHours(6),
                'status' => 'received',
                'driver_name' => 'Bambang Supriyadi',
                'vehicle_plate_number' => 'B 9283 KXA',
                'expedition' => 'Armada Truk Internal Gudang',
                'tracking_number' => 'INT-TRK-8812',
                'notes' => 'Pemerataan stok musiman menyambut kampanye Payday Sale.',
                'total_items_count' => 2,
                'total_quantity' => 25,
                'total_received_quantity' => 25,
                'created_by' => $manager?->id,
                'dispatched_by' => $manager?->id,
                'dispatched_at' => now()->subDays(5)->addHours(2),
                'received_by' => $staff?->id,
                'created_at' => now()->subDays(5),
            ]);

            StockTransferItem::create([
                'stock_transfer_id' => $transfer1->id,
                'source_product_id' => $item1->id,
                'destination_product_id' => null,
                'sku' => $item1->sku,
                'product_title' => $item1->title,
                'quantity' => 15,
                'received_quantity' => 15,
                'unit' => $item1->unit ?? 'pcs',
                'batch_number' => $item1->batch_number,
                'expiry_date' => $item1->expiry_date,
                'notes' => 'Kondisi kemasan utuh, lolos verifikasi.',
            ]);

            StockTransferItem::create([
                'stock_transfer_id' => $transfer1->id,
                'source_product_id' => $item2->id,
                'destination_product_id' => null,
                'sku' => $item2->sku,
                'product_title' => $item2->title,
                'quantity' => 10,
                'received_quantity' => 10,
                'unit' => $item2->unit ?? 'pcs',
                'batch_number' => $item2->batch_number,
                'expiry_date' => $item2->expiry_date,
                'notes' => 'Sesuai pesanan mutasi.',
            ]);
        }

        // 2. Transfer Sedang Dikirim (IN_TRANSIT)
        if (!StockTransfer::where('transfer_number', 'TRF-202611-0002')->exists() && $productsCentral->isNotEmpty()) {
            $item = $productsCentral->first();

            $transfer2 = StockTransfer::create([
                'transfer_number' => 'TRF-202611-0002',
                'source_warehouse_id' => $whCentral->id,
                'destination_warehouse_id' => $whBlokC ? $whBlokC->id : $whBlokA->id,
                'transfer_date' => now()->subHours(6),
                'expected_arrival_date' => now()->addDay(),
                'status' => 'in_transit',
                'driver_name' => 'Wahyudi Setiawan',
                'vehicle_plate_number' => 'B 4410 PQA',
                'expedition' => 'JNE Trucking (JTR)',
                'tracking_number' => 'JTR-8891029381',
                'notes' => 'Pindahan stok mendesak untuk antisipasi lonjakan pesanan cabang.',
                'total_items_count' => 1,
                'total_quantity' => 12,
                'total_received_quantity' => 0,
                'created_by' => $manager?->id,
                'dispatched_by' => $manager?->id,
                'dispatched_at' => now()->subHours(5),
                'created_at' => now()->subHours(6),
            ]);

            StockTransferItem::create([
                'stock_transfer_id' => $transfer2->id,
                'source_product_id' => $item->id,
                'destination_product_id' => null,
                'sku' => $item->sku,
                'product_title' => $item->title,
                'quantity' => 12,
                'received_quantity' => 0,
                'unit' => $item->unit ?? 'pcs',
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date,
                'notes' => 'Koli 1/1, disegel barcode segel tamper-evident.',
            ]);
        }

        // 3. Transfer Draft Baru (DRAFT)
        if (!StockTransfer::where('transfer_number', 'TRF-202611-0003')->exists() && $productsCentral->isNotEmpty()) {
            $item = $productsCentral->last();

            $transfer3 = StockTransfer::create([
                'transfer_number' => 'TRF-202611-0003',
                'source_warehouse_id' => $whBlokA->id,
                'destination_warehouse_id' => $whCentral->id,
                'transfer_date' => now(),
                'expected_arrival_date' => now()->addDays(2),
                'status' => 'draft',
                'driver_name' => 'Rahmat Hidayat',
                'vehicle_plate_number' => 'D 1892 YAA',
                'expedition' => 'Armada Box Engkel Internal',
                'tracking_number' => null,
                'notes' => 'Konsolidasi retur unit surplus ke Gudang Pusat.',
                'total_items_count' => 1,
                'total_quantity' => 8,
                'total_received_quantity' => 0,
                'created_by' => $staff?->id,
                'created_at' => now(),
            ]);

            StockTransferItem::create([
                'stock_transfer_id' => $transfer3->id,
                'source_product_id' => $item->id,
                'destination_product_id' => null,
                'sku' => $item->sku,
                'product_title' => $item->title,
                'quantity' => 8,
                'received_quantity' => 0,
                'unit' => $item->unit ?? 'pcs',
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date,
                'notes' => 'Menunggu persetujuan dispatch kepala gudang.',
            ]);
        }
    }
}

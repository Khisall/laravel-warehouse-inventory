<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Database\Seeder;

class ProductBatchSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('quantity', '>', 5)->take(5)->get();

        foreach ($products as $product) {
            $totalQty = $product->quantity;
            $qty1 = max(2, (int) round($totalQty * 0.3));
            $qty2 = max(2, (int) round($totalQty * 0.4));
            $qty3 = max(1, $totalQty - $qty1 - $qty2);

            // Batch 1: Expiring Soon (15 - 25 days) - Priority 1 FEFO
            ProductBatch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'LOT-' . $product->id . '-A01'],
                [
                    'warehouse_id' => $product->warehouse_id,
                    'initial_quantity' => $qty1,
                    'current_quantity' => $qty1,
                    'manufacturing_date' => now()->subMonths(6),
                    'expiry_date' => now()->addDays(rand(14, 28)),
                    'unit_cost' => $product->cost_price,
                    'status' => 'active',
                    'location_code' => 'RAK-A-' . rand(1, 9),
                    'notes' => 'Batch prioritas FEFO pertama (segera kedaluwarsa)',
                ]
            );

            // Batch 2: Medium Expiry (120 - 180 days) - Priority 2 FEFO
            ProductBatch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'LOT-' . $product->id . '-B02'],
                [
                    'warehouse_id' => $product->warehouse_id,
                    'initial_quantity' => $qty2,
                    'current_quantity' => $qty2,
                    'manufacturing_date' => now()->subMonths(2),
                    'expiry_date' => now()->addDays(rand(120, 180)),
                    'unit_cost' => $product->cost_price,
                    'status' => 'active',
                    'location_code' => 'RAK-B-' . rand(1, 9),
                    'notes' => 'Batch produksi reguler tahap kedua',
                ]
            );

            // Batch 3: Fresh Batch (300 - 365 days) - Priority 3 FEFO
            ProductBatch::firstOrCreate(
                ['product_id' => $product->id, 'batch_number' => 'LOT-' . $product->id . '-C03'],
                [
                    'warehouse_id' => $product->warehouse_id,
                    'initial_quantity' => $qty3,
                    'current_quantity' => $qty3,
                    'manufacturing_date' => now()->subDays(10),
                    'expiry_date' => now()->addDays(rand(300, 365)),
                    'unit_cost' => $product->cost_price,
                    'status' => 'active',
                    'location_code' => 'RAK-C-' . rand(1, 9),
                    'notes' => 'Batch kedatangan terbaru',
                ]
            );
        }

        // Add 1 Quarantined Batch for demonstration
        if ($firstProduct = Product::first()) {
            ProductBatch::firstOrCreate(
                ['batch_number' => 'LOT-Q-999'],
                [
                    'product_id' => $firstProduct->id,
                    'warehouse_id' => $firstProduct->warehouse_id,
                    'initial_quantity' => 10,
                    'current_quantity' => 10,
                    'manufacturing_date' => now()->subMonth(),
                    'expiry_date' => now()->addMonths(6),
                    'unit_cost' => $firstProduct->cost_price,
                    'status' => 'quarantine',
                    'location_code' => 'ZONE-HOLD-01',
                    'notes' => 'Dalam pemeriksaan lab QC - dilarang pick / kirim',
                ]
            );
        }
    }
}

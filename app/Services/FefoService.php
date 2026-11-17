<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FefoService
{
    /**
     * Generate a FEFO (First Expired, First Out) picking plan for a product.
     */
    public function getFefoPlan(Product $product, int $quantity): array
    {
        $batches = $product->batches()
            ->availableForFefo()
            ->get();

        $totalAvailable = $batches->sum('current_quantity');
        $remainingNeeded = $quantity;
        $plan = [];

        foreach ($batches as $batch) {
            if ($remainingNeeded <= 0) {
                break;
            }

            $pickQty = min($batch->current_quantity, $remainingNeeded);
            $plan[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'expiry_display' => $batch->expiry_date->translatedFormat('d M Y'),
                'days_until_expiry' => $batch->days_until_expiry,
                'expiry_status' => $batch->expiry_status,
                'location_code' => $batch->location_code ?: $product->warehouse_location ?: 'Gudang Utama',
                'current_qty' => $batch->current_quantity,
                'pick_qty' => $pickQty,
                'remaining_after' => $batch->current_quantity - $pickQty,
            ];

            $remainingNeeded -= $pickQty;
        }

        return [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'title' => $product->title,
            'requested_qty' => $quantity,
            'total_batch_stock' => $totalAvailable,
            'can_fulfill' => $totalAvailable >= $quantity || ($batches->isEmpty() && $product->quantity >= $quantity),
            'has_batches' => $batches->isNotEmpty(),
            'shortfall' => max(0, $remainingNeeded),
            'batches_to_pick' => $plan,
        ];
    }

    /**
     * Deduct stock using the FEFO picking algorithm.
     * Decrements the earliest expiring batches first and records stock movements.
     */
    public function deductFefo(
        Product $product,
        int $quantity,
        string $referenceNo,
        string $reason = 'Order Fulfillment',
        ?int $userId = null
    ): array {
        return DB::transaction(function () use ($product, $quantity, $referenceNo, $reason, $userId) {
            $userId = $userId ?? Auth::id() ?? \App\Models\User::value('id');
            $batches = $product->batches()->availableForFefo()->get();
            $pickedBatches = [];

            if ($batches->isNotEmpty()) {
                $remainingToDeduct = $quantity;

                foreach ($batches as $batch) {
                    if ($remainingToDeduct <= 0) {
                        break;
                    }

                    $deductFromThisBatch = min($batch->current_quantity, $remainingToDeduct);
                    $newBatchQty = $batch->current_quantity - $deductFromThisBatch;

                    $batch->update([
                        'current_quantity' => $newBatchQty,
                        'status' => $newBatchQty <= 0 ? 'depleted' : 'active',
                    ]);

                    $pickedBatches[] = [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'picked_quantity' => $deductFromThisBatch,
                        'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                    ];

                    $remainingToDeduct -= $deductFromThisBatch;
                }
            }

            // Decrement the master product quantity
            $stockBefore = $product->quantity;
            $stockAfter = max(0, $stockBefore - $quantity);
            $product->update(['quantity' => $stockAfter]);

            // Log Stock Movement
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'type' => 'out',
                'quantity' => -$quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_no' => $referenceNo,
                'batch_number' => !empty($pickedBatches) ? implode(', ', array_column($pickedBatches, 'batch_number')) : $product->batch_number,
                'expiry_date' => !empty($pickedBatches) ? $pickedBatches[0]['expiry_date'] : $product->expiry_date,
                'notes' => "[FEFO Picking] {$reason} (Ref: {$referenceNo})",
                'created_by' => $userId,
            ]);

            return [
                'product_id' => $product->id,
                'total_deducted' => $quantity,
                'stock_after' => $stockAfter,
                'picked_batches' => $pickedBatches,
            ];
        });
    }
}

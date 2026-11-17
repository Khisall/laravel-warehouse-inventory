<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScannerController extends Controller
{
    /**
     * Mobile Barcode & QR Camera Scanner Interface.
     */
    public function index(): View
    {
        return view('scanner.index');
    }

    /**
     * Lookup scanned code (SKU, QR, Batch Number, or Transfer Number).
     */
    public function apiLookup(Request $request): JsonResponse
    {
        $code = trim($request->input('code', ''));

        if (empty($code)) {
            return response()->json(['error' => 'Kode scan tidak boleh kosong.'], 400);
        }

        // 1. Check if it's a Stock Transfer (TRF-...)
        if (str_starts_with(strtoupper($code), 'TRF-')) {
            $transfer = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'items.sourceProduct'])
                ->where('transfer_number', $code)
                ->first();

            if ($transfer) {
                return response()->json([
                    'type' => 'transfer',
                    'data' => [
                        'id' => $transfer->id,
                        'transfer_number' => $transfer->transfer_number,
                        'status' => $transfer->status,
                        'status_label' => $transfer->status_label,
                        'status_badge_class' => $transfer->status_badge_class,
                        'source_warehouse' => $transfer->sourceWarehouse->name,
                        'destination_warehouse' => $transfer->destinationWarehouse->name,
                        'total_quantity' => $transfer->total_quantity,
                        'total_items_count' => $transfer->total_items_count,
                        'driver_name' => $transfer->driver_name ?: '-',
                        'vehicle_plate_number' => $transfer->vehicle_plate_number ?: '-',
                        'can_receive' => $transfer->canBeReceived(),
                        'show_url' => route('transfers.show', $transfer->id),
                        'surat_jalan_url' => route('transfers.surat-jalan', $transfer->id),
                        'items' => $transfer->items->map(fn($it) => [
                            'id' => $it->id,
                            'sku' => $it->sku,
                            'title' => $it->product_title,
                            'quantity' => $it->quantity,
                            'received_quantity' => $it->received_quantity,
                            'unit' => $it->unit,
                        ]),
                    ]
                ]);
            }
        }

        // 2. Check if it's a Batch Number (LOT-... or BATCH-...)
        $batch = ProductBatch::with(['product.warehouse'])->where('batch_number', $code)->first();
        if ($batch && $batch->product) {
            $product = $batch->product;
            return response()->json([
                'type' => 'product',
                'highlight_batch' => $batch->batch_number,
                'data' => $this->formatProductPayload($product)
            ]);
        }

        // 3. Lookup Product by SKU, ID, or Title
        $product = Product::with(['warehouse', 'category', 'batches' => function ($q) {
            $q->orderBy('expiry_date', 'asc');
        }])
        ->where('sku', $code)
        ->orWhere('title', 'like', "%{$code}%")
        ->first();

        if ($product) {
            return response()->json([
                'type' => 'product',
                'data' => $this->formatProductPayload($product)
            ]);
        }

        return response()->json([
            'error' => "Barcode atau SKU '{$code}' tidak ditemukan di database gudang."
        ], 404);
    }

    /**
     * Quick stock adjustment from scanner.
     */
    public function quickAdjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'delta_quantity' => 'required|integer', // positive or negative
            'reason' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);
            $stockBefore = $product->quantity;
            $stockAfter = max(0, $stockBefore + $validated['delta_quantity']);

            $product->update(['quantity' => $stockAfter]);

            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'type' => $validated['delta_quantity'] > 0 ? 'in' : 'out',
                'quantity' => $validated['delta_quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_no' => 'SCAN-ADJ-' . now()->format('ymdHis'),
                'notes' => "[Scanner Mobile Quick Adjust] " . $validated['reason'],
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Stok {$product->title} berhasil disesuaikan ({$stockBefore} -> {$stockAfter}).",
                'new_quantity' => $stockAfter,
            ]);
        });
    }

    /**
     * Format product payload for mobile scanner card.
     */
    protected function formatProductPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'title' => $product->title,
            'warehouse' => $product->warehouse?->name ?? 'Gudang Utama',
            'warehouse_location' => $product->warehouse_location ?? 'Rak A-01',
            'quantity' => $product->quantity,
            'min_stock' => $product->min_stock,
            'unit' => $product->unit ?? 'pcs',
            'cost_price' => $product->cost_price,
            'price' => $product->price,
            'status' => $product->status,
            'expiry_status' => $product->expiry_status,
            'days_until_expiry' => $product->days_until_expiry,
            'image_url' => $product->image_url,
            'batches' => $product->batches->map(fn($b) => [
                'batch_number' => $b->batch_number,
                'current_quantity' => $b->current_quantity,
                'expiry_date' => $b->expiry_date->format('d M Y'),
                'days_until_expiry' => $b->days_until_expiry,
                'expiry_status' => $b->expiry_status,
                'status' => $b->status,
                'status_label' => $b->status_label,
                'location_code' => $b->location_code,
            ]),
        ];
    }
}

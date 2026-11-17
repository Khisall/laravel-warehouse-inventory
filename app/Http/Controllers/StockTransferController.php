<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    /**
     * Display a listing of stock transfers with KPI counters & filters.
     */
    public function index(Request $request): View
    {
        $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'creator', 'dispatcher'])
            ->latest('id');

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Source Warehouse Filter
        if ($request->filled('source_warehouse_id')) {
            $query->where('source_warehouse_id', $request->source_warehouse_id);
        }

        // Destination Warehouse Filter
        if ($request->filled('destination_warehouse_id')) {
            $query->where('destination_warehouse_id', $request->destination_warehouse_id);
        }

        // Search Filter (Transfer No, Driver, Expedition, Plate No, Tracking No)
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('transfer_number', 'like', "%{$q}%")
                    ->orWhere('driver_name', 'like', "%{$q}%")
                    ->orWhere('vehicle_plate_number', 'like', "%{$q}%")
                    ->orWhere('expedition', 'like', "%{$q}%")
                    ->orWhere('tracking_number', 'like', "%{$q}%");
            });
        }

        // Date Range Filter
        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->paginate(12)->withQueryString();

        // Summary KPI Metrics
        $stats = [
            'total' => StockTransfer::count(),
            'in_transit' => StockTransfer::where('status', 'in_transit')->count(),
            'received' => StockTransfer::where('status', 'received')->count(),
            'draft' => StockTransfer::where('status', 'draft')->count(),
        ];

        $warehouses = Warehouse::orderBy('name')->get();

        return view('transfers.index', compact('transfers', 'stats', 'warehouses'));
    }

    /**
     * Show the form for creating a new stock transfer.
     */
    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        return view('transfers.create', compact('warehouses'));
    }

    /**
     * Store a newly created stock transfer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id|different:destination_warehouse_id',
            'destination_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'expected_arrival_date' => 'nullable|date|after_or_equal:transfer_date',
            'driver_name' => 'nullable|string|max:100',
            'vehicle_plate_number' => 'nullable|string|max:30',
            'expedition' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'action' => 'nullable|string|in:draft,dispatch',
        ], [
            'source_warehouse_id.different' => 'Gudang asal dan gudang tujuan tidak boleh sama.',
            'items.required' => 'Minimal satu barang harus ditambahkan untuk proses mutasi stok.',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Auto generate Transfer Number (e.g. TRF-202611-0004)
            $prefix = 'TRF-' . now()->format('Ym') . '-';
            $lastTransfer = StockTransfer::where('transfer_number', 'like', "{$prefix}%")
                ->latest('id')
                ->first();

            $nextSeq = 1;
            if ($lastTransfer) {
                $lastNum = (int) substr($lastTransfer->transfer_number, -4);
                $nextSeq = $lastNum + 1;
            }
            $transferNumber = $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_warehouse_id' => $validated['destination_warehouse_id'],
                'transfer_date' => $validated['transfer_date'],
                'expected_arrival_date' => $validated['expected_arrival_date'] ?? null,
                'status' => 'draft',
                'driver_name' => $validated['driver_name'] ?? null,
                'vehicle_plate_number' => $validated['vehicle_plate_number'] ?? null,
                'expedition' => $validated['expedition'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_items_count' => count($validated['items']),
                'total_quantity' => 0,
                'total_received_quantity' => 0,
                'created_by' => Auth::id(),
            ]);

            $totalQty = 0;

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                // Ensure item belongs to source warehouse
                if ($product->warehouse_id != $validated['source_warehouse_id']) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => "Produk {$product->title} ({$product->sku}) bukan milik gudang asal yang dipilih.",
                    ]);
                }

                $qty = (int) $itemData['quantity'];

                // Validate stock availability
                if ($product->quantity < $qty) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'items' => "Stok produk {$product->title} ({$product->sku}) tidak mencukupi. Tersedia: {$product->quantity}, diminta: {$qty}.",
                    ]);
                }

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'source_product_id' => $product->id,
                    'destination_product_id' => null,
                    'sku' => $product->sku,
                    'product_title' => $product->title,
                    'quantity' => $qty,
                    'received_quantity' => 0,
                    'unit' => $product->unit ?? 'pcs',
                    'batch_number' => $product->batch_number,
                    'expiry_date' => $product->expiry_date,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalQty += $qty;
            }

            $transfer->update([
                'total_quantity' => $totalQty,
            ]);

            // If action is dispatch and user has manager/admin role, dispatch immediately
            if ($request->input('action') === 'dispatch') {
                $user = Auth::user();
                if ($user && in_array($user->role, ['super_admin', 'warehouse_manager'])) {
                    $this->executeDispatch($transfer);
                    return redirect()->route('transfers.show', $transfer->id)
                        ->with('success', "Transfer {$transfer->transfer_number} berhasil dibuat dan langsung diberangkatkan (In-Transit).");
                }
            }

            return redirect()->route('transfers.show', $transfer->id)
                ->with('success', "Draft pengajuan transfer {$transfer->transfer_number} berhasil disimpan.");
        });
    }

    /**
     * Display the specified stock transfer.
     */
    public function show(StockTransfer $transfer): View
    {
        $transfer->load([
            'sourceWarehouse',
            'destinationWarehouse',
            'items.sourceProduct',
            'items.destinationProduct',
            'creator',
            'dispatcher',
            'receiver',
        ]);

        return view('transfers.show', compact('transfer'));
    }

    /**
     * Dispatch the transfer (Draft -> In Transit).
     * Deducts stock from source warehouse and records StockMovement.
     */
    public function dispatchTransfer(Request $request, StockTransfer $transfer): RedirectResponse
    {
        if (!$transfer->canBeDispatched()) {
            return redirect()->back()->with('error', 'Transfer ini tidak dalam status draft sehingga tidak dapat dikirim.');
        }

        DB::transaction(function () use ($transfer) {
            $this->executeDispatch($transfer);
        });

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Barang transfer {$transfer->transfer_number} berhasil diberangkatkan! Stok gudang asal telah dipotong.");
    }

    /**
     * Internal helper to execute dispatch logic.
     */
    protected function executeDispatch(StockTransfer $transfer): void
    {
        $transfer->load(['items.sourceProduct', 'destinationWarehouse', 'sourceWarehouse']);

        foreach ($transfer->items as $item) {
            $product = $item->sourceProduct;

            // Re-verify stock
            if ($product->quantity < $item->quantity) {
                throw new \Exception("Stok {$product->title} ({$product->sku}) di gudang asal tidak mencukupi untuk dikirim.");
            }

            $stockBefore = $product->quantity;
            $stockAfter = $stockBefore - $item->quantity;

            $product->update(['quantity' => $stockAfter]);

            // Record StockMovement Out
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $transfer->source_warehouse_id,
                'type' => 'transfer_out',
                'quantity' => -$item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_no' => $transfer->transfer_number,
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date,
                'notes' => "Mutasi keluar ke {$transfer->destinationWarehouse->name} (No: {$transfer->transfer_number})",
                'created_by' => Auth::id(),
            ]);
        }

        $transfer->update([
            'status' => 'in_transit',
            'dispatched_by' => Auth::id(),
            'dispatched_at' => now(),
        ]);
    }

    /**
     * Receive goods at destination warehouse (In Transit -> Received).
     * Increments stock at destination warehouse and records StockMovement.
     */
    public function receiveTransfer(Request $request, StockTransfer $transfer): RedirectResponse
    {
        if (!$transfer->canBeReceived()) {
            return redirect()->back()->with('error', 'Transfer ini tidak dalam status Dalam Pengiriman (In-Transit).');
        }

        $validated = $request->validate([
            'received_quantities' => 'required|array',
            'received_quantities.*' => 'required|integer|min:0',
            'item_notes' => 'nullable|array',
            'item_notes.*' => 'nullable|string|max:255',
            'receiving_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($transfer, $validated) {
            $transfer->load(['items.sourceProduct', 'destinationWarehouse', 'sourceWarehouse']);

            $totalReceived = 0;

            foreach ($transfer->items as $item) {
                $receivedQty = (int) ($validated['received_quantities'][$item->id] ?? $item->quantity);
                $note = $validated['item_notes'][$item->id] ?? null;

                $sourceProduct = $item->sourceProduct;

                // Find or create product in destination warehouse
                $destProduct = Product::where('warehouse_id', $transfer->destination_warehouse_id)
                    ->where(function ($q) use ($sourceProduct) {
                        $q->where('title', $sourceProduct->title)
                          ->orWhere('sku', $sourceProduct->sku);
                    })
                    ->first();

                if (!$destProduct) {
                    // Create new Product record in destination warehouse
                    $baseSku = $sourceProduct->sku;
                    $destCode = $transfer->destinationWarehouse->code ?: 'WH';
                    $newSku = "{$baseSku}-{$destCode}";

                    if (Product::where('sku', $newSku)->exists()) {
                        $newSku = "{$baseSku}-{$destCode}-" . rand(100, 999);
                    }

                    $destProduct = Product::create([
                        'sku' => $newSku,
                        'title' => $sourceProduct->title,
                        'category_id' => $sourceProduct->category_id,
                        'warehouse_id' => $transfer->destination_warehouse_id,
                        'warehouse_location' => $sourceProduct->warehouse_location,
                        'quantity' => $receivedQty,
                        'min_stock' => $sourceProduct->min_stock,
                        'unit' => $sourceProduct->unit ?? 'pcs',
                        'cost_price' => $sourceProduct->cost_price,
                        'price' => $sourceProduct->price,
                        'image' => $sourceProduct->image,
                        'batch_number' => $item->batch_number ?: $sourceProduct->batch_number,
                        'expiry_date' => $item->expiry_date ?: $sourceProduct->expiry_date,
                        'status' => $receivedQty > 0 ? 'in_stock' : 'out_of_stock',
                    ]);

                    $stockBefore = 0;
                    $stockAfter = $receivedQty;
                } else {
                    $stockBefore = $destProduct->quantity;
                    $stockAfter = $stockBefore + $receivedQty;
                    $destProduct->update(['quantity' => $stockAfter]);
                }

                $item->update([
                    'destination_product_id' => $destProduct->id,
                    'received_quantity' => $receivedQty,
                    'notes' => $note,
                ]);

                $totalReceived += $receivedQty;

                // Record StockMovement In at destination warehouse
                if ($receivedQty > 0) {
                    StockMovement::create([
                        'product_id' => $destProduct->id,
                        'warehouse_id' => $transfer->destination_warehouse_id,
                        'type' => 'transfer_in',
                        'quantity' => $receivedQty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_no' => $transfer->transfer_number,
                        'batch_number' => $item->batch_number,
                        'expiry_date' => $item->expiry_date,
                        'notes' => "Mutasi masuk dari {$transfer->sourceWarehouse->name} (No: {$transfer->transfer_number})",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $currentNotes = $transfer->notes;
            if (!empty($validated['receiving_notes'])) {
                $currentNotes = ($currentNotes ? $currentNotes . "\n" : '') . "[Catatan Penerimaan]: " . $validated['receiving_notes'];
            }

            $transfer->update([
                'status' => 'received',
                'received_by' => Auth::id(),
                'received_date' => now(),
                'total_received_quantity' => $totalReceived,
                'notes' => $currentNotes,
            ]);
        });

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer {$transfer->transfer_number} telah berhasil diterima & diverifikasi di gudang tujuan.");
    }

    /**
     * Cancel a transfer.
     * If transfer was in transit, returns stock to source warehouse.
     */
    public function cancelTransfer(StockTransfer $transfer): RedirectResponse
    {
        if (!$transfer->canBeCancelled()) {
            return redirect()->back()->with('error', 'Hanya transfer dengan status Draft atau Dalam Pengiriman yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($transfer) {
            $transfer->load(['items.sourceProduct', 'sourceWarehouse']);

            // If it was already dispatched, return goods to source warehouse
            if ($transfer->status === 'in_transit') {
                foreach ($transfer->items as $item) {
                    $product = $item->sourceProduct;
                    $stockBefore = $product->quantity;
                    $stockAfter = $stockBefore + $item->quantity;

                    $product->update(['quantity' => $stockAfter]);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $transfer->source_warehouse_id,
                        'type' => 'transfer_cancelled',
                        'quantity' => $item->quantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_no' => $transfer->transfer_number,
                        'notes' => "Pengembalian stok pembatalan transfer {$transfer->transfer_number}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            $transfer->update([
                'status' => 'cancelled',
            ]);
        });

        return redirect()->route('transfers.show', $transfer->id)
            ->with('success', "Transfer {$transfer->transfer_number} telah dibatalkan.");
    }

    /**
     * Print official Surat Jalan Transfer Antar Gudang.
     */
    public function printSuratJalan(StockTransfer $transfer): View
    {
        $transfer->load([
            'sourceWarehouse',
            'destinationWarehouse',
            'items.sourceProduct',
            'creator',
            'dispatcher',
            'receiver',
        ]);

        return view('transfers.surat_jalan', compact('transfer'));
    }

    /**
     * JSON API to fetch products belonging to a given warehouse with available stock.
     */
    public function productsByWarehouse(int $warehouseId): JsonResponse
    {
        $products = Product::where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->select('id', 'sku', 'title', 'quantity', 'unit', 'warehouse_location', 'batch_number', 'expiry_date')
            ->orderBy('title')
            ->get();

        return response()->json($products);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    /**
     * Display list of Stock Opname sessions.
     */
    public function index(Request $request)
    {
        $query = StockOpname::with(['warehouse', 'category', 'creator', 'completedByUser'])
            ->withCount('items')
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('opname_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $opnames = $query->paginate(12)->withQueryString();

        // Summary statistics
        $totalSessions = StockOpname::count();
        $inProgressSessions = StockOpname::where('status', 'in_progress')->count();
        $completedSessions = StockOpname::where('status', 'completed')->count();
        $totalVarianceValue = StockOpname::where('status', 'completed')->sum('total_variance_value');

        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('opname.index', compact(
            'opnames',
            'totalSessions',
            'inProgressSessions',
            'completedSessions',
            'totalVarianceValue',
            'warehouses',
            'categories'
        ));
    }

    /**
     * Create and start a new Stock Opname session.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:categories,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Generate unique Opname Number: OPN-YYYYMMDD-XXXX
        $datePrefix = 'OPN-' . date('Ymd');
        $countToday = StockOpname::whereDate('created_at', Carbon::today())->count() + 1;
        $opnameNumber = $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        // Fetch products matching the scope
        $productQuery = Product::query();
        if (!empty($validated['warehouse_id'])) {
            $productQuery->where('warehouse_id', $validated['warehouse_id']);
        }
        if (!empty($validated['category_id'])) {
            $productQuery->where('category_id', $validated['category_id']);
        }

        $products = $productQuery->get();

        if ($products->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat sesi: Tidak ada produk yang ditemukan dalam cakupan gudang/kategori yang dipilih.');
        }

        DB::beginTransaction();
        try {
            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'title' => $validated['title'],
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'status' => 'in_progress',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'total_items' => $products->count(),
                'matched_items' => 0,
                'variance_items' => 0,
                'total_variance_value' => 0,
            ]);

            $now = now();
            $itemsData = [];
            foreach ($products as $prod) {
                $itemsData[] = [
                    'stock_opname_id' => $opname->id,
                    'product_id' => $prod->id,
                    'system_stock' => $prod->quantity,
                    'physical_stock' => 0,
                    'variance' => -$prod->quantity,
                    'cost_price' => $prod->cost_price ?? 0,
                    'variance_value' => (-$prod->quantity) * ($prod->cost_price ?? 0),
                    'is_counted' => false,
                    'notes' => null,
                    'scanned_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            StockOpnameItem::insert($itemsData);
            DB::commit();

            return redirect()->route('opname.show', $opname->id)
                ->with('success', "Sesi Stock Opname ({$opname->opname_number}) berhasil dibuat dengan {$products->count()} barang!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat sesi Stock Opname: ' . $e->getMessage());
        }
    }

    /**
     * Show Stock Opname counting & audit room.
     */
    public function show(Request $request, StockOpname $opname)
    {
        $opname->load(['warehouse', 'category', 'creator', 'completedByUser']);

        $itemQuery = $opname->items()->with(['product.category', 'product.warehouse']);

        // Search in items
        if ($request->filled('search')) {
            $search = trim($request->search);
            $itemQuery->whereHas('product', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('warehouse_location', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        // Filter state: all, counted, uncounted, variance, matched
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'counted':
                    $itemQuery->where('is_counted', true);
                    break;
                case 'uncounted':
                    $itemQuery->where('is_counted', false);
                    break;
                case 'variance':
                    $itemQuery->where('is_counted', true)->where('variance', '!=', 0);
                    break;
                case 'matched':
                    $itemQuery->where('is_counted', true)->where('variance', 0);
                    break;
            }
        }

        $items = $itemQuery->orderBy('is_counted', 'asc')
                           ->orderBy('variance', 'asc')
                           ->paginate(25)
                           ->withQueryString();

        // Realtime stats across all items of this session
        $allItems = $opname->items()->get();
        $totalCounted = $allItems->where('is_counted', true)->count();
        $totalUncounted = $allItems->where('is_counted', false)->count();
        $countedItems = $allItems->where('is_counted', true);
        $matchedCount = $countedItems->where('variance', 0)->count();
        $varianceCount = $countedItems->where('variance', '!=', 0)->count();
        $netVarianceUnits = $countedItems->sum('variance');
        $netVarianceValue = $countedItems->sum('variance_value');

        return view('opname.show', compact(
            'opname',
            'items',
            'totalCounted',
            'totalUncounted',
            'matchedCount',
            'varianceCount',
            'netVarianceUnits',
            'netVarianceValue'
        ));
    }

    /**
     * Handle Barcode Scan event (USB Scanner or Camera) via AJAX.
     */
    public function scanBarcode(Request $request, StockOpname $opname)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        if ($opname->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Stock Opname ini tidak aktif (status: ' . $opname->status_label . ').',
            ], 422);
        }

        $code = trim($request->barcode);

        // Find product by SKU
        $product = Product::where('sku', $code)->first();
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Barang dengan SKU/Barcode '{$code}' tidak ditemukan di sistem!",
            ], 404);
        }

        // Find item in this opname session
        $item = $opname->items()->where('product_id', $product->id)->first();
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => "Barang '{$product->title}' ({$code}) tidak termasuk dalam cakupan audit sesi ini!",
            ], 404);
        }

        // Increment physical count by 1
        $newCount = $item->is_counted ? ($item->physical_stock + 1) : 1;
        $item->recordCount($newCount);

        return response()->json([
            'success' => true,
            'message' => "✓ {$product->title} ({$code}) tercatat: {$item->physical_stock} {$product->unit}",
            'item' => [
                'id' => $item->id,
                'sku' => $product->sku,
                'title' => $product->title,
                'unit' => $product->unit,
                'system_stock' => $item->system_stock,
                'physical_stock' => $item->physical_stock,
                'variance' => $item->variance,
                'variance_value' => $item->variance_value,
                'is_counted' => $item->is_counted,
            ],
            'summary' => [
                'total_items' => $opname->total_items,
                'matched_items' => $opname->matched_items,
                'variance_items' => $opname->variance_items,
                'total_variance_value' => $opname->total_variance_value,
            ]
        ]);
    }

    /**
     * Update individual item physical stock or note.
     */
    public function updateItem(Request $request, StockOpname $opname, StockOpnameItem $item)
    {
        if ($opname->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Sesi ini sudah ditutup dan tidak dapat diedit.');
        }

        $validated = $request->validate([
            'physical_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $item->recordCount($validated['physical_stock'], $validated['notes'] ?? null);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Stok fisik berhasil diperbarui!',
                'item' => $item,
            ]);
        }

        return redirect()->back()->with('success', "Hitungan fisik untuk {$item->product->sku} berhasil disimpan.");
    }

    /**
     * Quick action: Autofill all uncounted items with system stock (Sesuai Sistem).
     */
    public function quickFillAll(StockOpname $opname)
    {
        if ($opname->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Sesi ini tidak dalam status berjalan.');
        }

        $uncounted = $opname->items()->where('is_counted', false)->get();
        foreach ($uncounted as $item) {
            $item->recordCount($item->system_stock, 'Diverifikasi sesuai stok sistem otomatis');
        }

        return redirect()->back()->with('success', "Berhasil menyamakan {$uncounted->count()} barang dengan stok sistem!");
    }

    /**
     * Approve and Reconcile Stock Opname (Updates real product stock & writes movements).
     */
    public function reconcile(StockOpname $opname)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isManager()) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Supervisor / Kepala Gudang / Super Admin yang dapat merekonsiliasi stok.');
        }

        if ($opname->status !== 'in_progress') {
            return redirect()->back()->with('error', 'Hanya sesi yang sedang berjalan yang dapat direkonsiliasi.');
        }

        $itemsWithVariance = $opname->items()
            ->where('is_counted', true)
            ->where('variance', '!=', 0)
            ->with('product')
            ->get();

        DB::beginTransaction();
        try {
            $adjustmentsCount = 0;

            foreach ($itemsWithVariance as $item) {
                $product = $item->product;
                if (!$product) continue;

                $oldStock = $product->quantity;
                $newStock = $item->physical_stock;

                // Update real product quantity
                $product->quantity = $newStock;
                $product->last_modified = now();
                $product->save();

                // Create stock movement record
                StockMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $product->warehouse_id,
                    'type' => 'adjustment',
                    'quantity' => $item->variance,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'reference_no' => $opname->opname_number,
                    'batch_number' => $product->batch_number,
                    'expiry_date' => $product->expiry_date,
                    'notes' => "Rekonsiliasi Stock Opname [{$opname->opname_number}]: " . ($item->notes ?: ($item->variance > 0 ? "Surplus fisik +{$item->variance}" : "Defisit fisik {$item->variance}")),
                    'created_by' => auth()->user()->name,
                ]);

                $adjustmentsCount++;
            }

            // Mark opname as completed
            $opname->status = 'completed';
            $opname->completed_by = auth()->id();
            $opname->completed_at = now();
            $opname->save();
            $opname->recalculateSummary();

            DB::commit();

            return redirect()->route('opname.show', $opname->id)
                ->with('success', "✓ Stock Opname ({$opname->opname_number}) berhasil direkonsiliasi! {$adjustmentsCount} penyesuaian stok telah dicatat ke kartu stok.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal merekonsiliasi: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an in-progress Stock Opname session.
     */
    public function cancel(StockOpname $opname)
    {
        if ($opname->status === 'completed') {
            return redirect()->back()->with('error', 'Sesi yang sudah selesai tidak dapat dibatalkan.');
        }

        $opname->update([
            'status' => 'cancelled',
            'notes' => ($opname->notes ? $opname->notes . ' | ' : '') . 'Dibatalkan oleh ' . auth()->user()->name . ' pada ' . now()->format('d/m/Y H:i'),
        ]);

        return redirect()->route('opname.index')
            ->with('success', "Sesi Stock Opname ({$opname->opname_number}) berhasil dibatalkan.");
    }

    /**
     * Printable Berita Acara Stock Opname.
     */
    public function report(StockOpname $opname)
    {
        $opname->load(['warehouse', 'category', 'creator', 'completedByUser', 'items.product.category', 'items.product.warehouse']);

        $items = $opname->items()->orderBy('variance', 'asc')->get();

        return view('opname.report', compact('opname', 'items'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display the Inventory Alert Center (Low-Stock & Expiry Monitoring).
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'stock'); // 'stock' or 'expiry'

        // 1. Global KPI Metrics
        $outOfStockCount = Product::outOfStock()->count();
        $lowStockCount = Product::lowStock()->count();
        $totalNeedsReorder = Product::needsReorder()->count();

        $expiredCount = Product::expired()->count();
        $expiringSoonCount = Product::expiringSoon(30)->count();
        $expiringWarningCount = Product::expiringWarning(31, 90)->count();

        // Financial impacts (Role protected in view)
        $restockItems = Product::needsReorder()->get();
        $restockValueRequired = $restockItems->sum(function ($p) {
            return $p->suggested_reorder_qty * ($p->cost_price ?? 0);
        });

        $expiredItems = Product::expired()->get();
        $expiredStockValue = $expiredItems->sum(function ($p) {
            return $p->quantity * ($p->cost_price ?? 0);
        });

        $expiringSoonItems = Product::expiringSoon(30)->get();
        $expiringSoonValue = $expiringSoonItems->sum(function ($p) {
            return $p->quantity * ($p->cost_price ?? 0);
        });

        // 2. Tab Data Query
        if ($tab === 'expiry') {
            $query = Product::whereNotNull('expiry_date')
                ->with(['category', 'warehouse']);

            if ($request->filled('expiry_window')) {
                switch ($request->expiry_window) {
                    case 'expired':
                        $query->expired();
                        break;
                    case '30_days':
                        $query->expiringSoon(30);
                        break;
                    case '60_days':
                        $query->where('expiry_date', '>=', now()->startOfDay())
                              ->where('expiry_date', '<=', now()->addDays(60)->endOfDay());
                        break;
                    case '90_days':
                        $query->where('expiry_date', '>=', now()->startOfDay())
                              ->where('expiry_date', '<=', now()->addDays(90)->endOfDay());
                        break;
                }
            }

            // FEFO sorting: nearest expiry first
            $query->orderBy('expiry_date', 'asc');
        } else {
            // Default: 'stock' tab
            $query = Product::with(['category', 'warehouse']);

            if ($request->filled('severity')) {
                if ($request->severity === 'out_of_stock') {
                    $query->outOfStock();
                } elseif ($request->severity === 'low_stock') {
                    $query->lowStock();
                } else {
                    $query->needsReorder();
                }
            } else {
                $query->needsReorder();
            }

            $query->orderBy('quantity', 'asc');
        }

        // Common filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhere('warehouse_location', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(15)->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('alerts.index', compact(
            'tab',
            'products',
            'outOfStockCount',
            'lowStockCount',
            'totalNeedsReorder',
            'expiredCount',
            'expiringSoonCount',
            'expiringWarningCount',
            'restockValueRequired',
            'expiredStockValue',
            'expiringSoonValue',
            'warehouses',
            'categories',
            'suppliers'
        ));
    }

    /**
     * JSON summary API for header notification badge and live polling.
     */
    public function summaryApi()
    {
        $outOfStock = Product::outOfStock()->count();
        $lowStock = Product::lowStock()->count();
        $expired = Product::expired()->count();
        $expiringSoon = Product::expiringSoon(30)->count();

        $totalAlerts = $outOfStock + $lowStock + $expired + $expiringSoon;

        $urgentStock = Product::needsReorder()
            ->take(4)
            ->get(['id', 'sku', 'title', 'quantity', 'min_stock', 'unit']);

        $urgentExpiry = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->take(4)
            ->get(['id', 'sku', 'title', 'quantity', 'unit', 'batch_number', 'expiry_date']);

        return response()->json([
            'total_alerts' => $totalAlerts,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
            'expired' => $expired,
            'expiring_soon' => $expiringSoon,
            'urgent_stock' => $urgentStock,
            'urgent_expiry' => $urgentExpiry,
        ]);
    }

    /**
     * Quarantine / Write-Off expired or damaged stock.
     */
    public function writeOffExpired(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . max(1, $product->quantity),
            'reason' => 'required|string|max:255',
        ]);

        $oldQty = $product->quantity;
        $writeOffQty = (int) $validated['quantity'];
        $newQty = max(0, $oldQty - $writeOffQty);

        $product->quantity = $newQty;
        $product->last_modified = now();
        $product->save();

        // Create StockMovement record
        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $product->warehouse_id,
            'type' => 'out',
            'quantity' => $writeOffQty,
            'stock_before' => $oldQty,
            'stock_after' => $newQty,
            'reference_no' => 'EXP-WO-' . date('Ymd') . '-' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
            'batch_number' => $product->batch_number,
            'expiry_date' => $product->expiry_date,
            'notes' => "Karantina/Pemusnahan Barang Kadaluarsa: " . $validated['reason'],
            'created_by' => auth()->user()->name,
        ]);

        return redirect()->back()->with('success', "Stok kadaluarsa ({$product->sku}) sebanyak {$writeOffQty} {$product->unit} berhasil dikarantina dan dikeluarkan dari inventaris aktif.");
    }
}

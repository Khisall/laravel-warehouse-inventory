<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\FefoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductBatchController extends Controller
{
    protected FefoService $fefoService;

    public function __construct(FefoService $fefoService)
    {
        $this->fefoService = $fefoService;
    }

    /**
     * Display a listing of product batches with FEFO status and filters.
     */
    public function index(Request $request): View
    {
        $query = ProductBatch::with(['product', 'warehouse'])
            ->orderBy('expiry_date', 'asc');

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'expiring_soon') {
                $query->where('status', 'active')
                      ->where('current_quantity', '>', 0)
                      ->whereDate('expiry_date', '>=', now())
                      ->whereDate('expiry_date', '<=', now()->addDays(30));
            } else {
                $query->where('status', $request->status);
            }
        }

        // Product Filter
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Warehouse Filter
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search Query (Batch Number, SKU, Product Title, Location)
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('batch_number', 'like', "%{$q}%")
                    ->orWhere('location_code', 'like', "%{$q}%")
                    ->orWhereHas('product', function ($p) use ($q) {
                        $p->where('sku', 'like', "%{$q}%")
                          ->orWhere('title', 'like', "%{$q}%");
                    });
            });
        }

        $batches = $query->paginate(15)->withQueryString();

        // KPI Counters
        $stats = [
            'total' => ProductBatch::count(),
            'active' => ProductBatch::where('status', 'active')->count(),
            'expiring_soon' => ProductBatch::where('status', 'active')
                ->where('current_quantity', '>', 0)
                ->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->count(),
            'quarantine' => ProductBatch::where('status', 'quarantine')->count(),
        ];

        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('title')->get();

        return view('batches.index', compact('batches', 'stats', 'warehouses', 'products'));
    }

    /**
     * Store a newly created batch in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'batch_number' => 'required|string|max:100',
            'initial_quantity' => 'required|integer|min:1',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'required|date|after_or_equal:today',
            'location_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);
            $qty = (int) $validated['initial_quantity'];

            ProductBatch::create([
                'product_id' => $product->id,
                'warehouse_id' => $validated['warehouse_id'],
                'batch_number' => $validated['batch_number'],
                'initial_quantity' => $qty,
                'current_quantity' => $qty,
                'manufacturing_date' => $validated['manufacturing_date'] ?? null,
                'expiry_date' => $validated['expiry_date'],
                'unit_cost' => $product->cost_price,
                'status' => 'active',
                'location_code' => $validated['location_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Increment master product quantity
            $stockBefore = $product->quantity;
            $stockAfter = $stockBefore + $qty;
            $product->update(['quantity' => $stockAfter]);

            // Log Stock Movement
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $validated['warehouse_id'],
                'type' => 'in',
                'quantity' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_no' => $validated['batch_number'],
                'batch_number' => $validated['batch_number'],
                'expiry_date' => $validated['expiry_date'],
                'notes' => "Penerimaan stok batch baru: {$validated['batch_number']}",
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('batches.index')
            ->with('success', "Batch baru {$validated['batch_number']} berhasil ditambahkan ke inventaris.");
    }

    /**
     * Toggle quarantine status for a batch.
     */
    public function toggleQuarantine(ProductBatch $batch): RedirectResponse
    {
        $newStatus = $batch->status === 'quarantine' ? 'active' : 'quarantine';
        $batch->update(['status' => $newStatus]);

        $msg = $newStatus === 'quarantine'
            ? "Batch {$batch->batch_number} berhasil dimasukkan ke Karantina (Hold). Batch ini tidak akan dipilih oleh algoritma FEFO."
            : "Batch {$batch->batch_number} telah dilepas dari karantina dan aktif kembali untuk picking FEFO.";

        return redirect()->back()->with('success', $msg);
    }

    /**
     * JSON API returning FEFO picking plan recommendation for a product & requested quantity.
     */
    public function fefoRecommendation(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan.'], 404);
        }

        $plan = $this->fefoService->getFefoPlan($product, $quantity);

        return response()->json($plan);
    }
}

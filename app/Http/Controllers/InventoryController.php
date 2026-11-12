<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MarketplaceListing;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Display the main Dashboard and Inventory table.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'warehouse']);

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('warehouse_location', 'like', "%{$search}%")
                  ->orWhere('status_code', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Warehouse filter
        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->input('warehouse'));
        }

        // Status code filter (ACCO, 11SE, CACO, etc.)
        if ($request->filled('status_code')) {
            $query->where('status_code', $request->input('status_code'));
        }

        // Stock status filter (in_stock, low_stock, out_of_stock)
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['sku', 'title', 'quantity', 'price', 'last_modified', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(15)->withQueryString();

        // Calculate KPI stats
        $totalItems = Product::count();
        $totalStockQty = Product::sum('quantity');
        $totalValuation = Product::select(DB::raw('SUM(quantity * price) as val'))->value('val') ?? 0;
        $lowStockCount = Product::where('status', 'low_stock')->count();
        $outOfStockCount = Product::where('status', 'out_of_stock')->count();
        $marketplaceActive = MarketplaceListing::count();

        $categories = Category::withCount('products')->get();
        $warehouses = Warehouse::withCount('products')->get();

        return view('inventory.index', compact(
            'products',
            'totalItems',
            'totalStockQty',
            'totalValuation',
            'lowStockCount',
            'outOfStockCount',
            'marketplaceActive',
            'categories',
            'warehouses'
        ));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'warehouse_location' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'variation_text' => 'nullable|string|max:50',
            'batch_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'variations_count' => 'nullable|integer|min:0',
            'status_code' => 'required|string|max:20',
            'description' => 'nullable|string',
            'is_marketplace_listed' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        } else {
            // Assign a contextual default thumbnail SVG
            $titleLower = strtolower($validated['title']);
            if (str_contains($titleLower, 'jacket') || str_contains($titleLower, 'baju') || str_contains($titleLower, 'coat')) {
                $imagePath = 'images/products/jacket.svg';
            } elseif (str_contains($titleLower, 'boot')) {
                $imagePath = 'images/products/boots.svg';
            } elseif (str_contains($titleLower, 'sock') || str_contains($titleLower, 'kaos')) {
                $imagePath = 'images/products/socks.svg';
            } else {
                $imagePath = 'images/products/shoes.svg';
            }
        }

        $product = Product::create([
            'sku' => strtoupper(trim($validated['sku'])),
            'title' => $validated['title'],
            'category_id' => $validated['category_id'] ?? null,
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'warehouse_location' => $validated['warehouse_location'],
            'quantity' => $validated['quantity'],
            'min_stock' => $validated['min_stock'] ?? 5,
            'unit' => $validated['unit'] ?? 'pcs',
            'cost_price' => $validated['cost_price'] ?? 0,
            'price' => $validated['price'],
            'image' => $imagePath,
            'variation_text' => $validated['variation_text'] ?? null,
            'batch_number' => $validated['batch_number'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'variations_count' => $validated['variations_count'] ?? 0,
            'status_code' => strtoupper($validated['status_code']),
            'is_marketplace_listed' => $request->boolean('is_marketplace_listed'),
            'description' => $validated['description'] ?? null,
            'last_modified' => now(),
        ]);

        // Record stock intake log
        if ($product->quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'type' => 'in',
                'quantity' => $product->quantity,
                'stock_before' => 0,
                'stock_after' => $product->quantity,
                'reference_no' => 'RCV-' . $product->sku . '-' . date('Ymd'),
                'notes' => 'Initial stock registration into warehouse location: ' . $product->warehouse_location,
                'created_by' => 'Warehouse Admin',
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Barang baru (' . $product->sku . ') berhasil ditambahkan ke inventaris!');
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'warehouse_location' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'variation_text' => 'nullable|string|max:50',
            'batch_number' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'variations_count' => 'nullable|integer|min:0',
            'status_code' => 'required|string|max:20',
            'description' => 'nullable|string',
            'is_marketplace_listed' => 'nullable|boolean',
        ]);

        $oldStock = $product->quantity;
        $newStock = $validated['quantity'];

        if ($request->hasFile('image')) {
            if ($product->image && !str_starts_with($product->image, 'images/')) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->sku = strtoupper(trim($validated['sku']));
        $product->title = $validated['title'];
        $product->category_id = $validated['category_id'] ?? null;
        $product->warehouse_id = $validated['warehouse_id'] ?? null;
        $product->warehouse_location = $validated['warehouse_location'];
        $product->quantity = $newStock;
        $product->min_stock = $validated['min_stock'] ?? 5;
        $product->unit = $validated['unit'] ?? 'pcs';
        $product->cost_price = $validated['cost_price'] ?? 0;
        $product->price = $validated['price'];
        $product->variation_text = $validated['variation_text'] ?? null;
        $product->batch_number = $validated['batch_number'] ?? null;
        $product->expiry_date = $validated['expiry_date'] ?? null;
        $product->variations_count = $validated['variations_count'] ?? 0;
        $product->status_code = strtoupper($validated['status_code']);
        $product->is_marketplace_listed = $request->boolean('is_marketplace_listed');
        $product->description = $validated['description'] ?? null;
        $product->last_modified = now();
        $product->save();

        // If stock quantity was modified manually, record adjustment log
        if ($oldStock !== $newStock) {
            $diff = $newStock - $oldStock;
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'type' => $diff > 0 ? 'in' : 'out',
                'quantity' => abs($diff),
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'reference_no' => 'ADJ-' . $product->sku . '-' . date('Ymd'),
                'notes' => 'Manual stock update from edit form (selisih: ' . ($diff > 0 ? '+' : '') . $diff . ')',
                'created_by' => 'Warehouse Admin',
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Data barang (' . $product->sku . ') berhasil diperbarui!');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $sku = $product->sku;
        $product->delete();

        return redirect()->route('inventory.index')->with('success', 'Barang (' . $sku . ') berhasil dihapus dari sistem gudang.');
    }

    /**
     * Quick Stock In / Out / Adjustment action.
     */
    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $oldStock = $product->quantity;
        $amount = $validated['quantity'];
        $type = $validated['type'];

        if ($type === 'in') {
            $newStock = $oldStock + $amount;
        } elseif ($type === 'out') {
            if ($oldStock < $amount) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi! Stok saat ini: ' . $oldStock);
            }
            $newStock = $oldStock - $amount;
        } else {
            // direct adjustment
            $newStock = $amount;
        }

        $product->quantity = $newStock;
        $product->last_modified = now();
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $product->warehouse_id,
            'type' => $type,
            'quantity' => $amount,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'reference_no' => $validated['reference_no'] ?? ('STK-' . strtoupper($type) . '-' . date('YmdHis')),
            'notes' => $validated['notes'] ?? ('Quick stock ' . $type . ' recorded via dashboard action'),
            'created_by' => 'Warehouse Admin',
        ]);

        return redirect()->route('inventory.index')->with('success', 'Mutasi stok barang (' . $product->sku . ') berhasil disimpan! Stok sekarang: ' . $newStock);
    }

    /**
     * Bulk actions on selected items.
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('bulk_action');
        $ids = $request->input('selected_ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Silakan centang minimal satu barang untuk aksi massal.');
        }

        if ($action === 'print_labels') {
            return redirect()->route('inventory.print-bulk-labels', ['ids' => $ids]);
        }

        if ($action === 'delete') {
            Product::whereIn('id', $ids)->delete();
            return redirect()->route('inventory.index')->with('success', count($ids) . ' barang berhasil dihapus sekaligus.');
        }

        if ($action === 'marketplace_list') {
            Product::whereIn('id', $ids)->update(['is_marketplace_listed' => true]);
            return redirect()->route('inventory.index')->with('success', count($ids) . ' barang berhasil didaftarkan ke Marketplace.');
        }

        if ($action === 'mark_acco') {
            Product::whereIn('id', $ids)->update(['status_code' => 'ACCO']);
            return redirect()->route('inventory.index')->with('success', count($ids) . ' status channel diubah menjadi ACCO.');
        }

        if ($action === 'mark_11se') {
            Product::whereIn('id', $ids)->update(['status_code' => '11SE']);
            return redirect()->route('inventory.index')->with('success', count($ids) . ' status channel diubah menjadi 11SE.');
        }

        return redirect()->back()->with('info', 'Aksi massal selesai.');
    }

    /**
     * Export products as CSV.
     */
    public function exportCsv(Request $request)
    {
        $products = Product::with(['category', 'warehouse'])->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="inventory-report-' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'SKU',
                'Title',
                'Category',
                'QTY',
                'Warehouse Location',
                'Price ($)',
                'Cost Price ($)',
                'Total Valuation ($)',
                'Status Code',
                'Stock Status',
                'Marketplace Listed',
                'Last Modified',
            ]);

            foreach ($products as $p) {
                fputcsv($file, [
                    $p->sku,
                    $p->title,
                    $p->category?->name ?? 'Uncategorized',
                    $p->quantity,
                    $p->warehouse_location,
                    number_format($p->price, 2),
                    number_format($p->cost_price, 2),
                    number_format($p->quantity * $p->price, 2),
                    $p->status_code,
                    $p->status,
                    $p->is_marketplace_listed ? 'Yes' : 'No',
                    $p->last_modified ? $p->last_modified->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print barcode / QR code label for a single product.
     */
    public function printLabel(Product $product)
    {
        $products = collect([$product->load(['category', 'warehouse'])]);
        return view('inventory.print-labels', compact('products'));
    }

    /**
     * Print barcode / QR code labels for multiple products or selected sheet.
     */
    public function printBulkLabels(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            $products = Product::whereIn('id', $ids)->with(['category', 'warehouse'])->get();
        } else {
            $products = Product::with(['category', 'warehouse'])->limit(30)->get();
        }

        return view('inventory.print-labels', compact('products'));
    }

    /**
     * Look up product by SKU or Barcode (used by scanner).
     */
    public function lookupSku($sku)
    {
        $sku = trim($sku);
        $product = Product::where('sku', $sku)
            ->orWhere('sku', strtoupper($sku))
            ->orWhere('batch_number', $sku)
            ->with(['category', 'warehouse'])
            ->first();

        if (!$product) {
            return response()->json([
                'found' => false,
                'message' => 'Barang dengan SKU/Barcode "' . $sku . '" tidak ditemukan di database gudang.'
            ], 404);
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'title' => $product->title,
                'category' => $product->category?->name ?? 'Uncategorized',
                'quantity' => $product->quantity,
                'min_stock' => $product->min_stock,
                'unit' => $product->unit,
                'warehouse_location' => $product->warehouse_location,
                'price' => $product->price,
                'formatted_price' => $product->formatted_price,
                'cost_price' => $product->cost_price,
                'status' => $product->status,
                'status_code' => $product->status_code,
                'batch_number' => $product->batch_number ?? '-',
                'expiry_date' => $product->expiry_date ? $product->expiry_date->format('d M Y') : '-',
                'image_url' => $product->image_url,
            ]
        ]);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pQ) use ($search) {
                      $pQ->where('sku', 'like', "%{$search}%")
                         ->orWhere('title', 'like', "%{$search}%");
                  });
            });
        }

        $movements = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('title')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('activity.index', compact('movements', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'type' => 'required|in:in,out,adjustment,transfer',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $oldStock = $product->quantity;
        $qty = $validated['quantity'];

        if ($validated['type'] === 'in') {
            $newStock = $oldStock + $qty;
        } elseif ($validated['type'] === 'out') {
            if ($oldStock < $qty) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi untuk dikeluarkan! Sisa stok: ' . $oldStock);
            }
            $newStock = $oldStock - $qty;
        } else {
            // direct adjustment
            $newStock = $qty;
        }

        $product->quantity = $newStock;
        $product->last_modified = now();
        $product->save();

        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $validated['warehouse_id'] ?? $product->warehouse_id,
            'type' => $validated['type'],
            'quantity' => $qty,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'reference_no' => $validated['reference_no'] ?? ('STK-' . strtoupper($validated['type']) . '-' . date('YmdHis')),
            'notes' => $validated['notes'] ?? 'Stock movement recorded manually',
            'created_by' => 'Warehouse Admin',
        ]);

        return redirect()->route('activity.index')->with('success', 'Aktivitas mutasi barang berhasil dicatat.');
    }
}

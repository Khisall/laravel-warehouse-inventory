<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items.product')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15);
        $products = Product::where('quantity', '>', 0)->get();

        return view('orders.index', compact('orders', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'nullable|email|max:100',
            'customer_phone' => 'nullable|string|max:50',
            'marketplace' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'courier' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->quantity < $validated['quantity']) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi untuk order ini! Stok: ' . $product->quantity);
        }

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $subtotal = $product->price * $validated['quantity'];

        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'marketplace' => $validated['marketplace'],
            'status' => 'processing',
            'shipping_status' => 'packing',
            'courier' => $validated['courier'] ?? 'JNE Regular',
            'total_amount' => $subtotal,
            'shipping_address' => $validated['shipping_address'],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'sku' => $product->sku,
            'quantity' => $validated['quantity'],
            'unit_price' => $product->price,
            'subtotal' => $subtotal,
        ]);

        // Reduce inventory
        $oldStock = $product->quantity;
        $product->decrement('quantity', $validated['quantity']);
        $product->last_modified = now();
        $product->save();

        // Record stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $product->warehouse_id,
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'stock_before' => $oldStock,
            'stock_after' => $product->quantity,
            'reference_no' => $order->order_number,
            'notes' => 'Sales order fulfill for ' . $order->customer_name . ' (' . $order->marketplace . ')',
            'created_by' => 'Order System',
        ]);

        return redirect()->route('orders.index')->with('success', 'Order ' . $order->order_number . ' berhasil dibuat & stok barang otomatis berkurang!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'shipping_status' => 'nullable|string',
            'tracking_number' => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('orders.index')->with('success', 'Status pesanan ' . $order->order_number . ' berhasil diperbarui.');
    }
}

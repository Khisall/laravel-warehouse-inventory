<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing of Purchase Orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator', 'items.product'])
            ->withCount('items')
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $purchaseOrders = $query->paginate(12)->withQueryString();

        // Metrics Summary
        $totalPOs = PurchaseOrder::count();
        $pendingPOs = PurchaseOrder::whereIn('status', ['ordered', 'partial'])->count();
        $completedPOs = PurchaseOrder::where('status', 'received')->count();
        $totalPurchaseValue = PurchaseOrder::whereNotIn('status', ['cancelled'])->sum('total_amount');

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('purchase_orders.index', compact(
            'purchaseOrders',
            'totalPOs',
            'pendingPOs',
            'completedPOs',
            'totalPurchaseValue',
            'suppliers',
            'warehouses'
        ));
    }

    /**
     * Show the form for creating a new Purchase Order.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('title')->get(['id', 'sku', 'title', 'unit', 'cost_price', 'quantity', 'warehouse_location']);

        return view('purchase_orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    /**
     * Store a newly created Purchase Order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'notes' => 'nullable|string|max:500',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:100',
        ]);

        // Auto-generate PO Number: PO-YYYYMMDD-XXXX
        $datePrefix = 'PO-' . date('Ymd');
        $countToday = PurchaseOrder::whereDate('created_at', Carbon::today())->count() + 1;
        $poNumber = $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $shippingCost = (float) ($validated['shipping_cost'] ?? 0);
            $taxPercent = (float) ($validated['tax_percent'] ?? 0);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => 'ordered', // Langsung berstatus 'ordered' agar siap diterima barangnya
                'payment_status' => $validated['payment_status'],
                'subtotal' => 0,
                'tax_amount' => 0,
                'shipping_cost' => $shippingCost,
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity_ordered'];
                $cost = (float) $item['unit_cost'];
                $lineSubtotal = $qty * $cost;
                $subtotal += $lineSubtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_cost' => $cost,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $taxAmount = $subtotal * ($taxPercent / 100);
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            $po->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $po->id)
                ->with('success', "Pesanan Pembelian ({$po->po_number}) berhasil dibuat dan siap menerima kedatangan barang!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Purchase Order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'creator',
            'approver',
            'items.product',
            'goodsReceipts.receiver',
            'goodsReceipts.items.product',
        ]);

        $totalOrdered = $purchaseOrder->items->sum('quantity_ordered');
        $totalReceived = $purchaseOrder->items->sum('quantity_received');
        $remainingTotal = max(0, $totalOrdered - $totalReceived);

        return view('purchase_orders.show', compact('purchaseOrder', 'totalOrdered', 'totalReceived', 'remainingTotal'));
    }

    /**
     * Cancel an uncompleted Purchase Order.
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return redirect()->back()->with('error', 'PO yang sudah diterima lengkap tidak dapat dibatalkan.');
        }

        if ($purchaseOrder->items()->where('quantity_received', '>', 0)->exists()) {
            return redirect()->back()->with('error', 'PO tidak dapat dibatalkan karena sebagian barang sudah diterima di gudang.');
        }

        $purchaseOrder->update([
            'status' => 'cancelled',
            'notes' => ($purchaseOrder->notes ? $purchaseOrder->notes . ' | ' : '') . 'Dibatalkan oleh ' . auth()->user()->name . ' pada ' . now()->format('d/m/Y H:i'),
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', "Purchase Order ({$purchaseOrder->po_number}) berhasil dibatalkan.");
    }

    /**
     * Printable Surat Pesanan Pembelian (Purchase Order).
     */
    public function printPo(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'approver', 'items.product']);

        return view('purchase_orders.print', compact('purchaseOrder'));
    }
}

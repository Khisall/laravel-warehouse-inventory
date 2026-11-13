<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    /**
     * Display list of Goods Receipts (GRN).
     */
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'warehouse', 'receiver', 'items.product'])
            ->withCount('items')
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('vendor_do_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder', function ($pq) use ($search) {
                      $pq->where('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $receipts = $query->paginate(15)->withQueryString();

        return view('goods_receipts.index', compact('receipts'));
    }

    /**
     * Show form for receiving items against a specific Purchase Order.
     */
    public function create(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder->id)
                ->with('error', "Purchase Order ini berstatus '{$purchaseOrder->status_label}' dan tidak dapat menerima barang lagi.");
        }

        $purchaseOrder->load(['supplier', 'warehouse', 'items.product']);

        return view('goods_receipts.create', compact('purchaseOrder'));
    }

    /**
     * Store and commit a Goods Receipt into warehouse inventory.
     */
    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'vendor_do_number' => 'required|string|max:100',
            'received_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.quantity_rejected' => 'nullable|integer|min:0',
            'items.*.rejection_reason' => 'nullable|string|max:255',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.shelf_location' => 'nullable|string|max:50',
        ]);

        // Auto-generate GRN Number: GRN-YYYYMMDD-XXXX
        $datePrefix = 'GRN-' . date('Ymd');
        $countToday = GoodsReceipt::whereDate('created_at', Carbon::today())->count() + 1;
        $receiptNumber = $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $grn = GoodsReceipt::create([
                'receipt_number' => $receiptNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $purchaseOrder->warehouse_id,
                'vendor_do_number' => $validated['vendor_do_number'],
                'received_date' => $validated['received_date'],
                'received_by' => auth()->id(),
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalAccepted = 0;

            foreach ($validated['items'] as $itemData) {
                $qtyReceived = (int) ($itemData['quantity_received'] ?? 0);
                $qtyRejected = (int) ($itemData['quantity_rejected'] ?? 0);

                if ($qtyReceived === 0 && $qtyRejected === 0) {
                    continue;
                }

                $poItem = PurchaseOrderItem::findOrFail($itemData['purchase_order_item_id']);
                $product = Product::findOrFail($itemData['product_id']);

                // Create GRN item
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $product->id,
                    'quantity_received' => $qtyReceived,
                    'quantity_rejected' => $qtyRejected,
                    'rejection_reason' => $itemData['rejection_reason'] ?? null,
                    'batch_number' => $itemData['batch_number'] ?? $product->batch_number,
                    'expiry_date' => $itemData['expiry_date'] ?? $product->expiry_date,
                    'shelf_location' => $itemData['shelf_location'] ?? $product->warehouse_location,
                ]);

                // Update physical inventory for accepted quantity
                if ($qtyReceived > 0) {
                    $oldStock = $product->quantity;
                    $newStock = $oldStock + $qtyReceived;

                    $product->quantity = $newStock;
                    if (!empty($itemData['batch_number'])) {
                        $product->batch_number = $itemData['batch_number'];
                    }
                    if (!empty($itemData['expiry_date'])) {
                        $product->expiry_date = $itemData['expiry_date'];
                    }
                    if (!empty($itemData['shelf_location'])) {
                        $product->warehouse_location = $itemData['shelf_location'];
                    }
                    $product->last_modified = now();
                    $product->save();

                    // Create stock movement (Inbound)
                    StockMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'type' => 'in',
                        'quantity' => $qtyReceived,
                        'stock_before' => $oldStock,
                        'stock_after' => $newStock,
                        'reference_no' => $grn->receipt_number,
                        'batch_number' => $product->batch_number,
                        'expiry_date' => $product->expiry_date,
                        'notes' => "Penerimaan Inbound PO [{$purchaseOrder->po_number}], No. SJ Vendor: [{$grn->vendor_do_number}]",
                        'created_by' => auth()->user()->name,
                    ]);

                    // Update PO item received count
                    $poItem->quantity_received += $qtyReceived;
                    $poItem->save();

                    $totalAccepted += $qtyReceived;
                }
            }

            // Update parent PO receiving status (partial or received)
            $purchaseOrder->updateReceivingStatus();

            DB::commit();

            return redirect()->route('goods-receipts.show', $grn->id)
                ->with('success', "✓ Penerimaan barang ({$grn->receipt_number}) berhasil disimpan! {$totalAccepted} unit telah ditambahkan ke kartu stok.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses penerimaan barang: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Goods Receipt.
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'warehouse',
            'receiver',
            'items.product.category',
            'items.purchaseOrderItem',
        ]);

        return view('goods_receipts.show', compact('goodsReceipt'));
    }

    /**
     * Printable Surat Penerimaan Barang (GRN / Bukti Masuk Gudang).
     */
    public function printGrn(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'warehouse',
            'receiver',
            'items.product.category',
        ]);

        return view('goods_receipts.print', compact('goodsReceipt'));
    }
}

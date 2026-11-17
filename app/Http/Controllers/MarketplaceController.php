<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\FefoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    /**
     * Display a listing of connected marketplace channels & stock matrix.
     */
    public function index(Request $request): View
    {
        $query = MarketplaceListing::with(['product.warehouse', 'product.batches']);

        if ($request->filled('channel') && $request->channel !== 'all') {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('channel_sku', 'like', "%{$q}%")
                    ->orWhere('external_product_id', 'like', "%{$q}%")
                    ->orWhere('store_name', 'like', "%{$q}%")
                    ->orWhereHas('product', function ($p) use ($q) {
                        $p->where('sku', 'like', "%{$q}%")
                          ->orWhere('title', 'like', "%{$q}%");
                    });
            });
        }

        $listings = $query->latest()->paginate(15)->withQueryString();
        $unlistedProducts = Product::where('is_marketplace_listed', false)->orderBy('title')->get();
        $allProducts = Product::where('quantity', '>', 0)->orderBy('title')->get();

        // Marketplace Stats
        $stats = [
            'total_listings' => MarketplaceListing::count(),
            'shopee_count' => MarketplaceListing::where('channel', 'Shopee')->count(),
            'tokopedia_count' => MarketplaceListing::where('channel', 'Tokopedia')->count(),
            'tiktok_count' => MarketplaceListing::whereIn('channel', ['TikTok', 'TikTok Shop'])->count(),
            'total_synced_units' => MarketplaceListing::sum('synced_stock'),
        ];

        return view('marketplace.index', compact('listings', 'unlistedProducts', 'allProducts', 'stats'));
    }

    /**
     * Store a new marketplace listing.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'channel' => 'required|string|in:Shopee,Tokopedia,TikTok Shop,Lazada',
            'store_name' => 'nullable|string|max:100',
            'channel_price' => 'required|numeric|min:0',
            'safety_stock_buffer' => 'required|integer|min:0|max:100',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $buffer = (int) $validated['safety_stock_buffer'];
        $syncedStock = max(0, $product->quantity - $buffer);

        MarketplaceListing::create([
            'product_id' => $product->id,
            'channel' => $validated['channel'],
            'store_name' => $validated['store_name'] ?: ($validated['channel'] . ' Official Mall'),
            'channel_sku' => 'MKT-' . strtoupper(substr($validated['channel'], 0, 3)) . '-' . $product->sku,
            'external_product_id' => 'EXT-' . rand(100000, 999999),
            'channel_code' => strtoupper(substr($validated['channel'], 0, 4)),
            'channel_price' => $validated['channel_price'],
            'safety_stock_buffer' => $buffer,
            'synced_stock' => $syncedStock,
            'sync_status' => 'synced',
            'auto_sync' => true,
            'last_synced_at' => now(),
        ]);

        $product->update([
            'is_marketplace_listed' => true,
        ]);

        return redirect()->route('marketplace.index')
            ->with('success', "Produk {$product->title} berhasil didaftarkan ke {$validated['channel']} dengan Safety Stock Buffer {$buffer} unit.");
    }

    /**
     * Update safety stock buffer for a listing.
     */
    public function updateBuffer(Request $request, MarketplaceListing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'safety_stock_buffer' => 'required|integer|min:0|max:100',
        ]);

        $product = $listing->product;
        $newBuffer = (int) $validated['safety_stock_buffer'];
        $newSyncedStock = max(0, ($product ? $product->quantity : 0) - $newBuffer);

        $listing->update([
            'safety_stock_buffer' => $newBuffer,
            'synced_stock' => $newSyncedStock,
            'last_synced_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Safety Stock Buffer untuk {$listing->channel} berhasil diperbarui menjadi {$newBuffer} unit (Stok tayang: {$newSyncedStock} unit).");
    }

    /**
     * Simulate an incoming marketplace order (Shopee / Tokopedia / TikTok Shop).
     * Automatically applies FEFO deduction and re-syncs all connected channels.
     */
    public function simulateOrder(Request $request, FefoService $fefoService): RedirectResponse
    {
        $validated = $request->validate([
            'channel' => 'required|string|in:Shopee,Tokopedia,TikTok Shop,Lazada',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($validated, $fefoService) {
            $product = Product::findOrFail($validated['product_id']);
            $qty = (int) $validated['quantity'];
            $channel = $validated['channel'];
            $customerName = $validated['customer_name'] ?: 'Pelanggan ' . $channel;

            // Find listing for this channel
            $listing = MarketplaceListing::where('product_id', $product->id)
                ->where('channel', $channel)
                ->first();

            $buffer = $listing ? $listing->safety_stock_buffer : 0;
            $sellableStock = max(0, $product->quantity - $buffer);

            // Prevent overselling beyond safety buffer
            if ($qty > $product->quantity) {
                return redirect()->back()->with('error', "Simulasi Gagal: Kuantitas order ({$qty} unit) melebihi stok fisik gudang ({$product->quantity} unit).");
            }

            // Generate Order Number
            $prefix = match ($channel) {
                'Shopee' => 'ORD-SHP-',
                'Tokopedia' => 'ORD-TKP-',
                'TikTok Shop' => 'ORD-TTK-',
                default => 'ORD-MKT-',
            };
            $orderNumber = $prefix . now()->format('ymd') . '-' . rand(1000, 9999);

            // 1. Deduct stock using FEFO Engine
            $fefoResult = $fefoService->deductFefo(
                $product,
                $qty,
                $orderNumber,
                "Simulasi Order Masuk dari {$channel}",
                auth()->id()
            );

            // 2. Create Order Record
            $unitPrice = $listing ? $listing->channel_price : $product->price;
            $subtotal = $unitPrice * $qty;

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
                'marketplace' => $channel,
                'status' => 'completed',
                'total_amount' => $subtotal,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_title' => $product->title,
                'sku' => $product->sku,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            // 3. Re-sync all connected channels for this product
            $allListings = MarketplaceListing::where('product_id', $product->id)->get();
            $syncDetails = [];

            foreach ($allListings as $l) {
                $newSynced = max(0, $product->fresh()->quantity - $l->safety_stock_buffer);
                $l->update([
                    'synced_stock' => $newSynced,
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                ]);
                $syncDetails[] = "{$l->channel}: {$newSynced} unit";
            }

            $pickedMsg = !empty($fefoResult['picked_batches'])
                ? ' | Dialokasikan dari batch FEFO: ' . implode(', ', array_map(fn($b) => $b['batch_number'] . ' (' . $b['picked_quantity'] . ' unit)', $fefoResult['picked_batches']))
                : '';

            $msg = "🎉 Simulasi Order {$orderNumber} ({$channel}) berhasil! {$qty} unit terjual. Sisa stok fisik gudang: {$product->fresh()->quantity} unit{$pickedMsg}. Stok di channel lain otomatis tersinkron: [" . implode(', ', $syncDetails) . "].";

            return redirect()->route('marketplace.index')->with('success', $msg);
        });
    }

    /**
     * Sync all product stocks to all connected channels.
     */
    public function syncAll(): RedirectResponse
    {
        $listings = MarketplaceListing::with('product')->get();
        foreach ($listings as $listing) {
            if ($listing->product) {
                $synced = max(0, $listing->product->quantity - $listing->safety_stock_buffer);
                $listing->update([
                    'synced_stock' => $synced,
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                ]);
            }
        }

        return redirect()->route('marketplace.index')
            ->with('success', 'Seluruh channel marketplace (Shopee, Tokopedia, TikTok Shop, Lazada) berhasil disinkronisasi dengan kalkulasi Safety Stock Buffer.');
    }
}

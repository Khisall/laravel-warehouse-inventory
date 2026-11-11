<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\Product;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketplaceListing::with('product');

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $listings = $query->latest()->paginate(15);
        $products = Product::where('is_marketplace_listed', false)->get();

        return view('marketplace.index', compact('listings', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'channel' => 'required|string',
            'channel_code' => 'required|string',
            'channel_price' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        MarketplaceListing::create([
            'product_id' => $product->id,
            'channel' => $validated['channel'],
            'channel_sku' => 'MKT-' . $product->sku,
            'external_product_id' => 'EXT-' . rand(100000, 999999),
            'channel_code' => $validated['channel_code'],
            'channel_price' => $validated['channel_price'],
            'synced_stock' => $product->quantity,
            'sync_status' => 'synced',
            'last_synced_at' => now(),
        ]);

        $product->update([
            'is_marketplace_listed' => true,
            'status_code' => $validated['channel_code'],
        ]);

        return redirect()->route('marketplace.index')->with('success', 'Produk berhasil didaftarkan ke marketplace!');
    }

    public function syncAll()
    {
        $listings = MarketplaceListing::with('product')->get();
        foreach ($listings as $listing) {
            if ($listing->product) {
                $listing->update([
                    'synced_stock' => $listing->product->quantity,
                    'sync_status' => 'synced',
                    'last_synced_at' => now(),
                ]);
            }
        }

        return redirect()->route('marketplace.index')->with('success', 'Sinkronisasi semua stok ke marketplace berhasil diperbarui.');
    }
}

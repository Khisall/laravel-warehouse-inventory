<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->whereIn('status', ['processing', 'shipped', 'completed']);

        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $request->input('shipping_status'));
        }

        $shipments = $query->latest()->paginate(15);

        return view('shipping.index', compact('shipments'));
    }

    public function updateShipping(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipping_status' => 'required|in:packing,in_transit,delivered',
            'courier' => 'nullable|string',
            'tracking_number' => 'nullable|string',
        ]);

        if ($validated['shipping_status'] === 'delivered') {
            $order->status = 'completed';
        } elseif ($validated['shipping_status'] === 'in_transit') {
            $order->status = 'shipped';
        }

        $order->shipping_status = $validated['shipping_status'];
        if (!empty($validated['courier'])) $order->courier = $validated['courier'];
        if (!empty($validated['tracking_number'])) $order->tracking_number = $validated['tracking_number'];
        $order->save();

        return redirect()->route('shipping.index')->with('success', 'Status pengiriman (' . $order->order_number . ') berhasil diupdate.');
    }
}

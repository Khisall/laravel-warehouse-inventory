<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    /**
     * Display listing of suppliers / vendors.
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query->paginate(15)->withQueryString();
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();

        return view('suppliers.index', compact('suppliers', 'totalSuppliers', 'activeSuppliers'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:30|unique:suppliers,code',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'payment_terms' => 'required|string|max:50',
        ]);

        if (empty($validated['code'])) {
            $count = Supplier::count() + 1;
            $validated['code'] = 'SUP-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier / Pemasok baru berhasil ditambahkan!');
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:500',
            'payment_terms' => 'required|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Data Supplier (' . $supplier->name . ') berhasil diperbarui!');
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return redirect()->back()->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat Purchase Order.');
        }

        $name = $supplier->name;
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier ' . $name . ' berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('products')
            ->with(['products' => function ($q) {
                $q->limit(5);
            }])
            ->get();

        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'location' => 'required|string|max:100',
            'manager_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('success', 'Gudang baru berhasil ditambahkan!');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'location' => 'required|string|max:100',
            'manager_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('success', 'Data gudang berhasil diperbarui!');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->products()->count() > 0) {
            return redirect()->back()->with('error', 'Gudang tidak dapat dihapus karena masih memiliki barang tersimpan.');
        }

        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }
}

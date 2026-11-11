<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalItems = Product::count();
        $totalStockQty = Product::sum('quantity');
        $totalInventoryValue = Product::select(DB::raw('SUM(quantity * price) as val'))->value('val') ?? 0;
        $totalCostValue = Product::select(DB::raw('SUM(quantity * cost_price) as val'))->value('val') ?? 0;
        $estimatedProfitPotential = $totalInventoryValue - $totalCostValue;

        $categoryStats = Category::withCount('products')
            ->withSum('products', 'quantity')
            ->get();

        $warehouseStats = Warehouse::withCount('products')
            ->withSum('products', 'quantity')
            ->get();

        $recentMovements = StockMovement::with(['product', 'warehouse'])->latest()->limit(10)->get();

        $lowStockItems = Product::where('status', '!=', 'in_stock')->get();

        return view('reports.index', compact(
            'totalItems',
            'totalStockQty',
            'totalInventoryValue',
            'totalCostValue',
            'estimatedProfitPotential',
            'categoryStats',
            'warehouseStats',
            'recentMovements',
            'lowStockItems'
        ));
    }
}

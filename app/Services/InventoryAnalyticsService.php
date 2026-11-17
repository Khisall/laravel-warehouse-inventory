<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventoryAnalyticsService
{
    /**
     * Compute comprehensive ABC analysis and velocity (fast/slow/dead) metrics.
     */
    public function getTurnoverAnalytics(array $filters = []): array
    {
        $periodDays = isset($filters['period']) && is_numeric($filters['period']) ? (int) $filters['period'] : 90;
        $startDate = $periodDays > 0 ? Carbon::now()->subDays($periodDays)->startOfDay() : null;

        // Base Product Query
        $productQuery = Product::with(['category', 'warehouse']);

        if (!empty($filters['warehouse_id'])) {
            $productQuery->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['category_id'])) {
            $productQuery->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $productQuery->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('warehouse_location', 'like', "%{$search}%");
            });
        }

        $products = $productQuery->get();

        if ($products->isEmpty()) {
            return [
                'items' => collect(),
                'summary' => $this->getEmptySummary(),
                'period_days' => $periodDays,
            ];
        }

        // Fetch sales aggregates from OrderItem within period
        $orderItemQuery = OrderItem::whereHas('order', function ($oq) {
            $oq->where('status', '!=', 'cancelled');
        });

        if ($startDate) {
            $orderItemQuery->where('created_at', '>=', $startDate);
        }

        $salesByProduct = $orderItemQuery
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, MAX(created_at) as last_sale_at')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Fetch latest StockMovement OUT dates for cross-checking activity
        $lastMovementOutByProduct = StockMovement::where('type', 'out')
            ->selectRaw('product_id, MAX(created_at) as last_out_at, SUM(ABS(quantity)) as total_out_qty')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Build item metrics
        $analyzedItems = $products->map(function ($product) use ($salesByProduct, $lastMovementOutByProduct, $periodDays) {
            $sales = $salesByProduct->get($product->id);
            $movement = $lastMovementOutByProduct->get($product->id);

            $soldUnits = $sales ? (int) $sales->total_qty : 0;
            $revenueGenerated = $sales ? (float) $sales->total_revenue : ($soldUnits * (float) $product->price);

            // Determine last sale / out date
            $lastSaleDate = null;
            if ($sales && $sales->last_sale_at) {
                $lastSaleDate = Carbon::parse($sales->last_sale_at);
            } elseif ($movement && $movement->last_out_at) {
                $lastSaleDate = Carbon::parse($movement->last_out_at);
            }

            $daysSinceLastSale = $lastSaleDate ? (int) now()->diffInDays($lastSaleDate) : 999;
            $currentStock = (int) $product->quantity;
            $stockHoldingValue = $currentStock * (float) ($product->cost_price ?? 0);
            $retailPotentialValue = $currentStock * (float) ($product->price ?? 0);

            // Velocity metrics
            $dailyVelocity = $soldUnits / max(1, $periodDays);
            $daysOfInventory = $dailyVelocity > 0 ? (int) round($currentStock / $dailyVelocity) : 999;

            $cogs = $soldUnits * (float) ($product->cost_price ?? 0);
            $turnoverRatio = $stockHoldingValue > 0 ? round($cogs / $stockHoldingValue, 2) : ($soldUnits > 0 ? 1.0 : 0.0);

            return [
                'product' => $product,
                'sold_units' => $soldUnits,
                'revenue_generated' => $revenueGenerated,
                'cogs' => $cogs,
                'current_stock' => $currentStock,
                'stock_holding_value' => $stockHoldingValue,
                'retail_potential_value' => $retailPotentialValue,
                'last_sale_date' => $lastSaleDate,
                'days_since_last_sale' => $daysSinceLastSale,
                'daily_velocity' => $dailyVelocity,
                'days_of_inventory' => $daysOfInventory,
                'turnover_ratio' => $turnoverRatio,
                // Placeholders to be populated by classification step
                'abc_class' => 'C',
                'revenue_share_pct' => 0,
                'cumulative_revenue_pct' => 0,
                'velocity_status' => 'slow',
                'action_recommendation' => '',
            ];
        });

        // Step 3: Compute ABC Classification (Pareto 80 / 15 / 5)
        $totalRevenue = $analyzedItems->sum('revenue_generated');

        // Sort descending by revenue
        $sortedItems = $analyzedItems->sortByDesc('revenue_generated')->values();
        $runningRevenue = 0;

        $classifiedItems = $sortedItems->map(function ($item) use ($totalRevenue, &$runningRevenue) {
            $runningRevenue += $item['revenue_generated'];
            $revenueShare = $totalRevenue > 0 ? ($item['revenue_generated'] / $totalRevenue) * 100 : 0;
            $cumulativePct = $totalRevenue > 0 ? ($runningRevenue / $totalRevenue) * 100 : 100;

            // ABC Categorization
            if ($totalRevenue > 0 && $item['revenue_generated'] > 0) {
                if ($cumulativePct <= 80 || $revenueShare >= 20) {
                    $abcClass = 'A';
                } elseif ($cumulativePct <= 95) {
                    $abcClass = 'B';
                } else {
                    $abcClass = 'C';
                }
            } else {
                $abcClass = 'C';
            }

            // Velocity Status
            $isDeadStock = $item['current_stock'] > 0 && ($item['sold_units'] === 0 || $item['days_since_last_sale'] >= 90);
            $isFastMoving = $item['sold_units'] > 0 && ($item['days_since_last_sale'] <= 21 || $item['days_of_inventory'] <= 35 || $item['daily_velocity'] >= 0.2);
            $isMediumMoving = $item['sold_units'] > 0 && ($item['days_of_inventory'] > 35 && $item['days_of_inventory'] <= 90);

            if ($isDeadStock) {
                $velocityStatus = 'dead';
                $recommendation = 'Cuci gudang / Flash sale / Bundling promo dengan produk Kelas A';
            } elseif ($isFastMoving) {
                $velocityStatus = 'fast';
                $recommendation = 'Pertahankan safety stock tinggi & jadwalkan PO rutin ke supplier';
            } elseif ($isMediumMoving) {
                $velocityStatus = 'medium';
                $recommendation = 'Monitor stok bulanan & order sesuai kebutuhan reorder point';
            } else {
                $velocityStatus = 'slow';
                $recommendation = 'Kurangi kuantitas pesanan baru untuk hindari modal mati';
            }

            $item['abc_class'] = $abcClass;
            $item['revenue_share_pct'] = round($revenueShare, 1);
            $item['cumulative_revenue_pct'] = round($cumulativePct, 1);
            $item['velocity_status'] = $velocityStatus;
            $item['action_recommendation'] = $recommendation;

            return $item;
        });

        // Step 4: Executive KPI Summary Aggregation
        $totalItemsCount = $classifiedItems->count();
        $totalSoldUnits = $classifiedItems->sum('sold_units');
        $totalStockHoldingValue = $classifiedItems->sum('stock_holding_value');

        $classAItems = $classifiedItems->where('abc_class', 'A');
        $classBItems = $classifiedItems->where('abc_class', 'B');
        $classCItems = $classifiedItems->where('abc_class', 'C');

        $deadStockItems = $classifiedItems->where('velocity_status', 'dead');
        $fastMovingItems = $classifiedItems->where('velocity_status', 'fast');
        $mediumMovingItems = $classifiedItems->where('velocity_status', 'medium');
        $slowMovingItems = $classifiedItems->where('velocity_status', 'slow');

        $deadStockValue = $deadStockItems->sum('stock_holding_value');
        $deadStockUnits = $deadStockItems->sum('current_stock');

        $summary = [
            'total_products' => $totalItemsCount,
            'total_revenue' => $totalRevenue,
            'total_sold_units' => $totalSoldUnits,
            'total_stock_value' => $totalStockHoldingValue,

            // ABC Metrics
            'class_a_count' => $classAItems->count(),
            'class_a_revenue' => $classAItems->sum('revenue_generated'),
            'class_a_revenue_pct' => $totalRevenue > 0 ? round(($classAItems->sum('revenue_generated') / $totalRevenue) * 100, 1) : 0,

            'class_b_count' => $classBItems->count(),
            'class_b_revenue' => $classBItems->sum('revenue_generated'),
            'class_b_revenue_pct' => $totalRevenue > 0 ? round(($classBItems->sum('revenue_generated') / $totalRevenue) * 100, 1) : 0,

            'class_c_count' => $classCItems->count(),
            'class_c_revenue' => $classCItems->sum('revenue_generated'),
            'class_c_revenue_pct' => $totalRevenue > 0 ? round(($classCItems->sum('revenue_generated') / $totalRevenue) * 100, 1) : 0,

            // Velocity Metrics
            'fast_moving_count' => $fastMovingItems->count(),
            'medium_moving_count' => $mediumMovingItems->count(),
            'slow_moving_count' => $slowMovingItems->count(),
            'dead_stock_count' => $deadStockItems->count(),
            'dead_stock_value' => $deadStockValue,
            'dead_stock_units' => $deadStockUnits,
        ];

        // Step 5: Apply User Filters (ABC class or Velocity tab)
        $filteredItems = $classifiedItems;

        if (!empty($filters['classification']) && in_array(strtoupper($filters['classification']), ['A', 'B', 'C'])) {
            $filteredItems = $filteredItems->where('abc_class', strtoupper($filters['classification']));
        }

        if (!empty($filters['velocity']) && in_array($filters['velocity'], ['fast', 'medium', 'slow', 'dead'])) {
            $filteredItems = $filteredItems->where('velocity_status', $filters['velocity']);
        }

        return [
            'items' => $filteredItems->values(),
            'allItems' => $classifiedItems,
            'summary' => $summary,
            'period_days' => $periodDays,
        ];
    }

    private function getEmptySummary(): array
    {
        return [
            'total_products' => 0,
            'total_revenue' => 0,
            'total_sold_units' => 0,
            'total_stock_value' => 0,
            'class_a_count' => 0,
            'class_a_revenue' => 0,
            'class_a_revenue_pct' => 0,
            'class_b_count' => 0,
            'class_b_revenue' => 0,
            'class_b_revenue_pct' => 0,
            'class_c_count' => 0,
            'class_c_revenue' => 0,
            'class_c_revenue_pct' => 0,
            'fast_moving_count' => 0,
            'medium_moving_count' => 0,
            'slow_moving_count' => 0,
            'dead_stock_count' => 0,
            'dead_stock_value' => 0,
            'dead_stock_units' => 0,
        ];
    }
}

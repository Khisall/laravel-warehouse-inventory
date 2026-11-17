<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (Schema::hasTable('products')) {
                $outOfStock = Product::outOfStock()->count();
                $lowStock = Product::lowStock()->count();
                $expired = Product::expired()->count();
                $expiringSoon = Product::expiringSoon(30)->count();

                $totalAlerts = $outOfStock + $lowStock + $expired + $expiringSoon;

                $urgentStockItems = Product::needsReorder()
                    ->take(3)
                    ->get(['id', 'sku', 'title', 'quantity', 'min_stock', 'unit']);

                $urgentExpiryItems = Product::whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays(30))
                    ->orderBy('expiry_date', 'asc')
                    ->take(3)
                    ->get(['id', 'sku', 'title', 'quantity', 'unit', 'batch_number', 'expiry_date']);

                $view->with('globalAlerts', [
                    'total' => $totalAlerts,
                    'out_of_stock' => $outOfStock,
                    'low_stock' => $lowStock,
                    'expired' => $expired,
                    'expiring_soon' => $expiringSoon,
                    'urgent_stock' => $urgentStockItems,
                    'urgent_expiry' => $urgentExpiryItems,
                ]);
            }
        });
    }
}

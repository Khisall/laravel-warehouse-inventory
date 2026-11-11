<?php

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('inventory.index');
});

// Dashboard & Inventory Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::post('/', [InventoryController::class, 'store'])->name('store');
    Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
    Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
    Route::post('/{product}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('adjust-stock');
    Route::post('/bulk-action', [InventoryController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/export/csv', [InventoryController::class, 'exportCsv'])->name('export-csv');
});

// Warehouse Locations
Route::prefix('warehouses')->name('warehouses.')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('index');
    Route::post('/', [WarehouseController::class, 'store'])->name('store');
    Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
    Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
});

// Activity & Inventory History Log
Route::prefix('activity')->name('activity.')->group(function () {
    Route::get('/', [StockMovementController::class, 'index'])->name('index');
    Route::post('/', [StockMovementController::class, 'store'])->name('store');
});

// Marketplace Listings & Integration
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [MarketplaceController::class, 'index'])->name('index');
    Route::post('/', [MarketplaceController::class, 'store'])->name('store');
    Route::post('/sync-all', [MarketplaceController::class, 'syncAll'])->name('sync-all');
});

// Orders
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
});

// Shipping
Route::prefix('shipping')->name('shipping.')->group(function () {
    Route::get('/', [ShippingController::class, 'index'])->name('index');
    Route::put('/{order}', [ShippingController::class, 'updateShipping'])->name('update');
});

// Reports & Analytics
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// Quick Settings & Help
Route::view('/settings', 'settings.index')->name('settings.index');
Route::view('/whats-new', 'info.whats-new')->name('whats-new.index');
Route::view('/help', 'info.help')->name('help.index');

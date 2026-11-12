<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/demo/{role}', [AuthController::class, 'demoLogin'])->name('auth.demo');

// Root redirect
Route::get('/', function () {
    return redirect()->route('inventory.index');
});

// Protected Routes (Must be logged in)
Route::middleware('auth')->group(function () {

    // Dashboard & Inventory Routes (Accessible by all roles, but financial details filtered by role)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/print-labels', [InventoryController::class, 'printBulkLabels'])->name('print-bulk-labels');
        Route::get('/lookup/{sku}', [InventoryController::class, 'lookupSku'])->name('lookup-sku');
        Route::put('/{product}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::get('/{product}/print-label', [InventoryController::class, 'printLabel'])->name('print-label');
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

    // Stock Opname (Digital Physical Audit & Reconciliation)
    Route::prefix('opname')->name('opname.')->middleware('role:super_admin,warehouse_manager,warehouse_staff')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index'])->name('index');
        Route::post('/', [StockOpnameController::class, 'store'])->name('store');
        Route::get('/{opname}', [StockOpnameController::class, 'show'])->name('show');
        Route::post('/{opname}/scan', [StockOpnameController::class, 'scanBarcode'])->name('scan');
        Route::put('/{opname}/items/{item}', [StockOpnameController::class, 'updateItem'])->name('update-item');
        Route::post('/{opname}/quick-fill', [StockOpnameController::class, 'quickFillAll'])->name('quick-fill');
        Route::post('/{opname}/reconcile', [StockOpnameController::class, 'reconcile'])->name('reconcile');
        Route::post('/{opname}/cancel', [StockOpnameController::class, 'cancel'])->name('cancel');
        Route::get('/{opname}/report', [StockOpnameController::class, 'report'])->name('report');
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
        Route::get('/{order}/delivery-order', [OrderController::class, 'deliveryOrder'])->name('delivery-order');
        Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
    });

    // Shipping
    Route::prefix('shipping')->name('shipping.')->group(function () {
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::put('/{order}', [ShippingController::class, 'updateShipping'])->name('update');
    });

    // Reports & Financial Analytics (Managers & Super Admin only)
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('role:super_admin,warehouse_manager')
        ->name('reports.index');

    // User & Team Management Module (Super Admin / Owner only)
    Route::prefix('users')->name('users.')->middleware('role:super_admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // System Settings (Super Admin only)
    Route::view('/settings', 'settings.index')
        ->middleware('role:super_admin')
        ->name('settings.index');

    // General Info
    Route::view('/whats-new', 'info.whats-new')->name('whats-new.index');
    Route::view('/help', 'info.help')->name('help.index');
});

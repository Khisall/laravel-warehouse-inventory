<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckInventoryAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan warehouse inventory for low-stock and expiring products, and report alerts.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning warehouse inventory for critical alerts...\n");

        // 1. Check Low Stock & Out of Stock
        $reorderProducts = Product::needsReorder()->get();
        if ($reorderProducts->isNotEmpty()) {
            $this->warn("⚠️  [STOK PERLU RESTOCK / KRITIS]: {$reorderProducts->count()} SKU terdeteksi!");

            $stockRows = [];
            foreach ($reorderProducts as $p) {
                $status = $p->quantity <= 0 ? 'HABIS (0)' : 'MENIPIS';
                $stockRows[] = [
                    $p->sku,
                    $p->title,
                    $p->quantity . ' ' . $p->unit,
                    $p->min_stock . ' ' . $p->unit,
                    '+' . $p->suggested_reorder_qty . ' ' . $p->unit,
                    $status,
                ];
            }

            $this->table(['SKU', 'Nama Barang', 'Stok Saat Ini', 'Min Stok', 'Saran Restock', 'Status'], $stockRows);
        } else {
            $this->info("✓ Seluruh stok barang berada di atas batas minimum.");
        }

        $this->newLine();

        // 2. Check Expiry
        $expiringProducts = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->get();

        if ($expiringProducts->isNotEmpty()) {
            $this->error("🚨 [KEDALUWARSA / FEFO KRITIS]: {$expiringProducts->count()} SKU mendekati/melewati expired date!");

            $expRows = [];
            foreach ($expiringProducts as $p) {
                $days = $p->days_until_expiry;
                $remText = $days < 0 ? "EXPIRED (" . abs($days) . " hari lalu)" : ($days === 0 ? "HARI INI" : "{$days} hari lagi");
                $expRows[] = [
                    $p->sku,
                    $p->title,
                    $p->batch_number ?: '-',
                    $p->expiry_date->format('d/m/Y'),
                    $remText,
                    $p->quantity . ' ' . $p->unit,
                ];
            }

            $this->table(['SKU', 'Nama Barang', 'Batch', 'Tgl Expired', 'Sisa Waktu', 'Stok di Rak'], $expRows);
        } else {
            $this->info("✓ Tidak ada barang yang kedaluwarsa atau mendekati kedaluwarsa dalam 30 hari ke depan.");
        }

        $totalAlerts = $reorderProducts->count() + $expiringProducts->count();
        if ($totalAlerts > 0) {
            Log::warning("Inventory Alert Monitor: {$reorderProducts->count()} low-stock SKU, {$expiringProducts->count()} expiring SKU detected.");
        }

        $this->info("\nAlert scan completed. Total critical alerts: {$totalAlerts}");
        return 0;
    }
}

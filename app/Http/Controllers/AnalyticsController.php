<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Warehouse;
use App\Services\InventoryAnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    protected InventoryAnalyticsService $analyticsService;

    public function __construct(InventoryAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display ABC & Turnover Analytics Dashboard.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'period',
            'warehouse_id',
            'category_id',
            'classification',
            'velocity',
            'search',
        ]);

        $data = $this->analyticsService->getTurnoverAnalytics($filters);

        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('analytics.index', [
            'items' => $data['items'],
            'summary' => $data['summary'],
            'periodDays' => $data['period_days'],
            'warehouses' => $warehouses,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Export Analytics Data to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only([
            'period',
            'warehouse_id',
            'category_id',
            'classification',
            'velocity',
            'search',
        ]);

        $data = $this->analyticsService->getTurnoverAnalytics($filters);
        $items = $data['items'];

        $filename = 'analisis_perputaran_stok_abc_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'SKU',
                'Nama Produk',
                'Kategori',
                'Gudang',
                'Lokasi Rak',
                'Stok Fisik',
                'Satuan',
                'Nilai Modal Tertahan (Rp)',
                'Unit Terjual (Periode)',
                'Total Omset Penjualan (Rp)',
                'Kontribusi Omset (%)',
                'Klasifikasi ABC',
                'Status Perputaran',
                'Hari Sejak Penjualan Terakhir',
                'Estimasi Hari Habis (DOH)',
                'Rasio Perputaran (ITR)',
                'Rekomendasi Tindakan',
            ]);

            foreach ($items as $it) {
                $p = $it['product'];
                $dohText = $it['days_of_inventory'] >= 999 ? 'Non-Moving' : $it['days_of_inventory'] . ' Hari';
                $lastSaleText = $it['days_since_last_sale'] >= 999 ? 'Belum Pernah' : $it['days_since_last_sale'] . ' Hari Lalu';

                fputcsv($handle, [
                    $p->sku,
                    $p->title,
                    $p->category?->name ?? '-',
                    $p->warehouse?->name ?? '-',
                    $p->warehouse_location ?? '-',
                    $it['current_stock'],
                    $p->unit,
                    $it['stock_holding_value'],
                    $it['sold_units'],
                    $it['revenue_generated'],
                    $it['revenue_share_pct'] . '%',
                    'Kelas ' . $it['abc_class'],
                    strtoupper($it['velocity_status']),
                    $lastSaleText,
                    $dohText,
                    $it['turnover_ratio'],
                    $it['action_recommendation'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Printable Executive Report for ABC & Inventory Velocity.
     */
    public function print(Request $request)
    {
        $filters = $request->only([
            'period',
            'warehouse_id',
            'category_id',
            'classification',
            'velocity',
            'search',
        ]);

        $data = $this->analyticsService->getTurnoverAnalytics($filters);

        return view('analytics.print', [
            'items' => $data['items'],
            'summary' => $data['summary'],
            'periodDays' => $data['period_days'],
            'filters' => $filters,
        ]);
    }
}

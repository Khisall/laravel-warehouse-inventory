<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesAnalyticsSeeder extends Seeder
{
    /**
     * Seed realistic orders and movements for ABC & Turnover Analytics.
     */
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        $marketplaces = ['Shopee Official', 'Tokopedia Mall', 'TikTok Shop', 'Lazada Flagship', 'Direct B2B Store'];
        $couriers = ['JNE Regular', 'SiCepat BEST', 'J&T Express', 'Anteraja Regular'];

        // Sample historical orders across the last 120 days
        $ordersPlan = [
            // Fast Moving (Sold in last 7 - 20 days, high volume)
            [
                'order_number' => 'ORD-2026-1101',
                'customer' => 'Budi Prasetyo',
                'marketplace' => 'Shopee Official',
                'days_ago' => 3,
                'items' => [
                    ['sku' => 'MRP400', 'qty' => 8], // Premium Leather Jacket
                    ['sku' => 'MRP710', 'qty' => 5], // Thermal Windbreaker Pro
                ],
            ],
            [
                'order_number' => 'ORD-2026-1102',
                'customer' => 'Dewi Anggraini',
                'marketplace' => 'Tokopedia Mall',
                'days_ago' => 6,
                'items' => [
                    ['sku' => 'MRP400', 'qty' => 12],
                    ['sku' => 'MRP830', 'qty' => 4],
                ],
            ],
            [
                'order_number' => 'ORD-2026-1103',
                'customer' => 'Rian Ardiansyah',
                'marketplace' => 'TikTok Shop',
                'days_ago' => 12,
                'items' => [
                    ['sku' => 'MRP710', 'qty' => 6],
                    ['sku' => 'MRP405', 'qty' => 7],
                ],
            ],
            [
                'order_number' => 'ORD-2026-1104',
                'customer' => 'PT. Retail Globalindo',
                'marketplace' => 'Direct B2B Store',
                'days_ago' => 18,
                'items' => [
                    ['sku' => 'MRP400', 'qty' => 15],
                    ['sku' => 'MRP710', 'qty' => 10],
                ],
            ],

            // Medium Moving (Sold 30 - 60 days ago)
            [
                'order_number' => 'ORD-2026-1001',
                'customer' => 'Hendra Gunawan',
                'marketplace' => 'Shopee Official',
                'days_ago' => 35,
                'items' => [
                    ['sku' => 'MRP405', 'qty' => 3],
                    ['sku' => 'MRP830', 'qty' => 2],
                ],
            ],
            [
                'order_number' => 'ORD-2026-1002',
                'customer' => 'Maya Kartika',
                'marketplace' => 'Lazada Flagship',
                'days_ago' => 50,
                'items' => [
                    ['sku' => 'MRP405', 'qty' => 2],
                ],
            ],

            // Slow Moving (Sold 70 - 85 days ago)
            [
                'order_number' => 'ORD-2026-0901',
                'customer' => 'Agus Salim',
                'marketplace' => 'Tokopedia Mall',
                'days_ago' => 75,
                'items' => [
                    ['sku' => 'MRP830', 'qty' => 1],
                ],
            ],
        ];

        foreach ($ordersPlan as $plan) {
            $orderDate = Carbon::now()->subDays($plan['days_ago']);

            $order = Order::firstOrCreate(
                ['order_number' => $plan['order_number']],
                [
                    'customer_name' => $plan['customer'],
                    'customer_email' => strtolower(str_replace(' ', '.', $plan['customer'])) . '@example.com',
                    'customer_phone' => '0812' . rand(10000000, 99999999),
                    'marketplace' => $plan['marketplace'],
                    'status' => 'completed',
                    'shipping_status' => 'delivered',
                    'tracking_number' => 'TRACK-' . strtoupper(substr(md5($plan['order_number']), 0, 8)),
                    'courier' => $couriers[array_rand($couriers)],
                    'total_amount' => 0,
                    'shipping_address' => 'Jl. Pengiriman Logistik No. ' . rand(1, 99) . ', Jakarta',
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]
            );

            $orderTotal = 0;
            foreach ($plan['items'] as $itPlan) {
                $product = Product::where('sku', $itPlan['sku'])->first();
                if (!$product) {
                    continue;
                }

                $subtotal = $itPlan['qty'] * $product->price;
                $orderTotal += $subtotal;

                if ($order->items()->where('product_id', $product->id)->count() === 0) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_title' => $product->title,
                        'sku' => $product->sku,
                        'quantity' => $itPlan['qty'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);

                    // Record StockMovement OUT
                    StockMovement::firstOrCreate(
                        ['reference_no' => $order->order_number, 'product_id' => $product->id],
                        [
                            'warehouse_id' => $product->warehouse_id,
                            'type' => 'out',
                            'quantity' => -$itPlan['qty'],
                            'stock_before' => $product->quantity + $itPlan['qty'],
                            'stock_after' => $product->quantity,
                            'reference_no' => $order->order_number,
                            'notes' => "Pesanan Penjualan {$order->order_number} ({$plan['marketplace']})",
                            'created_by' => 'Marketplace Integration',
                            'created_at' => $orderDate,
                            'updated_at' => $orderDate,
                        ]
                    );
                }
            }

            $order->update(['total_amount' => $orderTotal]);
        }
    }
}

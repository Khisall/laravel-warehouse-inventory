<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MarketplaceListing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            [
                'name' => 'Gudang Manager',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Create Warehouses
        $warehousesData = [
            [
                'name' => 'Central Hub Warehouse',
                'code' => 'WH-CENTRAL',
                'location' => 'multiple 22',
                'manager_name' => 'Budi Santoso',
                'phone' => '+62 812-3456-7890',
                'address' => 'Kawasan Industri Pulo Gadung, Blok B No. 12, Jakarta Timur',
            ],
            [
                'name' => 'Warehouse Blok A',
                'code' => 'WH-BLOK-A',
                'location' => 'Location A: 5',
                'manager_name' => 'Andi Wijaya',
                'phone' => '+62 813-8899-1122',
                'address' => 'Gudang Sentral Blok A, Rak A-05',
            ],
            [
                'name' => 'Warehouse Blok C',
                'code' => 'WH-BLOK-C',
                'location' => 'Location C: 2',
                'manager_name' => 'Rina Marlina',
                'phone' => '+62 811-2233-4455',
                'address' => 'Gudang Sentral Blok C, Rak C-02',
            ],
            [
                'name' => 'Warehouse Blok D',
                'code' => 'WH-BLOK-D',
                'location' => 'Location D: 5',
                'manager_name' => 'Dedi Haryanto',
                'phone' => '+62 815-6677-8899',
                'address' => 'Gudang Sentral Blok D, Rak D-05',
            ],
            [
                'name' => 'Warehouse Blok B',
                'code' => 'WH-BLOK-B',
                'location' => 'Location B: 3',
                'manager_name' => 'Farhan Pratama',
                'phone' => '+62 817-4455-6677',
                'address' => 'Gudang Sentral Blok B, Rak B-03',
            ],
        ];

        $warehouses = [];
        foreach ($warehousesData as $wData) {
            $warehouses[$wData['code']] = Warehouse::updateOrCreate(['code' => $wData['code']], $wData);
        }

        // 3. Create Categories
        $categoriesData = [
            ['name' => 'Clothing', 'slug' => 'clothing', 'icon' => 'shirt', 'description' => 'Apparel, jackets, shirts, pants and outerwear'],
            ['name' => 'Boots', 'slug' => 'boots', 'icon' => 'footprints', 'description' => 'Leather boots, western boots, hiking boots'],
            ['name' => 'Socks', 'slug' => 'socks', 'icon' => 'layers', 'description' => 'Cotton socks, wool socks, athletic socks'],
            ['name' => 'Footwear', 'slug' => 'footwear', 'icon' => 'sparkles', 'description' => 'Casual shoes, running sneakers, slip-ons'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'icon' => 'watch', 'description' => 'Belts, bags, wallets, sunglasses'],
            ['name' => 'Outdoors', 'slug' => 'outdoors', 'icon' => 'compass', 'description' => 'Outdoor gear and adventure items'],
        ];

        $categories = [];
        foreach ($categoriesData as $cData) {
            $categories[$cData['name']] = Category::updateOrCreate(['slug' => $cData['slug']], $cData);
        }

        // 4. Create Products matching screenshot
        $productsData = [
            [
                'sku' => 'MRP400',
                'title' => 'Winter Jacket Leather',
                'category' => 'Clothing',
                'quantity' => 22,
                'min_stock' => 5,
                'warehouse_code' => 'WH-CENTRAL',
                'warehouse_location' => 'multiple 22',
                'price' => 223.20,
                'cost_price' => 145.00,
                'image' => 'images/products/jacket.svg',
                'variation_text' => null,
                'variations_count' => 0,
                'status_code' => 'ACCO',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Premium genuine leather winter jacket with fleece lining and heavy duty brass zippers.',
            ],
            [
                'sku' => 'MRP405',
                'title' => 'ABCD Western Boot lace up, op...',
                'category' => 'Boots',
                'quantity' => 35,
                'min_stock' => 8,
                'warehouse_code' => 'WH-BLOK-A',
                'warehouse_location' => 'Location A: 5',
                'price' => 223.20,
                'cost_price' => 130.00,
                'image' => 'images/products/boots.svg',
                'variation_text' => 'Variation (4)',
                'variations_count' => 4,
                'status_code' => '11SE',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Vintage western cut boots with durable lace-up closure and reinforced oil-resistant soles.',
            ],
            [
                'sku' => 'MRP620',
                'title' => 'XYZ White Socks',
                'category' => 'Socks',
                'quantity' => 23,
                'min_stock' => 10,
                'warehouse_code' => 'WH-CENTRAL',
                'warehouse_location' => 'multiple 22',
                'price' => 223.20,
                'cost_price' => 65.00,
                'image' => 'images/products/socks.svg',
                'variation_text' => null,
                'variations_count' => 0,
                'status_code' => 'ACCO',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Breathable combed cotton socks pack of 6 pairs with arch compression and cushioning.',
            ],
            [
                'sku' => 'MRP052',
                'title' => 'ABC Shoes & XYZ Footwear',
                'category' => 'Footwear',
                'quantity' => 12,
                'min_stock' => 5,
                'warehouse_code' => 'WH-BLOK-C',
                'warehouse_location' => 'Location C: 2',
                'price' => 223.20,
                'cost_price' => 120.00,
                'image' => 'images/products/shoes.svg',
                'variation_text' => null,
                'variations_count' => 0,
                'status_code' => 'CACO',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Lightweight everyday athletic walking and running sneakers with dual-density foam soles.',
            ],
            [
                'sku' => 'MRP406',
                'title' => 'ABCD Western Boot lace up, op...',
                'category' => 'Boots',
                'quantity' => 35,
                'min_stock' => 8,
                'warehouse_code' => 'WH-BLOK-D',
                'warehouse_location' => 'Location D: 5',
                'price' => 223.20,
                'cost_price' => 130.00,
                'image' => 'images/products/boots.svg',
                'variation_text' => 'Variation (4)',
                'variations_count' => 4,
                'status_code' => '11SE',
                'is_marketplace_listed' => false,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Stock batch allocated for regional fulfillment center D-05.',
            ],
            [
                'sku' => 'MRP053',
                'title' => 'ABC Shoes & XYZ Footwear',
                'category' => 'Footwear',
                'quantity' => 12,
                'min_stock' => 5,
                'warehouse_code' => 'WH-BLOK-C',
                'warehouse_location' => 'Location C: 2',
                'price' => 223.20,
                'cost_price' => 120.00,
                'image' => 'images/products/shoes.svg',
                'variation_text' => null,
                'variations_count' => 0,
                'status_code' => 'CACO',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Second batch allocation for retail distribution.',
            ],
            [
                'sku' => 'MRP401',
                'title' => 'Winter Jacket Leather',
                'category' => 'Clothing',
                'quantity' => 22,
                'min_stock' => 5,
                'warehouse_code' => 'WH-BLOK-A',
                'warehouse_location' => 'Location A: 5',
                'price' => 223.20,
                'cost_price' => 145.00,
                'image' => 'images/products/jacket.svg',
                'variation_text' => null,
                'variations_count' => 0,
                'status_code' => 'ACCO',
                'is_marketplace_listed' => false,
                'last_modified' => Carbon::create(2024, 8, 10, 13, 0, 0),
                'description' => 'Black edition leather winter jacket stored in Warehouse Block A.',
            ],
            [
                'sku' => 'MRP710',
                'title' => 'Thermal Windbreaker Pro',
                'category' => 'Clothing',
                'quantity' => 18,
                'min_stock' => 5,
                'warehouse_code' => 'WH-BLOK-B',
                'warehouse_location' => 'Location B: 3',
                'price' => 185.00,
                'cost_price' => 110.00,
                'image' => 'images/products/jacket.svg',
                'variation_text' => 'Variation (3)',
                'variations_count' => 3,
                'status_code' => 'ACCO',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 12, 11, 30, 0),
                'description' => 'Waterproof, breathable storm-shell jacket engineered for all outdoor conditions.',
            ],
            [
                'sku' => 'MRP830',
                'title' => 'Trail Hiking Rugged Boots',
                'category' => 'Boots',
                'quantity' => 4,
                'min_stock' => 6,
                'warehouse_code' => 'WH-BLOK-A',
                'warehouse_location' => 'Location A: 2',
                'price' => 249.99,
                'cost_price' => 160.00,
                'image' => 'images/products/boots.svg',
                'variation_text' => 'Variation (2)',
                'variations_count' => 2,
                'status_code' => '11SE',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 14, 15, 20, 0),
                'description' => 'High-traction Vibram-sole trail trekking boots with water-repellent nubuck leather.',
            ],
            [
                'sku' => 'MRP104',
                'title' => 'Aeroknit Runner Sneaker X',
                'category' => 'Footwear',
                'quantity' => 0,
                'min_stock' => 5,
                'warehouse_code' => 'WH-BLOK-D',
                'warehouse_location' => 'Location D: 1',
                'price' => 169.00,
                'cost_price' => 95.00,
                'image' => 'images/products/shoes.svg',
                'variation_text' => null,
                'variations_count' => 0,
                'status_code' => '11SE',
                'is_marketplace_listed' => true,
                'last_modified' => Carbon::create(2024, 8, 15, 9, 10, 0),
                'description' => 'Currently out of stock - restock shipment expected in 5 business days.',
            ],
        ];

        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category']] ?? null;
            $wh = $warehouses[$pData['warehouse_code']] ?? null;

            $product = Product::updateOrCreate(
                ['sku' => $pData['sku']],
                [
                    'title' => $pData['title'],
                    'category_id' => $cat?->id,
                    'warehouse_id' => $wh?->id,
                    'warehouse_location' => $pData['warehouse_location'],
                    'quantity' => $pData['quantity'],
                    'min_stock' => $pData['min_stock'],
                    'unit' => 'pcs',
                    'price' => $pData['price'],
                    'cost_price' => $pData['cost_price'],
                    'image' => $pData['image'],
                    'variation_text' => $pData['variation_text'],
                    'variations_count' => $pData['variations_count'],
                    'status_code' => $pData['status_code'],
                    'is_marketplace_listed' => $pData['is_marketplace_listed'],
                    'description' => $pData['description'],
                    'last_modified' => $pData['last_modified'],
                ]
            );

            // Create initial stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $wh?->id,
                'type' => 'in',
                'quantity' => $pData['quantity'],
                'stock_before' => 0,
                'stock_after' => $pData['quantity'],
                'reference_no' => 'RCV-' . $product->sku . '-INIT',
                'notes' => 'Initial stock intake into ' . $pData['warehouse_location'],
                'created_by' => 'Gudang Manager',
                'created_at' => $pData['last_modified'],
            ]);

            // Create marketplace listing if listed
            if ($pData['is_marketplace_listed']) {
                MarketplaceListing::create([
                    'product_id' => $product->id,
                    'channel' => in_array($pData['status_code'], ['ACCO']) ? 'Shopee Official' : 'Tokopedia Mall',
                    'channel_sku' => 'MKT-' . $product->sku,
                    'external_product_id' => 'EXT-' . rand(100000, 999999),
                    'channel_code' => $pData['status_code'],
                    'channel_price' => $pData['price'],
                    'synced_stock' => $pData['quantity'],
                    'sync_status' => 'synced',
                    'last_synced_at' => $pData['last_modified'],
                ]);
            }
        }

        // 5. Create Sample Orders
        $order1 = Order::create([
            'order_number' => 'ORD-2024-0801',
            'customer_name' => 'Alexander Pratama',
            'customer_email' => 'alex@example.com',
            'customer_phone' => '081299887766',
            'marketplace' => 'Shopee Official',
            'status' => 'completed',
            'shipping_status' => 'delivered',
            'tracking_number' => 'JNE-CGK-8921029',
            'courier' => 'JNE Regular',
            'total_amount' => 446.40,
            'shipping_address' => 'Jl. Sudirman No. 45, Kebayoran Baru, Jakarta Selatan',
            'notes' => 'Customer requested urgent delivery.',
        ]);

        $order1Product = Product::where('sku', 'MRP400')->first();
        if ($order1Product) {
            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $order1Product->id,
                'product_title' => $order1Product->title,
                'sku' => $order1Product->sku,
                'quantity' => 2,
                'unit_price' => $order1Product->price,
                'subtotal' => $order1Product->price * 2,
            ]);
        }

        $order2 = Order::create([
            'order_number' => 'ORD-2024-0802',
            'customer_name' => 'Sarah Melinda',
            'customer_email' => 'sarah.m@example.com',
            'customer_phone' => '081377665544',
            'marketplace' => 'Tokopedia Mall',
            'status' => 'processing',
            'shipping_status' => 'packing',
            'tracking_number' => 'SICEPAT-0092182',
            'courier' => 'SiCepat BEST',
            'total_amount' => 223.20,
            'shipping_address' => 'Jl. Dago Asri No. 18, Coblong, Bandung, Jawa Barat',
            'notes' => 'Please verify size before packing.',
        ]);

        $order2Product = Product::where('sku', 'MRP405')->first();
        if ($order2Product) {
            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $order2Product->id,
                'product_title' => $order2Product->title,
                'sku' => $order2Product->sku,
                'quantity' => 1,
                'unit_price' => $order2Product->price,
                'subtotal' => $order2Product->price,
            ]);
        }
    }
}

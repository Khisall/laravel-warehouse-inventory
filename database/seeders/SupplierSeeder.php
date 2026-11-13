<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-ELK-01',
                'name' => 'PT. Mitra Elektronik Indonesia',
                'contact_person' => 'Hendra Setiawan',
                'phone' => '+62 811-2233-4455',
                'email' => 'sales@mitraelektronik.co.id',
                'address' => 'Kawasan Industri Jababeka II Blok C-12, Cikarang',
                'payment_terms' => 'NET 30',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-KOMP-02',
                'name' => 'CV. Maju Bersama Komponen',
                'contact_person' => 'Dewi Lestari',
                'phone' => '+62 812-9988-7766',
                'email' => 'order@majubersama.com',
                'address' => 'Jl. Pangeran Jayakarta No. 45, Jakarta Barat',
                'payment_terms' => 'NET 14',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-IDT-03',
                'name' => 'PT. Indotech Global Distribusi',
                'contact_person' => 'Kevin Sanjaya',
                'phone' => '+62 813-4455-6677',
                'email' => 'kevin@indotechglobal.com',
                'address' => 'Ruko Grand Puri Niaga Blok B-09, Jakarta Barat',
                'payment_terms' => 'COD',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-SPN-04',
                'name' => 'PT. Nusantara Retail Parts',
                'contact_person' => 'Siti Nurhaliza',
                'phone' => '+62 817-6655-4433',
                'email' => 'supply@nusantaraparts.co.id',
                'address' => 'Kawasan Industri MM2100 Blok DD-3, Cibitung, Bekasi',
                'payment_terms' => 'NET 60',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $sData) {
            Supplier::firstOrCreate(['code' => $sData['code']], $sData);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'BWA',
                'code' => 'bwa',
                'status' => 'active',
                'thumbnail' => 'ocbc.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BNI',
                'code' => 'bni_va',
                'status' => 'active',
                'thumbnail' => 'bni.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BCA',
                'code' => 'bca_va',
                'status' => 'active',
                'thumbnail' => 'bca.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BRI',
                'code' => 'bri_va',
                'status' => 'active',
                'thumbnail' => 'bri.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mandiri',
                'code' => 'mandiri_va',
                'status' => 'active',
                'thumbnail' => 'mandiri.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}

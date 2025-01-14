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
                'code' => 'bni',
                'status' => 'active',
                'thumbnail' => 'bni.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BCA',
                'code' => 'bca',
                'status' => 'active',
                'thumbnail' => 'bca.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BRI',
                'code' => 'bri',
                'status' => 'active',
                'thumbnail' => 'bri.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mandiri',
                'code' => 'mandiri',
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

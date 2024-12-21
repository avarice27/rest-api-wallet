<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tips')->insert([
            [
                'title' => 'Cara menyimpan uang yang baik',
                'thumbnail' => 'tips1.png',
                'url' => 'https://www.cimbniaga.co.id/id/inspirasi/perencanaan/cara-menabung-yang-benar-menurut-pakar-keuangan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cara berinvestasi Emas',
                'thumbnail' => 'tips2.png',
                'url' => 'https://www.prudential.co.id/id/pulse/article/mau-investasi-emas-ketahui-terlebih-dahulu-tips-berikut-ini/',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Cara menabung',
                'thumbnail' => 'tips3.png',
                'url' => 'https://amartha.com/blog/pendana/lifestyle/sebelas-cara-menabung-di-rumah-yang-efektif/',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

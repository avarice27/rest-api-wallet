<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class OrderStatusSeeder extends Seeder
{
   public function run(): void
   {
       $statuses = [
           [
               'uuid' => Str::uuid(),
               'status' => 'pending',
               'created_at' => now(),
               'updated_at' => now(),
               'transaction_id' => null
           ],
           [
               'uuid' => Str::uuid(),
               'status' => 'success',
               'created_at' => now(),
               'updated_at' => now(),
               'transaction_id' => null
           ],
           [
               'uuid' => Str::uuid(),
               'status' => 'failed',
               'created_at' => now(),
               'updated_at' => now(),
               'transaction_id' => null
           ]
       ];

       DB::table('order_status')->insert($statuses);
   }
}

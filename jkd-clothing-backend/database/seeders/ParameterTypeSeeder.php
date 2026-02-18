<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ParameterType;

class ParameterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
   {
    $types = [
        ['name' => 'product_status', 'table_reference' => 'products'],
        ['name' => 'order_status', 'table_reference' => 'orders'],
        ['name' => 'payment_status', 'table_reference' => 'payments'],
        ['name' => 'shipping_status', 'table_reference' => 'orders'],
        ['name' => 'inventory_movement_type', 'table_reference' => 'inventory_movements'],
        ['name' => 'user_status', 'table_reference' => 'users'],
    ];

    foreach ($types as $type) {
        ParameterType::firstOrCreate($type);
    }
   }
}

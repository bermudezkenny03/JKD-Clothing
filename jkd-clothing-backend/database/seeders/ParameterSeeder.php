<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Parameter;
use App\Models\ParameterType;

class ParameterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productStatus = ParameterType::where('name', 'product_status')->first();
        $orderStatus = ParameterType::where('name', 'order_status')->first();
        $paymentStatus = ParameterType::where('name', 'payment_status')->first();
        
        $parameters = [
        // Product Status
        ['name' => 'draft', 'parameter_type_id' => $productStatus->id],
        ['name' => 'published', 'parameter_type_id' => $productStatus->id],
        ['name' => 'archived', 'parameter_type_id' => $productStatus->id],

        // Order Status
        ['name' => 'pending', 'parameter_type_id' => $orderStatus->id],
        ['name' => 'processing', 'parameter_type_id' => $orderStatus->id],
        ['name' => 'completed', 'parameter_type_id' => $orderStatus->id],
        ['name' => 'cancelled', 'parameter_type_id' => $orderStatus->id],

        // Payment Status
        ['name' => 'pending', 'parameter_type_id' => $paymentStatus->id],
        ['name' => 'paid', 'parameter_type_id' => $paymentStatus->id],
        ['name' => 'failed', 'parameter_type_id' => $paymentStatus->id],
        ['name' => 'refunded', 'parameter_type_id' => $paymentStatus->id],
    ];

    foreach ($parameters as $param) {
        Parameter::firstOrCreate($param);
    }
    }
}

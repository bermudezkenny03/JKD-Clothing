<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductStatus;

class ProductStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Draft', 'slug' => 'draft'],
            ['name' => 'Active', 'slug' => 'active'],
            ['name' => 'Inactive', 'slug' => 'inactive'],
            ['name' => 'Archived', 'slug' => 'archived'],
        ];

        foreach ($statuses as $status) {
            ProductStatus::updateOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $inventoryRole = Role::where('name', 'Inventory Manager')->first();
        $salesRole = Role::where('name', 'Sales Manager')->first();
        $customerRole = Role::where('name', 'Customer')->first();

        /*
        |--------------------------------------------------------------------------
        | USUARIOS INTERNOS JKDCLOTHING
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@jkdclothing.com',
            'password' => Hash::make('Password123!'),
            'phone' => '111111111',
            'status' => 1,
            'role_id' => $superAdminRole->id
        ]);

        User::create([
            'name' => 'Store',
            'last_name' => 'Admin',
            'email' => 'admin@jkdclothing.com',
            'password' => Hash::make('Password123!'),
            'phone' => '222222222',
            'status' => 1,
            'role_id' => $adminRole->id
        ]);

        User::create([
            'name' => 'Inventory',
            'last_name' => 'Manager',
            'email' => 'inventory@jkdclothing.com',
            'password' => Hash::make('Password123!'),
            'phone' => '333333333',
            'status' => 1,
            'role_id' => $inventoryRole->id
        ]);

        User::create([
            'name' => 'Sales',
            'last_name' => 'Manager',
            'email' => 'sales@jkdclothing.com',
            'password' => Hash::make('Password123!'),
            'phone' => '444444444',
            'status' => 1,
            'role_id' => $salesRole->id
        ]);

        /*
        |--------------------------------------------------------------------------
        | CLIENTE DE PRUEBA
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'customer@jkdclothing.com',
            'password' => Hash::make('Password123!'),
            'phone' => '555555555',
            'status' => 1,
            'role_id' => $customerRole->id
        ]);

        echo "✅ Usuarios JKDClothing creados correctamente\n";
    }
}

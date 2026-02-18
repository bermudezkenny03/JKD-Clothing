<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Module;
use App\Models\Permission;
use App\Models\RoleModulePermission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        RoleModulePermission::truncate();
        Module::truncate();
        Permission::truncate();
        Role::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | ROLES
        |--------------------------------------------------------------------------
        */

        $roles = [
            'Super Admin'       => 'Full system access',
            'Admin'             => 'Operational admin',
            'Inventory Manager' => 'Manage inventory only',
            'Sales Manager'     => 'Manage orders and sales',
            'Customer'          => 'Store customer',
        ];

        foreach ($roles as $name => $description) {
            Role::create(compact('name', 'description'));
        }

        $superAdmin       = Role::where('name', 'Super Admin')->first();
        $admin            = Role::where('name', 'Admin')->first();
        $inventoryManager = Role::where('name', 'Inventory Manager')->first();
        $salesManager     = Role::where('name', 'Sales Manager')->first();
        $customer         = Role::where('name', 'Customer')->first();

        /*
        |--------------------------------------------------------------------------
        | MODULES (PARENTS)
        |--------------------------------------------------------------------------
        */

        $dashboard = Module::create([
            'slug' => 'dashboard',
            'name' => 'Dashboard',
            'icon' => 'mdi-view-dashboard',
            'route' => '/dashboard',
            'sort_order' => 1
        ]);

        $catalog = Module::create([
            'slug' => 'catalog-management',
            'name' => 'Catalog Management',
            'icon' => 'mdi-store',
            'route' => null,
            'sort_order' => 2
        ]);

        $orderManagement = Module::create([
            'slug' => 'order-management',
            'name' => 'Order Management',
            'icon' => 'mdi-cart',
            'route' => null,
            'sort_order' => 3
        ]);

        $inventoryManagement = Module::create([
            'slug' => 'inventory-management',
            'name' => 'Inventory Management',
            'icon' => 'mdi-warehouse',
            'route' => null,
            'sort_order' => 4
        ]);

        $accessManagement = Module::create([
            'slug' => 'access-management',
            'name' => 'Access Management',
            'icon' => 'mdi-shield-account',
            'route' => null,
            'sort_order' => 5
        ]);

        /*
        |--------------------------------------------------------------------------
        | SUBMODULES
        |--------------------------------------------------------------------------
        */

        $products = Module::create([
            'slug' => 'products',
            'name' => 'Products',
            'route' => '/products',
            'parent_id' => $catalog->id
        ]);

        $categories = Module::create([
            'slug' => 'categories',
            'name' => 'Categories',
            'route' => '/categories',
            'parent_id' => $catalog->id
        ]);

        $orders = Module::create([
            'slug' => 'orders',
            'name' => 'Orders',
            'route' => '/orders',
            'parent_id' => $orderManagement->id
        ]);

        $inventory = Module::create([
            'slug' => 'inventory',
            'name' => 'Inventory',
            'route' => '/inventory',
            'parent_id' => $inventoryManagement->id
        ]);

        $users = Module::create([
            'slug' => 'users',
            'name' => 'Users',
            'route' => '/users',
            'parent_id' => $accessManagement->id
        ]);

        $rolesModule = Module::create([
            'slug' => 'roles',
            'name' => 'Roles',
            'route' => '/roles',
            'parent_id' => $accessManagement->id
        ]);

        /*
        |--------------------------------------------------------------------------
        | PERMISSIONS
        |--------------------------------------------------------------------------
        */

        $permissions = collect([
            ['name' => 'View',   'slug' => 'view'],
            ['name' => 'Create', 'slug' => 'create'],
            ['name' => 'Edit',   'slug' => 'edit'],
            ['name' => 'Delete', 'slug' => 'delete'],
        ])->map(fn($p) => Permission::create($p));

        /*
        |--------------------------------------------------------------------------
        | ASSIGN HELPER
        |--------------------------------------------------------------------------
        */

        $assign = function ($role, $modules, $permissions) {
            foreach ($modules as $module) {
                foreach ($permissions as $permission) {
                    RoleModulePermission::create([
                        'role_id' => $role->id,
                        'module_id' => $module->id,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        };

        /*
        |--------------------------------------------------------------------------
        | ASSIGN PERMISSIONS
        |--------------------------------------------------------------------------
        */

        // Super Admin → acceso total
        $assign($superAdmin, Module::all(), $permissions);

        // Admin → todo excepto roles
        $assign($admin, [
            $dashboard,
            $products,
            $categories,
            $orders,
            $inventory,
            $users
        ], $permissions);

        // Inventory Manager → dashboard + inventory
        $assign($inventoryManager, [
            $dashboard,
            $inventory
        ], $permissions);

        // Sales Manager → dashboard + orders
        $assign($salesManager, [
            $dashboard,
            $orders
        ], $permissions);

        // Customer → solo ver productos
        $assign($customer, [$products], $permissions->where('slug', 'view'));

        echo "✅ PermissionSeeder ejecutado correctamente (Clean & Pro Version)\n";
    }
}

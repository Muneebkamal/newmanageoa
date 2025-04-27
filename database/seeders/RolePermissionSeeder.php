<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view_dashboard',
            'view_my_uploads',
            'view_leads',
            'view_oa_manage_lists',
            'view_buy_list',
            'view_orders',
            'view_shipping',
            'view_locations',
            'view_users_email',
            'view_employees'
        ];

        foreach ($permissions as $permission) {
            // Check if permission already exists, if not, create it
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $managerRole = Role::create(['name' => 'Manager']);
        $buyerRole = Role::create(['name' => 'Buyer']);

        // Assign permissions to the roles
        $adminRole->givePermissionTo(Permission::all()); // Admin gets all permissions

        // Example: Give specific permissions to User
        $managerRole->givePermissionTo([
            'view_dashboard',
            'view_my_uploads',
            'view_leads',
            'view_orders',
        ]);
        $buyerRole->givePermissionTo([
            'view_dashboard',
            'view_my_uploads',
            'view_leads',
            'view_orders',
        ]);
    }
}

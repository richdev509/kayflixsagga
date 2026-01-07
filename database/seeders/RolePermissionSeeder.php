<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Video permissions
            'create-video',
            'edit-video',
            'delete-video',
            'publish-video',
            'view-video',

            // Creator permissions
            'manage-creators',
            'approve-creator',

            // User permissions
            'manage-users',
            'manage-roles',

            // Subscription permissions
            'manage-subscriptions',

            // Payout permissions
            'view-payouts',
            'manage-payouts',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $creatorRole = Role::create(['name' => 'creator']);
        $creatorRole->givePermissionTo([
            'create-video',
            'edit-video',
            'delete-video',
            'view-video',
            'view-payouts',
        ]);

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'view-video',
        ]);
    }
}


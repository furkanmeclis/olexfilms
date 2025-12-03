<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => UserRoleEnum::SUPER_ADMIN->value]);
        $centerStaffRole = Role::firstOrCreate(['name' => UserRoleEnum::CENTER_STAFF->value]);
        $dealerOwnerRole = Role::firstOrCreate(['name' => UserRoleEnum::DEALER_OWNER->value]);
        $dealerStaffRole = Role::firstOrCreate(['name' => UserRoleEnum::DEALER_STAFF->value]);

        // Update cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create initial super admin user if it doesn't exist
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '5550000000',
                'is_active' => true,
            ]
        );

        if (! $superAdmin->hasRole(UserRoleEnum::SUPER_ADMIN->value)) {
            $superAdmin->assignRole($superAdminRole);
        }
    }
}

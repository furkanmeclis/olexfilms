<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Get super admin role
        $superAdminRole = Role::firstOrCreate(['name' => UserRoleEnum::SUPER_ADMIN->value]);

        // Create super admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '5550000000',
                'is_active' => true,
                'dealer_id' => null, // Super admin için dealer_id null olmalı
            ]
        );

        // Assign role if not already assigned
        if (! $admin->hasRole(UserRoleEnum::SUPER_ADMIN->value)) {
            $admin->assignRole($superAdminRole);
        }

        // Update cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Admin kullanıcısı başarıyla oluşturuldu!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Şifre: password');
    }
}






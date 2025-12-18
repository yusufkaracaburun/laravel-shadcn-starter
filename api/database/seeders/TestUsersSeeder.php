<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::factory()
            ->systemUser()
            ->create([
                'name'       => 'Super Admin example',
                'email'      => 'super@admin.com',
                'password'   => Hash::make('password'),
                'created_at' => now()->subMonths(3),
            ]);
        $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);
        $superAdmin->syncPermissions(UserRole::SUPER_ADMIN->rolePermissions());

        // Admin
        $admin = User::factory()
            ->systemUser()
            ->create([
                'name'       => 'Admin example',
                'email'      => 'admin@demo.com',
                'password'   => Hash::make('password'),
                'created_at' => now()->subMonths(3),
            ]);
        $admin->assignRole(UserRole::ADMIN->value);
        $admin->syncPermissions(UserRole::ADMIN->rolePermissions());

        // Managers
        foreach (['manager1@demo.com', 'manager2@demo.com'] as $idx => $email) {
            $manager = User::factory()
                ->systemUser()
                ->create([
                    'name'       => 'Manager ' . ($idx + 1) . ' example',
                    'email'      => $email,
                    'password'   => Hash::make('password'),
                    'created_at' => now()->subMonths(3),
                ]);
            $manager->assignRole(UserRole::MANAGER->value);
            $manager->syncPermissions(UserRole::MANAGER->rolePermissions());
        }

        // Gewone users
        foreach (range(1, 3) as $i) {
            $user = User::factory()
                ->systemUser()
                ->create([
                    'name'       => "User {$i} example",
                    'email'      => "user{$i}@demo.com",
                    'password'   => Hash::make('password'),
                    'created_at' => now()->subMonths(3),
                ]);
            $user->assignRole(UserRole::USER->value);
            $user->syncPermissions(UserRole::USER->rolePermissions());
        }

        // Test contact-users (klanten met login)
        //        foreach (range(1, 10) as $i) {
        //            $contactUser = User::factory()->contactUser()->create();
        //            $contactUser->assignRole(UserRole::CUSTOMER->value);
        //        }
    }
}

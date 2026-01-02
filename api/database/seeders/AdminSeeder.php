<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.admin'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.admin',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('Super admin user created: admin@admin.admin');
        } else {
            $this->command->info('Super admin user already exists: admin@admin.admin');
        }
    }
}

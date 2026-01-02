<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin if not exists
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@admin.admin'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.admin',
                'password' => Hash::make('Admin123!'),
                'is_super_admin' => true,
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('Super admin created: admin@admin.admin');
        } else {
            $this->command->info('Super admin already exists: admin@admin.admin');
        }
    }
}

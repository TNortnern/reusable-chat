<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update super admin
        $admin = Admin::updateOrCreate(
            ['email' => 'admin@admin.admin'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin1234!'),
                'is_super_admin' => true,
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('Super admin created: admin@admin.admin');
        } else {
            $this->command->info('Super admin updated: admin@admin.admin');
        }
    }
}

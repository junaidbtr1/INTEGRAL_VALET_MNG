<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@btrvalet.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'is_super_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'timezone' => 'Asia/Karachi',
            ]
        );
    }
}

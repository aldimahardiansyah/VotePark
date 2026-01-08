<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Available roles:
     * - holding_admin: Can manage sites and has full access across all sites
     * - site_admin: Can manage users, units, and events within their assigned site
     * - user: Regular user with basic access
     */
    public function run(): void
    {
        // Create a Holding Admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@proapps.id'],
            [
                'name' => 'Holding Admin',
                'password' => bcrypt('password'),
                'role' => 'holding_admin',
            ]
        );

        // Update any existing users without a role to have the default 'user' role
        User::whereNull('role')->orWhere('role', '')->update(['role' => 'user']);
    }
}

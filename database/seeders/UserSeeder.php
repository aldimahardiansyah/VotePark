<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Site;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@votepark.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'site_id' => null,
            'active' => true,
        ]);

        // Get sites
        $sites = Site::all();

        foreach ($sites as $site) {
            // Create site admin
            User::create([
                'name' => $site->name . ' Admin',
                'email' => 'admin@' . $site->slug . '.com',
                'password' => Hash::make('password'),
                'role' => 'admin_site',
                'site_id' => $site->id,
                'active' => true,
            ]);

            // Create sample tenants
            for ($i = 1; $i <= 3; $i++) {
                User::create([
                    'name' => $site->name . ' Tenant ' . $i,
                    'email' => 'tenant' . $i . '@' . $site->slug . '.com',
                    'password' => Hash::make('password'),
                    'role' => 'tenant',
                    'site_id' => $site->id,
                    'unit_code' => $site->slug . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'active' => true,
                ]);
            }
        }
    }
}

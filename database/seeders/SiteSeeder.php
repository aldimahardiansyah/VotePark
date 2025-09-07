<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = [
            [
                'name' => 'Paladian Park',
                'slug' => 'paladian-park',
                'address' => 'Jl. Paladian No. 1, Jakarta',
                'phone' => '+62-21-123-4567',
                'email' => 'admin@paladianpark.com',
                'description' => 'Premium residential complex in South Jakarta',
            ],
            [
                'name' => 'Park Royale',
                'slug' => 'park-royale',
                'address' => 'Jl. Royale No. 88, Jakarta',
                'phone' => '+62-21-234-5678',
                'email' => 'admin@parkroyale.com',
                'description' => 'Luxury apartment complex with modern amenities',
            ],
            [
                'name' => 'Icon Apartemen',
                'slug' => 'icon-apartemen',
                'address' => 'Jl. Icon No. 100, Jakarta',
                'phone' => '+62-21-345-6789',
                'email' => 'admin@iconapartemen.com',
                'description' => 'Modern urban living in the heart of Jakarta',
            ],
        ];

        foreach ($sites as $siteData) {
            Site::create($siteData);
        }
    }
}

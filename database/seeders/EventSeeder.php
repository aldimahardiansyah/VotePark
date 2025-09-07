<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Site;
use App\Models\User;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::all();

        foreach ($sites as $site) {
            // Get the admin for this site
            $admin = $site->adminUsers()->first();
            
            if ($admin) {
                // Create 2 events per site
                Event::create([
                    'name' => $site->name . ' - Annual General Meeting 2024',
                    'date' => now()->addDays(5),
                    'site_id' => $site->id,
                    'created_by' => $admin->id,
                ]);
                
                Event::create([
                    'name' => $site->name . ' - Budget Approval Meeting',
                    'date' => now()->addDays(10),
                    'site_id' => $site->id,
                    'created_by' => $admin->id,
                ]);
            }
        }
    }
}

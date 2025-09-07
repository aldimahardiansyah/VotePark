<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Site;
use App\Models\User;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sites = Site::all();

        foreach ($sites as $site) {
            // Create units for each site
            for ($tower = 1; $tower <= 2; $tower++) {
                for ($unit = 1; $unit <= 10; $unit++) {
                    $unitCode = $site->slug . '-T' . $tower . '-' . str_pad($unit, 3, '0', STR_PAD_LEFT);
                    
                    $newUnit = Unit::create([
                        'code' => $unitCode,
                        'npp' => rand(50, 150) / 100, // Random NPP between 0.5 and 1.5
                        'wide' => rand(30, 100), // Random wide between 30-100 sqm
                        'tower' => 'Tower ' . $tower,
                        'site_id' => $site->id,
                    ]);

                    // Assign some units to tenants (not all units need owners)
                    if ($unit <= 6) { // First 6 units per tower get assigned
                        $tenants = User::where('role', 'tenant')
                            ->where('site_id', $site->id)
                            ->get();
                        
                        if ($tenants->count() > 0) {
                            $randomTenant = $tenants->random();
                            $newUnit->users()->attach($randomTenant->id);
                        }
                    }
                }
            }
        }
    }
}

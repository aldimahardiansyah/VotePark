<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class AttendanceEventImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            // Assuming the first row contains the headers
            if ($row[0] == 'Email') {
                continue;
            }

            $email = $row[0];

            $user = User::where('email', $email)->first();
            if (!$user) {
                Log::warning("No user found for email: $email");
                continue; // Skip if no user is found
            }
            $unit = Unit::where('user_id', $user->id)->first();

            if (!$unit) {
                Log::warning("No unit found for email: $email");
                continue; // Skip if no unit is found
            }

            Event::first()->units()->attach($email, [
                'unit_code' => $unit->code,
                'unit_id' => $unit->id,
                'event_id' => Event::first()->id,
            ]);
        }
    }
}

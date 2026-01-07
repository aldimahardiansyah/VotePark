<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ParticipantImport implements ToCollection, WithStartRow
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Skip the first row (header)
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $email = $row[0];

            if (empty($email)) {
                continue;
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                Log::warning("No user found for email: $email");
                continue;
            }

            $unit = Unit::where('user_id', $user->id)->first();
            if (!$unit) {
                Log::warning("No unit found for email: $email");
                continue;
            }

            // Check if already attached
            if ($this->event->units()->where('unit_id', $unit->id)->exists()) {
                Log::info("Unit already attached for email: $email");
                continue;
            }

            $status = $this->event->requires_approval ? 'pending' : 'approved';

            $this->event->units()->attach($unit->id, [
                'unit_code' => $unit->code,
                'status' => $status,
                'registered_email' => $email,
            ]);
        }
    }
}

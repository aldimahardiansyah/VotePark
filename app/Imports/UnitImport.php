<?php

namespace App\Imports;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UnitImport implements ToCollection, WithStartRow, WithChunkReading, WithBatchInserts
{
    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Skip the first row (header)
    }

    public function collection(Collection $rows)
    {
        set_time_limit(300); // 5 menit

        foreach ($rows as $row) {
            $user_name = $row[0];
            $user_email = $row[1];
            $unit_code = $row[2];
            $unit_npp = $row[3];
            $unit_tower = $row[4];

            // skip if unit already exist
            $unit = Unit::where('code', $unit_code)->exists();
            if ($unit) continue;

            $user = User::firstOrCreate(
                ['email' => $user_email],
                [
                    'name' => $user_name,
                    'password' => bcrypt('password'),
                ]
            );

            Unit::updateOrCreate(
                ['code' => $unit_code],
                ['user_id' => $user->id]
            );

            // get unit by unit_code
            $unit = Unit::where('code', $unit_code)->first();
            if ($unit) {
                // update unit
                $unit->update([
                    'npp' => $unit_npp,
                    'wide' => 1,
                    'tower' => $unit_tower,
                ]);
            } else {
                Log::info('Unit not found: ' . $unit_code);
            }
        }
    }

    public function batchSize(): int
    {
        return 100; // Simpan data per 100 baris
    }

    public function chunkSize(): int
    {
        return 100; // Ambil data per 100 baris dari file
    }
}

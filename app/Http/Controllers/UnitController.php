<?php

namespace App\Http\Controllers;

use App\Imports\UnitImport;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('user')->get();

        // Get the count and total NPP for each tower
        $towers = Unit::select('tower', DB::raw('count(*) as count'), DB::raw('SUM(npp) as total_npp'))
            ->groupBy('tower')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->tower => [
                    'count' => $item->count,
                    'total_npp' => $item->total_npp,
                ]];
            });

        return view('contents.unit.index', compact('units', 'towers'));
    }

    public function create()
    {
        return view('contents.unit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:units,code',
            'npp' => 'required|numeric',
            'wide' => 'required|numeric',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
        ]);

        // User first or create
        $user = User::firstOrCreate(
            ['email' => $request->input('user_email')],
            [
                'name' => $request->input('user_name'),
                'password' => bcrypt('password'), // Set a default password or handle it as per your requirement
            ]
        );

        Unit::create([
            'code' => $request->input('code'),
            'npp' => $request->input('npp'),
            'wide' => $request->input('wide'),
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Unit created successfully.');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);

        return view('contents.unit.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:10',
            'npp' => 'required|numeric',
            'wide' => 'required|numeric',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:users,email,' . $unit->user->id,
        ]);

        $unit->update($request->only('code', 'npp', 'wide'));
        $unit->user->update([
            'name' => $request->input('user_name'),
            'email' => $request->input('user_email'),
        ]);

        return redirect()->back()->with('success', 'Unit updated successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        Excel::import(new UnitImport, $file);

        return redirect()->route('unit.index')->with('success', 'Data imported successfully.');
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->user()->delete();
        $unit->delete();

        return redirect()->back()->with('success', 'Unit deleted successfully.');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'user_name');
        $sheet->setCellValue('B1', 'user_email');
        $sheet->setCellValue('C1', 'unit_code');
        $sheet->setCellValue('D1', 'unit_npp');
        $sheet->setCellValue('E1', 'unit_tower');

        // Add example row
        $sheet->setCellValue('A2', 'John Doe');
        $sheet->setCellValue('B2', 'john@example.com');
        $sheet->setCellValue('C2', 'A101');
        $sheet->setCellValue('D2', '1.5');
        $sheet->setCellValue('E2', 'A');

        $writer = new Xlsx($spreadsheet);

        $fileName = 'unit_import_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function getUnitData(Unit $unit)
    {
        return response()->json([
            'id' => $unit->id,
            'code' => $unit->code,
            'npp' => $unit->npp,
            'tower' => $unit->tower,
            'user_name' => $unit->user->name ?? '',
            'user_email' => $unit->user->email ?? '',
        ]);
    }
}

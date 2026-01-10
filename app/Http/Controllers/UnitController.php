<?php

namespace App\Http\Controllers;

use App\Imports\UnitImport;
use App\Models\Site;
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
        $user = auth()->user();

        // Filter by site for site_admin
        $query = Unit::with('user', 'site');
        if ($user->isSiteAdmin() && $user->site_id) {
            $query->where('site_id', $user->site_id);
        }

        $units = $query->orderBy('code')
        ->get();

        // Get the count and total NPP for each tower
        $towerQuery = Unit::select('tower', DB::raw('count(*) as count'), DB::raw('SUM(npp) as total_npp'));
        if ($user->isSiteAdmin() && $user->site_id) {
            $towerQuery->where('site_id', $user->site_id);
        }
        $towers = $towerQuery->groupBy('tower')
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
        $user = auth()->user();
        $sites = $user->isHoldingAdmin() ? Site::all() : collect();
        $userSiteId = $user->site_id;

        return view('contents.unit.create', compact('sites', 'userSiteId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'code' => 'required|string|max:10|unique:units,code',
            'npp' => 'required|numeric',
            'wide' => 'required|numeric',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        // User first or create
        $owner = User::firstOrCreate(
            ['email' => $request->input('user_email')],
            [
                'name' => $request->input('user_name'),
                'password' => bcrypt('password'),
            ]
        );

        $siteId = $user->isHoldingAdmin() ? $request->input('site_id') : $user->site_id;

        Unit::create([
            'code' => $request->input('code'),
            'npp' => $request->input('npp'),
            'wide' => $request->input('wide'),
            'user_id' => $owner->id,
            'site_id' => $siteId,
        ]);

        return redirect()->back()->with('success', 'Unit created successfully.');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $user = auth()->user();
        $sites = $user->isHoldingAdmin() ? Site::all() : collect();
        $userSiteId = $user->site_id;

        return view('contents.unit.edit', compact('unit', 'sites', 'userSiteId'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $user = auth()->user();

        $rules = [
            'code' => 'required|string|max:10',
            'npp' => 'required|numeric',
            'wide' => 'required|numeric',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:users,email,' . $unit->user->id,
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        $updateData = $request->only('code', 'npp', 'wide');
        if ($user->isHoldingAdmin()) {
            $updateData['site_id'] = $request->input('site_id');
        }

        $unit->update($updateData);
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
        $sheet->setCellValue('D1', 'unit_wide');
        $sheet->setCellValue('E1', 'unit_npp');
        $sheet->setCellValue('F1', 'unit_tower');

        // Add example row
        $sheet->setCellValue('A2', 'John Doe');
        $sheet->setCellValue('B2', 'john@example.com');
        $sheet->setCellValue('C2', 'A101');
        $sheet->setCellValue('D2', '40.5');
        $sheet->setCellValue('E2', '1.5');
        $sheet->setCellValue('F2', 'A');

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

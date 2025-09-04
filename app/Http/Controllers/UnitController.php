<?php

namespace App\Http\Controllers;

use App\Imports\UnitImport;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UnitController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
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
        } else {
            $units = Unit::where('site_id', $user->site_id)->with('user')->get();
            // Get the count and total NPP for each tower (site-specific)
            $towers = Unit::where('site_id', $user->site_id)
                ->select('tower', DB::raw('count(*) as count'), DB::raw('SUM(npp) as total_npp'))
                ->groupBy('tower')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->tower => [
                        'count' => $item->count,
                        'total_npp' => $item->total_npp,
                    ]];
                });
        }

        return view('contents.unit.index', compact('units', 'towers'));
    }

    public function create(Request $request)
    {
        return view('contents.unit.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'code' => 'required|string|max:10|unique:units,code',
            'npp' => 'required|numeric',
            'wide' => 'required|numeric',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
        ]);

        // User first or create
        $userModel = User::firstOrCreate(
            ['email' => $request->input('user_email')],
            [
                'name' => $request->input('user_name'),
                'password' => bcrypt('password'), // Set a default password or handle it as per your requirement
                'role' => 'tenant',
                'site_id' => $user->isSuperAdmin() ? null : $user->site_id, // Assign site for non-superadmin
            ]
        );

        Unit::create([
            'code' => $request->input('code'),
            'npp' => $request->input('npp'),
            'wide' => $request->input('wide'),
            'user_id' => $userModel->id,
            'site_id' => $user->isSuperAdmin() ? null : $user->site_id, // Assign site for non-superadmin
        ]);

        return redirect()->back()->with('success', 'Unit created successfully.');
    }

    public function edit($id, Request $request)
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
        if ($request->password !== '085779705274') {
            return redirect()->back()->with('error', 'You are not authorized to import unit.');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        Excel::import(new UnitImport, $file);

        return redirect()->route('unit.index')->with('success', 'Data imported successfully.');
    }

    public function destroy($id, Request $request)
    {
        if ($request->password !== '085779705274') {
            return redirect()->back()->with('error', 'You are not authorized to delete this unit.');
        }

        $unit = Unit::findOrFail($id);
        $unit->user()->delete();
        $unit->delete();

        return redirect()->back()->with('success', 'Unit deleted successfully.');
    }
}

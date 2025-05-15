<?php

namespace App\Http\Controllers;

use App\Imports\UnitImport;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('user')->get();

        return view('contents.unit.index', compact('units'));
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
}

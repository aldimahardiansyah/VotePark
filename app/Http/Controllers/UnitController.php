<?php

namespace App\Http\Controllers;

use App\Imports\UnitImport;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('user')->get();

        return view('contents.unit.index', compact('units'));
    }

    public function create(Request $request)
    {
        if ($request->password !== '085779705274') {
            return redirect()->back()->with('error', 'You are not authorized to edit unit.');
        }

        return view('contents.unit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:units,code',
            'npp' => 'required|numeric',
            'wide' => 'required|numeric',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255|unique:users,email',
        ]);

        $user = User::create([
            'name' => $request->input('user_name'),
            'email' => $request->input('user_email'),
            'password' => bcrypt('password'), // Set a default password or handle it as per your requirement
        ]);

        Unit::create([
            'code' => $request->input('code'),
            'npp' => $request->input('npp'),
            'wide' => $request->input('wide'),
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Unit created successfully.');
    }

    public function edit($id, Request $request)
    {
        if ($request->password !== '085779705274') {
            return redirect()->back()->with('error', 'You are not authorized to edit unit.');
        }

        $unit = Unit::findOrFail($id);

        return view('contents.unit.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        if ($request->password !== '085779705274') {
            return redirect()->back()->with('error', 'You are not authorized to edit unit.');
        }

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

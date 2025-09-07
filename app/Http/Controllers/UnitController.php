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
            $units = Unit::with('users')->get();
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
            $units = Unit::where('site_id', $user->site_id)->with('users')->get();
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
            'code' => 'required|string|max:50|unique:units,code',
            'npp' => 'required|numeric|min:0',
            'wide' => 'required|numeric|min:0',
            'tower' => 'required|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:255',
        ]);

        // Create the unit first
        $unit = Unit::create([
            'code' => $request->input('code'),
            'npp' => $request->input('npp'),
            'wide' => $request->input('wide'),
            'tower' => $request->input('tower'),
            'site_id' => $user->isSuperAdmin() ? 1 : $user->site_id, // Default to site 1 for superadmin or user's site
        ]);

        // If user details provided, create/find user and attach to unit
        if ($request->filled('user_name') && $request->filled('user_email')) {
            $userModel = User::firstOrCreate(
                ['email' => $request->input('user_email')],
                [
                    'name' => $request->input('user_name'),
                    'password' => bcrypt('password'), // Set a default password
                    'role' => 'tenant',
                    'site_id' => $user->isSuperAdmin() ? 1 : $user->site_id,
                    'active' => true,
                ]
            );

            // Attach user to unit
            $unit->users()->attach($userModel->id);
        }

        return redirect()->route('unit.index')->with('success', 'Unit created successfully.');
    }

    public function edit($id, Request $request)
    {
        $unit = Unit::with('users')->findOrFail($id);
        
        // Check if user can edit this unit (site-based access control)
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $unit->site_id !== $user->site_id) {
            abort(403, 'Unauthorized access to this unit.');
        }
        
        return view('contents.unit.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::with('users')->findOrFail($id);
        
        // Check if user can edit this unit (site-based access control)
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $unit->site_id !== $user->site_id) {
            abort(403, 'Unauthorized access to this unit.');
        }

        $request->validate([
            'code' => 'required|string|max:50|unique:units,code,' . $id,
            'npp' => 'required|numeric|min:0',
            'wide' => 'required|numeric|min:0',
            'tower' => 'required|string|max:255',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:255',
        ]);

        // Update unit details
        $unit->update($request->only('code', 'npp', 'wide', 'tower'));

        // Handle user assignment
        if ($request->filled('user_name') && $request->filled('user_email')) {
            // Check if email is unique except for current unit owners
            $existingUser = User::where('email', $request->input('user_email'))
                ->whereNotIn('id', $unit->users->pluck('id'))
                ->first();
            
            if ($existingUser) {
                // If user exists, just attach to unit
                $unit->users()->syncWithoutDetaching([$existingUser->id]);
            } else {
                // If this is a new email, create new user or update existing one
                $primaryOwner = $unit->users()->first();
                if ($primaryOwner) {
                    $primaryOwner->update([
                        'name' => $request->input('user_name'),
                        'email' => $request->input('user_email'),
                    ]);
                } else {
                    // Create new user and attach
                    $newUser = User::create([
                        'name' => $request->input('user_name'),
                        'email' => $request->input('user_email'),
                        'password' => bcrypt('password'),
                        'role' => 'tenant',
                        'site_id' => $unit->site_id,
                        'active' => true,
                    ]);
                    $unit->users()->attach($newUser->id);
                }
            }
        }

        return redirect()->route('unit.index')->with('success', 'Unit updated successfully.');
    }

    public function import(Request $request)
    {
        // Removed password requirement as requested
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');

        try {
            Excel::import(new UnitImport, $file);
            return redirect()->route('unit.index')->with('success', 'Data imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('unit.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function destroy($id, Request $request)
    {
        // Removed password requirement as requested
        $unit = Unit::findOrFail($id);
        
        // Check if user can delete this unit (site-based access control)
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $unit->site_id !== $user->site_id) {
            abort(403, 'Unauthorized access to this unit.');
        }
        
        // Detach all users from this unit
        $unit->users()->detach();
        
        // Delete the unit
        $unit->delete();

        return redirect()->route('unit.index')->with('success', 'Unit deleted successfully.');
    }
}

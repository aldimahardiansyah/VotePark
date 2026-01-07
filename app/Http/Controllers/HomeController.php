<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        
        // For regular users, show their units
        if ($user->role === 'user') {
            $units = Unit::where('user_id', $user->id)->with('event')->get();
            return view('home', compact('user', 'units'));
        }
        
        // For admin users, redirect to unit index
        return redirect()->route('unit.index');
    }
}

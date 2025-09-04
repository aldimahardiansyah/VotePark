<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes that require authentication and specific roles
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Routes for admin_site and superadmin
    Route::middleware(['role:superadmin,admin_site'])->group(function () {
        Route::resource('unit', UnitController::class);
        Route::post('unit/import', [UnitController::class, 'import'])->name('unit.import');
        Route::delete('event/remove-participant', [EventController::class, 'removeParticipant'])->name('event.remove-participant');
        Route::resource('event', EventController::class);
        Route::post('event/import-attendance', [EventController::class, 'importAttendance'])->name('event.import-attendance');
        Route::post('event/add-participant', [EventController::class, 'addParticipant'])->name('event.add-participant');
        Route::post('vote/import', [VoteController::class, 'import'])->name('vote.import');
        Route::get('vote/{vote}', [VoteController::class, 'show'])->name('vote.show');
        Route::delete('vote/{vote}', [VoteController::class, 'destroy'])->name('vote.destroy');
        Route::resource('question', QuestionController::class);
    });
    
    // Routes that all authenticated users can access
    // Add voting routes here
});

require __DIR__.'/auth.php';

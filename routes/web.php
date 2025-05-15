<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('unit.index'));
});

Route::resource('unit', UnitController::class);
Route::post('unit/import', [UnitController::class, 'import'])->name('unit.import');
Route::delete('event/remove-participant', [EventController::class, 'removeParticipant'])->name('event.remove-participant');
Route::resource('event', EventController::class);
Route::post('event/import-attendance', [EventController::class, 'importAttendance'])->name('event.import-attendance');
Route::post('event/add-participant', [EventController::class, 'addParticipant'])->name('event.add-participant');
Route::post('vote/import', [VoteController::class, 'import'])->name('vote.import');
Route::get('vote/{vote}', [VoteController::class, 'show'])->name('vote.show');
Route::delete('vote/{vote}', [VoteController::class, 'destroy'])->name('vote.destroy');

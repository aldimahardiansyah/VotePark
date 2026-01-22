<?php

use App\Http\Controllers\AnonymousVotingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('login'));
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::resource('unit', UnitController::class);
    Route::post('unit/import', [UnitController::class, 'import'])->name('unit.import');
    Route::get('unit/template/download', [UnitController::class, 'downloadTemplate'])->name('unit.template.download');

    Route::delete('event/remove-participant', [EventController::class, 'removeParticipant'])->name('event.remove-participant');
    Route::resource('event', EventController::class);
    Route::post('event/import-participant', [EventController::class, 'importParticipant'])->name('event.import-participant');
    Route::get('event/{event}/participant-template/download', [EventController::class, 'downloadParticipantTemplate'])->name('event.participant-template.download');
    Route::get('event/{event}/export-participants', [EventController::class, 'exportParticipants'])->name('event.export-participants');
    Route::post('event/add-participant', [EventController::class, 'addParticipant'])->name('event.add-participant');
    Route::post('event/approve-participant', [EventController::class, 'approveParticipant'])->name('event.approve-participant');
    Route::post('event/reject-participant', [EventController::class, 'rejectParticipant'])->name('event.reject-participant');
    Route::get('event/{event}/rejected-participants', [EventController::class, 'rejectedParticipants'])->name('event.rejected-participants');

    Route::post('vote/import', [VoteController::class, 'import'])->name('vote.import');
    Route::get('vote/{vote}', [VoteController::class, 'show'])->name('vote.show');
    Route::delete('vote/{vote}', [VoteController::class, 'destroy'])->name('vote.destroy');
    Route::resource('question', QuestionController::class);

    // Anonymous Voting routes
    Route::resource('anonymous-voting', AnonymousVotingController::class);
    Route::post('anonymous-voting/{votingSession}/add-candidate', [AnonymousVotingController::class, 'addCandidate'])->name('anonymous-voting.add-candidate');
    Route::put('anonymous-voting/candidate/{candidate}', [AnonymousVotingController::class, 'updateCandidate'])->name('anonymous-voting.update-candidate');
    Route::delete('anonymous-voting/candidate/{candidate}', [AnonymousVotingController::class, 'deleteCandidate'])->name('anonymous-voting.delete-candidate');
    Route::post('anonymous-voting/{votingSession}/record-ballot', [AnonymousVotingController::class, 'recordBallot'])->name('anonymous-voting.record-ballot');
    Route::delete('anonymous-voting/ballot/{ballot}', [AnonymousVotingController::class, 'deleteBallot'])->name('anonymous-voting.delete-ballot');
});

// Holding Admin routes - require holding_admin role
Route::middleware(['auth', 'holding_admin'])->group(function () {
    Route::resource('site', SiteController::class);
});

// Public routes for presentation and registration
Route::get('event/{event}/presentation', [EventController::class, 'presentation'])->name('event.presentation');
Route::get('event/{event}/register', [EventController::class, 'registerForm'])->name('event.register');
Route::post('event/{event}/register', [EventController::class, 'registerParticipant'])->name('event.register.submit');
Route::get('api/units/{unit}', [UnitController::class, 'getUnitData'])->name('api.unit.data');

// Anonymous voting presentation (public)
Route::get('anonymous-voting/{votingSession}/presentation', [AnonymousVotingController::class, 'presentation'])->name('anonymous-voting.presentation');

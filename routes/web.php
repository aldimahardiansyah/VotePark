<?php

use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('unit.index'));
});

Route::resource('unit', UnitController::class);
Route::post('unit/import', [UnitController::class, 'import'])->name('unit.import');

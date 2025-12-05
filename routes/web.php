<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DeadlineController;
use App\Http\Controllers\PatenteController;
use App\Http\Controllers\RevisioneController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/vehicles/{vehicle}/detail', [VehicleDetailController::class, 'show'])->name('vehicles.detail');
    
    // Pluralizzazione in Inglese
    // Perché? Perché Laravel usa una libreria di pluralizzazione inglese (Str::singular()) che conosce le regole inglesi.
    // - deadlines → deadline (rimuove la 's')
    // - vehicles → vehicle (rimuove la 's')
    // Quindi per deadlines, Laravel automaticamente usa {deadline} come parametro, stessa cosa per vehicles.
    // Per patenti e revisioni, dobbiamo specificarlo manualmente, perchè italiano non segue le regole inglesi.
    // La libreria di pluralizzazione di Laravel non conosce l'italiano. Non sa che revisioni → revisione.
    
    // ROUTE RESOURCE
    Route::resource('vehicles', VehicleController::class);
    Route::resource('patenti', PatenteController::class)->parameters([
        'patenti' => 'patente'
    ]);
    Route::resource('deadlines', DeadlineController::class);
    Route::patch('/deadlines/{deadline}/toggle-pagato', [DeadlineController::class, 'togglePagato'])->name('deadlines.togglePagato');
    Route::resource('revisioni', RevisioneController::class)->parameters([
        'revisioni' => 'revisione'
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
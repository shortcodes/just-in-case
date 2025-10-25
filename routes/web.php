<?php

use App\Http\Controllers\CustodianshipController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/custodianships', [CustodianshipController::class, 'index'])->name('custodianships.index');
    Route::get('/custodianships/{custodianship}', [CustodianshipController::class, 'show'])->name('custodianships.show');
    Route::get('/custodianships/{custodianship}/edit', [CustodianshipController::class, 'edit'])->name('custodianships.edit');
    Route::patch('/custodianships/{custodianship}', [CustodianshipController::class, 'update'])->name('custodianships.update');
    Route::delete('/custodianships/{custodianship}', [CustodianshipController::class, 'destroy'])->name('custodianships.destroy');
    Route::post('/custodianships/{custodianship}/reset', [CustodianshipController::class, 'reset'])->name('custodianships.reset');
});

require __DIR__.'/auth.php';

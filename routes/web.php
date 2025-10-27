<?php

use App\Http\Controllers\ActivateCustodianshipController;
use App\Http\Controllers\CustodianshipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetCustodianshipController;
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
    Route::get('/custodianships/create', [CustodianshipController::class, 'create'])->name('custodianships.create');
    Route::post('/custodianships', [CustodianshipController::class, 'store'])->name('custodianships.store');
    Route::get('/custodianships/{custodianship}', [CustodianshipController::class, 'show'])->name('custodianships.show');
    Route::get('/custodianships/{custodianship}/edit', [CustodianshipController::class, 'edit'])->name('custodianships.edit');
    Route::patch('/custodianships/{custodianship}', [CustodianshipController::class, 'update'])->name('custodianships.update');
    Route::delete('/custodianships/{custodianship}', [CustodianshipController::class, 'destroy'])->name('custodianships.destroy');
    Route::post('/custodianships/{custodianship}/reset', ResetCustodianshipController::class)->name('custodianships.reset');
    Route::post('/custodianships/{custodianship}/activate', ActivateCustodianshipController::class)->name('custodianships.activate');
});

require __DIR__.'/auth.php';

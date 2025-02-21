<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('layouts.app');
// });

Route::controller(AuthenticateController::class)->group(function () {
    Route::get('/', 'index')->name('login')->middleware('guest');
    Route::post('/', 'authenticate')->name('auth')->middleware('guest');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);

    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/absensi/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
});

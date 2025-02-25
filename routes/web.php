<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\GuruAbsensiController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\GuruEvaluasiController;
use App\Http\Controllers\GuruProfileController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckRole;
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
    // Routes untuk Admin
    Route::middleware('checkRole:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

        Route::resource('users', UserController::class);
        Route::get('/users/{id}/qr-code', [UserController::class, 'downloadQrCode'])->name('users.qr-code');

        Route::get('/absensi', [AbsensiController::class, 'absensiList'])->name('absensi.index');
        Route::post('/inputKerapian', [AbsensiController::class, 'inputKerapian'])->name('absensi.inputKerapian');

        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
        Route::post('/evaluasi', [EvaluasiController::class, 'store'])->name('evaluasi.store');
    });

    // Routes untuk Guru
    Route::middleware('checkRole:guru')->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [GuruProfileController::class, 'index'])->name('profile');
        Route::get('/absensi', [GuruAbsensiController::class, 'index'])->name('absensi');
        Route::get('/evaluasi', [GuruEvaluasiController::class, 'index'])->name('evaluasi');
    });

    // Route umum
    Route::get('/scan', [AbsensiController::class, 'index'])->name('absensi.scan.index');
    Route::post('/absensi/scan', [AbsensiController::class, 'scan'])->name('absensi.scan');
});

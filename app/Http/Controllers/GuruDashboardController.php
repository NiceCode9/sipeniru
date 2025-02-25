<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Evaluasi;
use Carbon\Carbon;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Statistik Absensi
        $monthlyAbsensi = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->get();

        $totalHadir = $monthlyAbsensi->count();
        $totalTerlambat = $monthlyAbsensi->filter(function ($absensi) {
            return Carbon::parse($absensi->check_in)->format('H:i:s') > '07:30:00';
        })->count();

        // Evaluasi Terkini
        $lastEvaluation = Evaluasi::where('user_id', $user->id)
            ->latest()
            ->first();

        // Absensi Hari Ini
        $todayAttendance = Absensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        return view('guru.dashboard', compact(
            'totalHadir',
            'totalTerlambat',
            'lastEvaluation',
            'todayAttendance'
        ));
    }
}

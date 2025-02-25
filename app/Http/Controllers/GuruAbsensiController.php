<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuruAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::where('user_id', auth()->id());

        // Filter rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $absensi = $query->orderBy('tanggal', 'desc')->paginate(10);

        // Statistik
        $bulanIni = Carbon::now();
        $monthlyStats = $query->whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->get();

        $totalHadir = $monthlyStats->count();
        $totalTerlambat = $monthlyStats->filter(function ($a) {
            return Carbon::parse($a->check_in)->format('H:i:s') > '07:30:00';
        })->count();

        return view('guru.absensi', compact('absensi', 'totalHadir', 'totalTerlambat'));
    }
}

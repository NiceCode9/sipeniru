<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GuruAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Absensi::where('user_id', $userId);

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

    public function uploadFotoAttribut(Request $request, $id)
    {
        $request->validate([
            'foto_attribut' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $userId = Auth::id();
        $absensi = Absensi::where('user_id', $userId)->where('id', $id)->firstOrFail();

        if ($request->hasFile('foto_attribut')) {
            $path = $request->file('foto_attribut')->store('foto_attribut', 'public');
            $absensi->path = $path;
            $absensi->save();
        }

        return redirect()->back()->with('success', 'Foto attribut berhasil diunggah.');
    }

    public function hapusFotoAttribut(Request $request, $id)
    {
        $userId = Auth::id();
        $absensi = Absensi::where('user_id', $userId)->where('id', $id)->firstOrFail();
        if ($absensi->path) {
            Storage::disk('public')->delete($absensi->path);
            $absensi->path = null;
            $absensi->save();
        }
        return redirect()->back()->with('success', 'Foto attribut berhasil dihapus.');
    }
}

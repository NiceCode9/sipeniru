<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use Illuminate\Http\Request;

class GuruEvaluasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluasi::where('user_id', auth()->id());

        // Filter rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $evaluations = $query->latest()->paginate(10);

        // Statistik evaluasi
        $averages = $query->selectRaw('
            AVG(presentasi_absensi) as avg_absensi,
            AVG(score_kerapian) as avg_kerapian,
            AVG(score_akhir) as avg_score
        ')->first();

        return view('guru.evaluasi', compact('evaluations', 'averages'));
    }
}

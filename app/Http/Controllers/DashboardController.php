<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Evaluasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\EvaluationExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluasi::query();

        // Apply date filters
        switch ($request->get('period')) {
            case 'semester':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now();
                if (Carbon::now()->month > 6) {
                    $start->addMonths(6);
                }
                $query->whereBetween('created_at', [$start, $end]);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                if ($request->filled(['start_date', 'end_date'])) {
                    $query->whereBetween('created_at', [
                        $request->start_date,
                        $request->end_date
                    ]);
                }
                break;
        }

        // Statistics
        $totalGuru = User::where('role', 'guru')->count();
        $totalEvaluasi = $query->count();
        $avgKehadiran = $query->avg('presentasi_absensi');
        $avgNilaiAkhir = $query->avg('score_akhir');

        // Predikat distribution - separate query
        $predikatCount = (clone $query)->select('predikat')
            ->selectRaw('count(*) as total')
            ->groupBy('predikat')
            ->get();

        $predikatLabels = $predikatCount->pluck('predikat');
        $predikatData = $predikatCount->pluck('total');

        // Monthly trend data - separate query
        $monthlyData = (clone $query)->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('AVG(score_akhir) as average')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // $monthLabels = $monthlyData->pluck('month')->map(function ($month) {
        //     return Carbon::createFromFormat('Y-m', $month)->format('M Y');
        // });
        $monthLabels = $monthlyData->pluck('month')->map(function ($month) {
            return Carbon::createFromFormat('Y-m', $month)
                ->locale('id')->translatedFormat('M Y');
        });
        $monthlyAverages = $monthlyData->pluck('average');

        // Top performers - separate query without grouping
        $topPerformers = (clone $query)->with('user')
            ->selectRaw('user_id, MAX(score_akhir) as score_akhir, MAX(predikat) as predikat')
            ->groupBy('user_id')
            ->orderBy('score_akhir', 'desc')
            ->take(5)
            ->get();

        $bottomPerformers = (clone $query)->with('user')
            ->selectRaw('user_id, MIN(score_akhir) as score_akhir, MIN(predikat) as predikat')
            ->groupBy('user_id')
            ->orderBy('score_akhir', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalGuru',
            'totalEvaluasi',
            'avgKehadiran',
            'avgNilaiAkhir',
            'predikatLabels',
            'predikatData',
            'monthLabels',
            'monthlyAverages',
            'topPerformers',
            'bottomPerformers',
        ));
    }

    public function export(Request $request)
    {
        return Excel::download(new EvaluationExport($request), 'evaluasi.xlsx');
    }
}

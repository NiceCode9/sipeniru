<?php

namespace App\Http\Controllers;

use App\Models\Evaluasi;
use App\Models\User;
use App\Services\FuzzyEvaluationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluasiController extends Controller
{
    private $fuzzyService;

    public function __construct(FuzzyEvaluationService $fuzzyService)
    {
        $this->fuzzyService = $fuzzyService;
    }

    // public function index(Request $request)
    // {
    //     $teachers = User::where('role', 'guru')->get();

    //     $query = Evaluasi::with('user');

    //     // Filter guru
    //     if ($request->filled('teacher_id')) {
    //         $query->where('user_id', $request->teacher_id);
    //     }

    //     // Filter rentang tanggal
    //     if ($request->filled('filter_start_date') && $request->filled('filter_end_date')) {
    //         $query->where(function ($q) use ($request) {
    //             $q->whereBetween('start_date', [$request->filter_start_date, $request->filter_end_date])
    //                 ->orWhereBetween('end_date', [$request->filter_start_date, $request->filter_end_date]);
    //         });
    //     }

    //     $evaluations = $query->latest()->paginate(10);

    //     return view('admin.evaluasi', compact('teachers', 'evaluations'));
    // }
    // EvaluasiController.php
    public function index(Request $request)
    {
        $teachers = User::where('role', 'guru')->get();

        $query = Evaluasi::with('user');

        // Filter guru
        if ($request->filled('teacher_id')) {
            $query->where('user_id', $request->teacher_id);
        }

        // Filter rentang tanggal
        if ($request->filled('filter_start_date') && $request->filled('filter_end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->filter_start_date, $request->filter_end_date])
                    ->orWhereBetween('end_date', [$request->filter_start_date, $request->filter_end_date]);
            });
        }

        $evaluations = $query->latest()->paginate(10);

        // Data untuk chart
        $chartData = null;
        if ($request->filled('teacher_id') || ($request->filled('filter_start_date') && $request->filled('filter_end_date'))) {
            $chartData = Evaluasi::query()
                ->when($request->filled('teacher_id'), function ($q) use ($request) {
                    $q->where('user_id', $request->teacher_id);
                })
                ->when($request->filled('filter_start_date') && $request->filled('filter_end_date'), function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->whereBetween('start_date', [$request->filter_start_date, $request->filter_end_date])
                            ->orWhereBetween('end_date', [$request->filter_start_date, $request->filter_end_date]);
                    });
                })
                ->select('predikat', DB::raw('count(*) as total'))
                ->groupBy('predikat')
                ->get()
                ->pluck('total', 'predikat')
                ->toArray();
        }

        return view('admin.evaluasi', compact('teachers', 'evaluations', 'chartData'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $user = User::find($validated['user_id']);

        // Hitung persentase kehadiran
        $totalDays = Carbon::parse($validated['start_date'])
            ->diffInDaysFiltered(function (Carbon $date) {
                return $date->isWeekday();
            }, $validated['end_date']);

        $absensi = $user->absensi()
            ->whereBetween('tanggal', [$validated['start_date'], $validated['end_date']])
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get();

        $absensiHari = $absensi->count();
        $persentaseAbsensi = ($absensiHari / $totalDays) * 100;

        // Hitung rata-rata nilai kerapian
        $rataKerapian = $absensi->avg('nilai_kerapian') ?? 0;

        // Evaluasi menggunakan Fuzzy Logic
        $scoreAkhir = $this->fuzzyService->evaluasiGuru(
            $persentaseAbsensi,
            $rataKerapian
        );

        // Tentukan predikat berdasarkan score akhir
        $predikat = 'KURANG';
        if ($scoreAkhir >= 90) {
            $predikat = 'SANGAT BAIK';
        } elseif ($scoreAkhir >= 75) {
            $predikat = 'BAIK';
        } elseif ($scoreAkhir >= 60) {
            $predikat = 'CUKUP';
        }

        // Simpan hasil evaluasi
        Evaluasi::create([
            'user_id' => $validated['user_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'presentasi_absensi' => $persentaseAbsensi,
            'score_kerapian' => $rataKerapian,
            'score_akhir' => $scoreAkhir,
            'predikat' => $predikat
        ]);

        // Simpan hasil perhitungan ke session
        session()->flash('evaluation_results', [
            'teacher_name' => $user->name,
            'presentasi_absensi' => number_format($persentaseAbsensi, 2),
            'score_kerapian' => number_format($rataKerapian, 2),
            'score_akhir' => number_format($scoreAkhir, 2),
            'predikat' => $predikat
        ]);

        return back()->with('success', 'Evaluasi berhasil disimpan');
    }
}

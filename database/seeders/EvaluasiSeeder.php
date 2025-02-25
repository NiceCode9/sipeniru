<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Evaluasi;
use App\Services\FuzzyEvaluationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EvaluasiSeeder extends Seeder
{
    public function run(): void
    {
        $fuzzyService = new FuzzyEvaluationService();
        $users = User::where('role', 'guru')->get();
        $startDate = Carbon::now()->subMonths(6);

        foreach ($users as $user) {
            // Buat 6 evaluasi bulanan untuk setiap guru
            for ($i = 0; $i < 6; $i++) {
                $periodStart = $startDate->copy()->addMonths($i);
                $periodEnd = $periodStart->copy()->endOfMonth();

                // Hitung total hari kerja
                $totalDays = $periodStart->diffInDaysFiltered(function (Carbon $date) {
                    return $date->isWeekday();
                }, $periodEnd);

                // Ambil data absensi
                $absensi = $user->absensi()
                    ->whereBetween('tanggal', [$periodStart, $periodEnd])
                    ->whereNotNull('check_in')
                    ->whereNotNull('check_out')
                    ->get();

                $absensiHari = $absensi->count();
                $persentaseAbsensi = ($absensiHari / $totalDays) * 100;
                $rataKerapian = $absensi->avg('nilai_kerapian') ?? 0;

                // Evaluasi menggunakan Fuzzy Logic
                $scoreAkhir = $fuzzyService->evaluasiGuru(
                    $persentaseAbsensi,
                    $rataKerapian
                );

                // Tentukan predikat
                $predikat = 'KURANG';
                if ($scoreAkhir >= 90) {
                    $predikat = 'SANGAT BAIK';
                } elseif ($scoreAkhir >= 75) {
                    $predikat = 'BAIK';
                } elseif ($scoreAkhir >= 60) {
                    $predikat = 'CUKUP';
                }

                // Simpan evaluasi
                Evaluasi::create([
                    'user_id' => $user->id,
                    'start_date' => $periodStart,
                    'end_date' => $periodEnd,
                    'presentasi_absensi' => $persentaseAbsensi,
                    'score_kerapian' => $rataKerapian,
                    'score_akhir' => $scoreAkhir,
                    'predikat' => $predikat,
                    'created_at' => $periodEnd
                ]);
            }
        }
    }
}

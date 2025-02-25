<?php

namespace Database\Seeders;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AbsensiSeeder extends Seeder
{
    public function run(): void
    {
        $users = range(2, 11); // ID guru dari 2-11
        $today = Carbon::today();

        foreach ($users as $userId) {
            // Tentukan karakteristik kehadiran untuk setiap guru
            $attendanceRate = rand(75, 98) / 100; // Tingkat kehadiran 75-98%
            $lateRate = rand(5, 20) / 100; // Tingkat keterlambatan 5-20%

            // Loop untuk 60 hari ke belakang
            for ($i = 0; $i < 60; $i++) {
                $date = $today->copy()->subDays($i);

                // Skip hari Sabtu dan Minggu
                if ($date->isWeekend()) {
                    continue;
                }

                // Tentukan apakah guru hadir hari ini berdasarkan attendance rate
                if (rand(1, 100) <= ($attendanceRate * 100)) {
                    // Guru hadir

                    // Tentukan apakah guru terlambat
                    $isLate = rand(1, 100) <= ($lateRate * 100);

                    // Generate check-in time
                    if ($isLate) {
                        $checkIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                    } else {
                        $checkIn = $date->copy()->setTime(7, rand(0, 30));
                    }

                    // Generate check-out time (beberapa guru pulang lebih awal/lebih lambat)
                    $earlyLeaveChance = rand(1, 100);
                    if ($earlyLeaveChance <= 10) {
                        $checkOut = $date->copy()->setTime(rand(14, 15), rand(0, 59));
                    } elseif ($earlyLeaveChance >= 90) {
                        $checkOut = $date->copy()->setTime(rand(17, 18), rand(0, 59));
                    } else {
                        $checkOut = $date->copy()->setTime(16, rand(0, 59));
                    }

                    // Nilai kerapian bervariasi per guru
                    $baseKerapian = rand(70, 90); // Basis nilai kerapian per guru
                    $dailyVariation = rand(-10, 10); // Variasi harian
                    $nilaiKerapian = min(100, max(60, $baseKerapian + $dailyVariation)) / 10;

                    Absensi::create([
                        'user_id' => $userId,
                        'tanggal' => $date->format('Y-m-d'),
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'nilai_kerapian' => $nilaiKerapian,
                    ]);
                }
            }
        }
    }
}

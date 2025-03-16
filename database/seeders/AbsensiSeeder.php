<?php

namespace Database\Seeders;

use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AbsensiSeeder extends Seeder
{
    public function run(): void
    {
        $users = range(2, 11);
        $today = Carbon::today();

        $kerapianOptions = ['Disiplin', 'Kurang Disiplin', 'Tidak Disiplin'];
        $kelengkapanOptions = ['Lengkap', 'Kurang Lengkap', 'Tidak Lengkap'];

        foreach ($users as $userId) {
            $attendanceRate = rand(75, 98) / 100;
            $lateRate = rand(5, 20) / 100;

            // Base tendency for each teacher

            for ($i = 0; $i < 60; $i++) {
                $kerapianSeragam = fake()->randomElement($kerapianOptions);
                $kelengkapanAtribut = fake()->randomElement($kelengkapanOptions);
                $date = $today->copy()->subDays($i);

                if ($date->isWeekend()) {
                    continue;
                }

                if (rand(1, 100) <= ($attendanceRate * 100)) {
                    $isLate = rand(1, 100) <= ($lateRate * 100);

                    // Generate check times like before
                    if ($isLate) {
                        $checkIn = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                    } else {
                        $checkIn = $date->copy()->setTime(7, rand(0, 30));
                    }

                    $earlyLeaveChance = rand(1, 100);
                    if ($earlyLeaveChance <= 10) {
                        $checkOut = $date->copy()->setTime(rand(14, 15), rand(0, 59));
                    } elseif ($earlyLeaveChance >= 90) {
                        $checkOut = $date->copy()->setTime(rand(17, 18), rand(0, 59));
                    } else {
                        $checkOut = $date->copy()->setTime(16, rand(0, 59));
                    }

                    // Calculate nilai_kerapian based on the conversion logic
                    $nilaiKerapian = $this->hitungNilaiKerapian($kerapianSeragam, $kelengkapanAtribut);

                    Absensi::create([
                        'user_id' => $userId,
                        'tanggal' => $date->format('Y-m-d'),
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'kerapian_seragam' => $kerapianSeragam,
                        'kelengkapan_atribut' => $kelengkapanAtribut,
                        'nilai_kerapian' => $nilaiKerapian
                    ]);
                }
            }
        }
    }

    private function hitungNilaiKerapian($kerapianSeragam, $kelengkapanAtribut)
    {
        $nilaiKerapianSeragam = $this->konversiKerapianSeragam($kerapianSeragam);
        $nilaiKelengkapanAtribut = $this->konversiKelengkapanAtribut($kelengkapanAtribut);
        return ($nilaiKerapianSeragam + $nilaiKelengkapanAtribut) / 2;
    }

    private function konversiKerapianSeragam($nilai)
    {
        switch ($nilai) {
            case 'Disiplin':
                return 10;
            case 'Kurang Disiplin':
                return 6;
            case 'Tidak Disiplin':
                return 3;
            default:
                return 0;
        }
    }

    private function konversiKelengkapanAtribut($nilai)
    {
        switch ($nilai) {
            case 'Lengkap':
                return 10;
            case 'Kurang Lengkap':
                return 6;
            case 'Tidak Lengkap':
                return 3;
            default:
                return 0;
        }
    }
}

<?php

namespace App\Services;


class FuzzyEvaluationService
{
    private function absensi($persentase)
    {
        if ($persentase >= 90) {
            return ['BAIK' => 1, 'CUKUP' => 0, 'KURANG' => 0];
        } elseif ($persentase >= 70) {
            return [
                'BAIK' => ($persentase - 70) / 20,
                'CUKUP' => (90 - $persentase) / 20,
                'KURANG' => 0
            ];
        } elseif ($persentase >= 50) {
            return [
                'BAIK' => 0,
                'CUKUP' => ($persentase - 50) / 20,
                'KURANG' => (70 - $persentase) / 20
            ];
        } else {
            return ['BAIK' => 0, 'CUKUP' => 0, 'KURANG' => 1];
        }
    }

    private function kerapian($skor)
    {
        if ($skor >= 8) {
            return ['RAPI' => 1, 'SEDANG' => 0, 'TIDAK_RAPI' => 0];
        } elseif ($skor >= 6) {
            return [
                'RAPI' => ($skor - 6) / 2,
                'SEDANG' => (8 - $skor) / 2,
                'TIDAK_RAPI' => 0
            ];
        } elseif ($skor >= 4) {
            return [
                'RAPI' => 0,
                'SEDANG' => ($skor - 4) / 2,
                'TIDAK_RAPI' => (6 - $skor) / 2
            ];
        } else {
            return ['RAPI' => 0, 'SEDANG' => 0, 'TIDAK_RAPI' => 1];
        }
    }

    private $aturan = [
        ['IF' => ['absensi' => 'BAIK', 'kerapian' => 'RAPI'], 'THEN' => 'SANGAT_BAIK'],
        ['IF' => ['absensi' => 'BAIK', 'kerapian' => 'SEDANG'], 'THEN' => 'BAIK'],
        ['IF' => ['absensi' => 'CUKUP', 'kerapian' => 'RAPI'], 'THEN' => 'BAIK'],
        ['IF' => ['absensi' => 'CUKUP', 'kerapian' => 'SEDANG'], 'THEN' => 'CUKUP'],
        ['IF' => ['absensi' => 'KURANG', 'kerapian' => 'TIDAK_RAPI'], 'THEN' => 'KURANG'],
    ];

    public function evaluasiGuru($persentaseAbsensi, $skorKerapian)
    {
        // Fuzzifikasi
        $absensiFuzzy = $this->absensi($persentaseAbsensi);
        $kerapianFuzzy = $this->kerapian($skorKerapian);

        // Evaluasi aturan
        $hasilEvaluasi = [];
        foreach ($this->aturan as $aturan) {
            $nilaiAbsensi = $absensiFuzzy[$aturan['IF']['absensi']] ?? 0;
            $nilaiKerapian = $kerapianFuzzy[$aturan['IF']['kerapian']] ?? 0;

            // Menggunakan MIN untuk operasi AND
            $kekuatanAturan = min($nilaiAbsensi, $nilaiKerapian);
            $hasilEvaluasi[$aturan['THEN']] = $kekuatanAturan;
        }

        // Defuzzifikasi menggunakan Center of Gravity
        $pembilang = 0;
        $penyebut = 0;
        $rentangOutput = [
            'SANGAT_BAIK' => 90,
            'BAIK' => 75,
            'CUKUP' => 60,
            'KURANG' => 45
        ];

        foreach ($hasilEvaluasi as $kategori => $kekuatan) {
            $pembilang += $kekuatan * $rentangOutput[$kategori];
            $penyebut += $kekuatan;
        }

        return $penyebut > 0 ? $pembilang / $penyebut : 0;
    }
}

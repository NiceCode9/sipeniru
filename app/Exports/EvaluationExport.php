<?php

namespace App\Exports;

use App\Models\Evaluasi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class EvaluationExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Evaluasi::query()->with('user');

        switch ($this->request->get('period')) {
            case 'semester':
                $start = Carbon::now()->startOfYear();
                if (Carbon::now()->month > 6) {
                    $start->addMonths(6);
                }
                $query->whereBetween('created_at', [$start, Carbon::now()]);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                if ($this->request->filled(['start_date', 'end_date'])) {
                    $query->whereBetween('created_at', [
                        $this->request->start_date,
                        $this->request->end_date
                    ]);
                }
                break;
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Guru',
            'Tanggal Evaluasi',
            'Persentase Kehadiran',
            'Nilai Kerapian',
            'Nilai Akhir',
            'Predikat'
        ];
    }

    public function map($evaluasi): array
    {
        return [
            $evaluasi->user->name,
            $evaluasi->created_at->format('d/m/Y'),
            number_format($evaluasi->presentasi_absensi, 2) . '%',
            number_format($evaluasi->score_kerapian, 2),
            number_format($evaluasi->score_akhir, 2),
            $evaluasi->predikat
        ];
    }
}

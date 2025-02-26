@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h4>Selamat Datang, {{ auth()->user()->name }}</h4>
            </div>
        </div>

        <div class="row">
            <!-- Status Absensi Hari Ini -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">Status Absensi Hari Ini</div>
                    <div class="card-body">
                        @if ($todayAttendance)
                            <ul class="list-unstyled">
                                <li>Check In:
                                    {{ $todayAttendance->check_in ? Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i:s') : 'Belum' }}
                                </li>
                                <li>Check Out:
                                    {{ $todayAttendance->check_out ? Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i:s') : 'Belum' }}
                                </li>
                                <li>Nilai Kerapian: {{ $todayAttendance->nilai_kerapian ?? 'Belum dinilai' }}</li>
                            </ul>
                        @else
                            <p class="mb-0">Anda belum melakukan absensi hari ini</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistik Bulan Ini -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">Statistik Bulan Ini</div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>Total Kehadiran: {{ $totalHadir }} hari</li>
                            <li>Total Keterlambatan: {{ $totalTerlambat }} kali</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluasi Terakhir -->
        @if ($lastEvaluation)
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Evaluasi Terakhir</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Persentase Kehadiran</h6>
                                        <h4>{{ number_format($lastEvaluation->presentasi_absensi, 2) }}%</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Nilai Kerapian</h6>
                                        <h4>{{ number_format($lastEvaluation->score_kerapian, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Nilai Akhir</h6>
                                        <h4>{{ number_format($lastEvaluation->score_akhir, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h6>Predikat</h6>
                                        <h4>{{ $lastEvaluation->predikat }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

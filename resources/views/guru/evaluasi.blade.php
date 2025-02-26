@extends('layouts.app', ['title' => 'Evaluasi'])

@section('content')
    <div class="container">
        <!-- Statistik Rata-rata -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Rata-rata Kehadiran</h6>
                        <h3>{{ number_format($averages->avg_absensi, 2) }}%</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>Rata-rata Kerapian</h6>
                        <h3>{{ number_format($averages->avg_kerapian, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h6>Rata-rata Nilai Akhir</h6>
                        <h3>{{ number_format($averages->avg_score, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Evaluasi</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form action="{{ route('guru.evaluasi') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"
                                placeholder="Tanggal Mulai">
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"
                                placeholder="Tanggal Akhir">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('guru.evaluasi') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Kehadiran</th>
                                <th>Kerapian</th>
                                <th>Nilai Akhir</th>
                                <th>Predikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluations as $evaluation)
                                <tr>
                                    <td>
                                        {{ Carbon\Carbon::parse($evaluation->start_date)->format('d/m/Y') }} -
                                        {{ Carbon\Carbon::parse($evaluation->end_date)->format('d/m/Y') }}
                                    </td>
                                    <td>{{ number_format($evaluation->presentasi_absensi, 2) }}%</td>
                                    <td>{{ number_format($evaluation->score_kerapian, 2) }}</td>
                                    <td>{{ number_format($evaluation->score_akhir, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge badge-{{ $evaluation->predikat === 'SANGAT BAIK'
                                                ? 'success'
                                                : ($evaluation->predikat === 'BAIK'
                                                    ? 'info'
                                                    : ($evaluation->predikat === 'CUKUP'
                                                        ? 'warning'
                                                        : 'danger')) }}">
                                            {{ $evaluation->predikat }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $evaluations->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

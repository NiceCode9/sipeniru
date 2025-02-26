@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="container">
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="row">
                    <div class="col-md-4">
                        <label>Periode</label>
                        <select name="period" class="form-control" onchange="this.form.submit()">
                            <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>Semua Waktu</option>
                            <option value="semester" {{ request('period') == 'semester' ? 'selected' : '' }}>Semester Ini
                            </option>
                            <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Tahun Ini</option>
                            <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Kustom</option>
                        </select>
                    </div>
                    <div class="col-md-3 {{ request('period') != 'custom' ? 'd-none' : '' }}" id="customDateInputs">
                        <label>Dari</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 {{ request('period') != 'custom' ? 'd-none' : '' }}">
                        <label>Sampai</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <a href="{{ route('admin.dashboard.export', request()->all()) }}" class="btn btn-success">Export
                            Data</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Statistik Umum -->
            <div class="col-md-3">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h5>Total Guru</h5>
                        <h2>{{ $totalGuru }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <h5>Rata-rata Kehadiran</h5>
                        <h2>{{ number_format($avgKehadiran, 2) }}%</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <h5>Rata-rata Nilai Akhir</h5>
                        <h2>{{ number_format($avgNilaiAkhir, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <h5>Total Evaluasi</h5>
                        <h2>{{ $totalEvaluasi }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Monthly Trend Chart -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        Trend Evaluasi Per Bulan
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grafik Evaluasi -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Distribusi Predikat Evaluasi
                    </div>
                    <div class="card-body">
                        <canvas id="predikatChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabel Top Performers -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        5 Guru dengan Nilai Tertinggi
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama Guru</th>
                                        <th>Nilai Akhir</th>
                                        <th>Predikat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topPerformers as $guru)
                                        <tr>
                                            <td>{{ $guru->user->name }}</td>
                                            <td>{{ number_format($guru->score_akhir, 2) }}</td>
                                            <td>{{ $guru->predikat }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('predikatChart');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($predikatLabels) !!},
                    datasets: [{
                        data: {!! json_encode($predikatData) !!},
                        backgroundColor: [
                            '#28a745',
                            '#17a2b8',
                            '#ffc107',
                            '#dc3545'
                        ]
                    }]
                }
            });

            // Trend Chart
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthLabels) !!},
                    datasets: [{
                        label: 'Rata-rata Nilai Akhir',
                        data: {!! json_encode($monthlyAverages) !!},
                        borderColor: '#4e73df',
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });

            // Custom date inputs visibility
            document.querySelector('select[name="period"]').addEventListener('change', function() {
                const customInputs = document.getElementById('customDateInputs').parentElement;
                customInputs.nextElementSibling.classList.toggle('d-none', this.value !== 'custom');
                customInputs.classList.toggle('d-none', this.value !== 'custom');
            });
        </script>
    @endpush
@endsection

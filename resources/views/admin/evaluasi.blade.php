@extends('layouts.app', ['title' => 'Evaluasi Guru'])

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('evaluation_results'))
                <div class="card mb-4">
                    <div class="card-header">Hasil Evaluasi</div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Nama Guru</th>
                                <td>{{ session('evaluation_results')['teacher_name'] }}</td>
                            </tr>
                            <tr>
                                <th>Persentase Kehadiran</th>
                                <td>{{ session('evaluation_results')['presentasi_absensi'] }}%</td>
                            </tr>
                            <tr>
                                <th>Nilai Kerapian</th>
                                <td>{{ session('evaluation_results')['score_kerapian'] }}</td>
                            </tr>
                            <tr>
                                <th>Nilai Akhir</th>
                                <td>{{ session('evaluation_results')['score_akhir'] }}</td>
                            </tr>
                            <tr>
                                <th>Predikat</th>
                                <td>{{ session('evaluation_results')['predikat'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">Buat Evaluasi Baru</div>
                <div class="card-body">
                    <form action="{{ route('admin.evaluasi.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Pilih Guru</label>
                            <select name="user_id" class="form-control" required>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Hitung dan Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mt-4">
                <div class="card-header">Daftar Evaluasi</div>
                <div class="card-body">
                    <form action="{{ route('admin.evaluasi.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Guru</label>
                                    <select name="teacher_id" class="form-control">
                                        <option value="">Semua Guru</option>
                                        @foreach ($teachers as $teacher)
                                            <option value="{{ $teacher->id }}"
                                                {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" name="filter_start_date" class="form-control"
                                        value="{{ request('filter_start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Akhir</label>
                                    <input type="date" name="filter_end_date" class="form-control"
                                        value="{{ request('filter_end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Filter</button>
                                    <a href="{{ route('admin.evaluasi.index') }}" class="btn btn-secondary"
                                        title="reset">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="evaluations-table">
                            <thead>
                                <tr>
                                    <th>Nama Guru</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Akhir</th>
                                    <th>Presentasi Absensi</th>
                                    <th>Nilai Kerapian</th>
                                    <th>Nilai Akhir</th>
                                    <th>Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($evaluations as $evaluation)
                                    <tr>
                                        <td>{{ $evaluation->user ? $evaluation->user->name : '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($evaluation->start_date)->locale('id')->isoFormat('DD MMMM YYYY') }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($evaluation->end_date)->locale('id')->isoFormat('DD MMMM YYYY') }}
                                        </td>
                                        <td>{{ number_format($evaluation->presentasi_absensi, 2) }}%</td>
                                        <td>{{ number_format($evaluation->score_kerapian, 2) }}</td>
                                        <td>{{ number_format($evaluation->score_akhir, 2) }}</td>
                                        <td>{{ $evaluation->predikat }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $evaluations->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($chartData) && !empty($chartData))
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Grafik Predikat Evaluasi</div>
                    <div class="card-body">
                        <div style="height: 400px;">
                            <canvas id="predikatChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('predikatChart').getContext('2d');
                    const chartData = @json($chartData);

                    // Urutan predikat yang diinginkan
                    const predikatOrder = ['SANGAT BAIK', 'BAIK', 'CUKUP', 'KURANG'];

                    // Siapkan labels dan data sesuai urutan
                    const labels = [];
                    const data = [];
                    const backgroundColors = [];
                    const borderColors = [];

                    // Warna untuk setiap predikat
                    const colorMap = {
                        'SANGAT BAIK': {
                            bg: 'rgba(54, 162, 235, 0.7)',
                            border: 'rgba(54, 162, 235, 1)'
                        },
                        'BAIK': {
                            bg: 'rgba(75, 192, 192, 0.7)',
                            border: 'rgba(75, 192, 192, 1)'
                        },
                        'CUKUP': {
                            bg: 'rgba(255, 206, 86, 0.7)',
                            border: 'rgba(255, 206, 86, 1)'
                        },
                        'KURANG': {
                            bg: 'rgba(255, 99, 132, 0.7)',
                            border: 'rgba(255, 99, 132, 1)'
                        }
                    };

                    // Loop melalui urutan predikat yang telah ditentukan
                    predikatOrder.forEach(predikat => {
                        if (chartData[predikat] !== undefined) {
                            labels.push(predikat);
                            data.push(chartData[predikat]);
                            backgroundColors.push(colorMap[predikat].bg);
                            borderColors.push(colorMap[predikat].border);
                        }
                    });

                    // Buat chart
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Jumlah Evaluasi',
                                data: data,
                                backgroundColor: backgroundColors,
                                borderColor: borderColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.parsed.y} evaluasi`;
                                        }
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.label}: ${context.parsed.y} evaluasi (${Math.round(context.parsed.y / context.dataset.data.reduce((a, b) => a + b, 0) * 100)}%)`;
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif
@endsection

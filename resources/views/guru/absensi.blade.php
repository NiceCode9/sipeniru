@extends('layouts.app', ['title' => 'Absensi'])

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Statistik Bulan Ini</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h5>Total Hadir</h5>
                                <h2>{{ $totalHadir }}</h2>
                            </div>
                            <div class="col-6">
                                <h5>Total Terlambat</h5>
                                <h2>{{ $totalTerlambat }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Absensi</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('guru.absensi') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('guru.absensi') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Nilai Kerapian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($absensi as $a)
                                <tr>
                                    <td>{{ Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $a->check_in ? Carbon\Carbon::parse($a->check_in)->format('H:i:s') : '-' }}</td>
                                    <td>{{ $a->check_out ? Carbon\Carbon::parse($a->check_out)->format('H:i:s') : '-' }}
                                    </td>
                                    <td>
                                        @if ($a->check_in && Carbon\Carbon::parse($a->check_in)->format('H:i:s') > '07:30:00')
                                            <span class="badge badge-warning">Terlambat</span>
                                        @elseif($a->check_in)
                                            <span class="badge badge-success">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td>{{ $a->nilai_kerapian ?? 'Belum dinilai' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $absensi->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

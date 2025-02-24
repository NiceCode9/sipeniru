@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Absensi</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Absen Masuk</th>
                                    <th>Absen Keluar</th>
                                    <th>Nilai Kerapian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $absensi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $absensi->user->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->locale('ID')->isoFormat('DD MMMM YYYY') }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->check_in)->format('H:i:s') }}
                                        </td>
                                        <td>{{ $absensi->check_out ? \Carbon\Carbon::parse($absensi->check_out)->format('H:i:s') : '-' }}
                                        </td>
                                        <td>
                                            <span id="score-{{ $absensi->id }}">
                                                {{ $absensi->nilai_kerapian ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($absensi->check_out)
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="inputNeatness({{ $absensi->id }}, {{ $absensi->user->id }})">
                                                    Nilai Kerapian
                                                </button>
                                            @else
                                                <span class="badge badge-secondary">Belum Absen Keluar</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function inputNeatness(attendanceId, userId) {
            const nilai = prompt('Masukkan nilai kerapian (0-10):');
            let url = "{{ route('absensi.inputKerapian') }}";
            if (nilai !== null) {
                if (nilai <= 10) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            user_id: userId,
                            // date: '{{ Carbon\Carbon::today()->format('Y-m-d') }}',
                            nilai_kerapian: nilai,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            document.getElementById(`score-${attendanceId}`).textContent = nilai;
                            alert('Nilai kerapian berhasil disimpan');
                        },
                        error: function(xhr) {
                            alert('Terjadi kesalahan');
                        }
                    });
                } else {
                    alert('Nilai kerapian maksimal 10');
                    return false;
                }
            }
        }
    </script>
@endpush

@extends('layouts.app', ['title' => 'Riwayat Absensi'])

@push('css')
    <style>
        .swal-custom-popup {
            width: 800px !important;
            max-width: 90vw;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Daftar Absensi</h4>
                </div>
                <div class="card-body">
                    <!-- Form Filter -->
                    <form action="{{ route('admin.absensi.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Guru</label>
                                    <select name="user_id" class="form-control">
                                        <option value="">Semua Guru</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
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
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        {{-- <table class="table table-bordered">
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
                        </table> --}}
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Absen Masuk</th>
                                    <th>Absen Keluar</th>
                                    <th>Kerapian Seragam</th>
                                    <th>Kelengkapan Atribut</th>
                                    <th>Nilai Kerapian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $absensi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $absensi->user ? $absensi->user->name : '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->locale('ID')->isoFormat('DD MMMM YYYY') }}
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->check_in)->format('H:i:s') }}</td>
                                        <td>{{ $absensi->check_out ? \Carbon\Carbon::parse($absensi->check_out)->format('H:i:s') : '-' }}
                                        </td>
                                        <td id="kerapian-seragam-{{ $absensi->id }}">
                                            {{ $absensi->kerapian_seragam ?? '-' }}</td>
                                        <td id="kelengkapan-atribut-{{ $absensi->id }}">
                                            {{ $absensi->kelengkapan_atribut ?? '-' }}</td>
                                        <td id="score-{{ $absensi->id }}">{{ $absensi->nilai_kerapian ?? '-' }}</td>
                                        <td>
                                            @if ($absensi->check_out)
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="inputNeatness({{ $absensi->id }}, {{ $absensi->user->id }})">
                                                    Nilai Kerapian
                                                </button>
                                            @else
                                                <span class="badge badge-secondary">Belum Absen Keluar</span>
                                            @endif
                                            @if ($absensi->path)
                                                <button type="button" class="btn btn-info btn-sm btn-show-foto"
                                                    data-bs-toggle="modal" data-bs-target="#fotoModal"
                                                    data-foto="{{ asset('storage/' . $absensi->path) }}">
                                                    <i class="fa fa-image"></i> Lihat Foto
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $data->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Foto -->
    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoModalLabel">Foto Attribut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="fotoAttributImg" alt="Foto Attribut" class="img-fluid"
                        style="max-height:400px;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // function inputNeatness(attendanceId, userId) {
        //     const nilai = prompt('Masukkan nilai kerapian (0-10):');
        //     let url = "{{ route('admin.absensi.inputKerapian') }}";
        //     if (nilai !== null) {
        //         if (nilai <= 10) {
        //             $.ajax({
        //                 url: url,
        //                 type: 'POST',
        //                 data: {
        //                     user_id: userId,
        //                     // date: '{{ Carbon\Carbon::today()->format('Y-m-d') }}',
        //                     nilai_kerapian: nilai,
        //                     _token: '{{ csrf_token() }}'
        //                 },
        //                 success: function(response) {
        //                     document.getElementById(`score-${attendanceId}`).textContent = nilai;
        //                     alert('Nilai kerapian berhasil disimpan');
        //                 },
        //                 error: function(xhr) {
        //                     alert('Terjadi kesalahan');
        //                 }
        //             });
        //         } else {
        //             alert('Nilai kerapian maksimal 10');
        //             return false;
        //         }
        //     }
        // }

        // function inputNeatness(attendanceId, userId) {
        //     // Buat dialog modal alih-alih prompt
        //     Swal.fire({
        //         title: 'Input Nilai Kerapian',
        //         html: `
    //             <div class="form-group">
    //                 <label>Kerapian Seragam</label>
    //                 <select id="kerapian-seragam" class="form-control">
    //                     <option value="">Pilih Nilai</option>
    //                     <option value="Disiplin">Disiplin</option>
    //                     <option value="Kurang Disiplin">Kurang Disiplin</option>
    //                     <option value="Tidak Disiplin">Tidak Disiplin</option>
    //                 </select>
    //             </div>
    //             <div class="form-group mt-3">
    //                 <label>Kelengkapan Atribut</label>
    //                 <select id="kelengkapan-atribut" class="form-control">
    //                     <option value="">Pilih Nilai</option>
    //                     <option value="Lengkap">Lengkap</option>
    //                     <option value="Kurang Lengkap">Kurang Lengkap</option>
    //                     <option value="Tidak Lengkap">Tidak Lengkap</option>
    //                 </select>
    //             </div>
    //         `,
        //         showCancelButton: true,
        //         confirmButtonText: 'Simpan',
        //         cancelButtonText: 'Batal',
        //         preConfirm: () => {
        //             const kerapianSeragam = document.getElementById('kerapian-seragam').value;
        //             const kelengkapanAtribut = document.getElementById('kelengkapan-atribut').value;

        //             if (!kerapianSeragam || !kelengkapanAtribut) {
        //                 Swal.showValidationMessage('Semua nilai harus diisi');
        //                 return false;
        //             }

        //             return {
        //                 kerapianSeragam,
        //                 kelengkapanAtribut
        //             };
        //         }
        //     }).then((result) => {
        //         if (result.isConfirmed) {
        //             let url = "{{ route('admin.absensi.inputKerapian') }}";
        //             $.ajax({
        //                 url: url,
        //                 type: 'POST',
        //                 data: {
        //                     user_id: userId,
        //                     absensi_id: attendanceId,
        //                     kerapian_seragam: result.value.kerapianSeragam,
        //                     kelengkapan_atribut: result.value.kelengkapanAtribut,
        //                     _token: '{{ csrf_token() }}'
        //                 },
        //                 success: function(response) {
        //                     console.log(response);

        //                     document.getElementById(`kerapian-seragam-${attendanceId}`).textContent =
        //                         result.value.kerapianSeragam;
        //                     document.getElementById(`kelengkapan-atribut-${attendanceId}`).textContent =
        //                         result.value.kelengkapanAtribut;
        //                     document.getElementById(`score-${attendanceId}`).textContent = response
        //                         .nilai_kerapian || '-';
        //                     Swal.fire('Berhasil', 'Nilai kerapian berhasil disimpan', 'success');
        //                 },
        //                 error: function(xhr) {
        //                     console.log(xhr.responseJSON);

        //                     Swal.fire('Error', 'Terjadi kesalahan', 'error');
        //                 }
        //             });
        //         }
        //     });
        // }

        function inputNeatness(attendanceId, userId) {
            Swal.fire({
                title: 'Input Nilai Kerapian',
                html: `
                    <div class="mb-3">
                        <h5>Kerapian Seragam</h5>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="kerapian_seragam1" name="kerapian_seragam" value="baju sesuai hari">
                            <label class="form-check-label" for="kerapian_seragam1">Seragam Sesuai Hari</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="kerapian_seragam2" name="kerapian_seragam" value="baju masuk">
                            <label class="form-check-label" for="kerapian_seragam2">Baju Dimasukkan</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="kerapian_seragam3" name="kerapian_seragam" value="seragam bersih">
                            <label class="form-check-label" for="kerapian_seragam3">Seragam Bersih</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h5>Kelengkapan Atribut</h5>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="kelengkapan_atribut1" name="kelengkapan_atribut" value="songkok/kerudung">
                            <label class="form-check-label" for="kelengkapan_atribut1">Songkok / Kerudung</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="kelengkapan_atribut2" name="kelengkapan_atribut" value="ikat pinggang">
                            <label class="form-check-label" for="kelengkapan_atribut2">Ikat Pinggang</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="kelengkapan_atribut3" name="kelengkapan_atribut" value="sepatu">
                            <label class="form-check-label" for="kelengkapan_atribut3">Sepatu</label>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal-custom-popup',
                },
                preConfirm: () => {
                    // Validasi jika tidak ada checkbox yang dipilih
                    if ($('input[name="kerapian_seragam"]:checked').length === 0 ||
                        $('input[name="kelengkapan_atribut"]:checked').length === 0) {
                        Swal.showValidationMessage('Pilih minimal satu checkbox');
                        return false;
                    }

                    // Mengambil nilai dari checkbox yang dipilih
                    const kerapianSeragam = [];
                    $('input[name="kerapian_seragam"]:checked').each(function() {
                        kerapianSeragam.push($(this).val());
                    });
                    const kelengkapanAtribut = [];
                    $('input[name="kelengkapan_atribut"]:checked').each(function() {
                        kelengkapanAtribut.push($(this).val());
                    });
                    // Mengembalikan nilai sebagai objek
                    // return {
                    //     kerapianSeragam,
                    //     kelengkapanAtribut
                    // };
                    return {
                        kerapianSeragam,
                        kelengkapanAtribut,
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('admin.absensi.inputKerapian') }}";
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            user_id: userId,
                            absensi_id: attendanceId,
                            kerapian_seragam: result.value.kerapianSeragam.length,
                            kelengkapan_atribut: result.value.kelengkapanAtribut.length,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            console.log(response);

                            document.getElementById(`kerapian-seragam-${attendanceId}`).textContent =
                                response.kerapian_seragam || '-';
                            document.getElementById(`kelengkapan-atribut-${attendanceId}`).textContent =
                                response.kelengkapan_atribut || '-';
                            document.getElementById(`score-${attendanceId}`).textContent = response
                                .nilai_kerapian || '-';
                            Swal.fire('Berhasil', 'Nilai kerapian berhasil disimpan', 'success');
                        },
                        error: function(xhr) {
                            console.log(xhr.responseJSON);

                            Swal.fire('Error', 'Terjadi kesalahan', 'error');
                        }
                    });
                }
            });
        }

        // Menampilkan foto di modal
        $(document).on('click', '.btn-show-foto', function() {
            const foto = $(this).data('foto');
            $('#foto-bukti').attr('src', foto);
        });
    </script>
@endpush

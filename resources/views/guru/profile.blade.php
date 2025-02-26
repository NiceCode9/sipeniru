@extends('layouts.app', ['title' => 'Profil Saya'])

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Profil Saya</div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Nama</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>NIP</th>
                                <td>{{ $user->nip }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">QR Code Absensi</div>
                    <div class="card-body text-center">
                        <div class="qr-code">
                            {!! $qrCode !!}
                        </div>
                        <p class="mt-3">Gunakan QR Code ini untuk melakukan absensi</p>
                        <a href="{{ route('guru.users.qr-code', auth()->user()->id) }}" class="btn btn-sm btn-primary"
                            title="Download QrCode"><i class="fas fa-download"></i> Unduh</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

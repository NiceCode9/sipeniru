@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Scanner QR Code Absensi</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="reader"></div>
                            <div class="mt-3">
                                <select id="cameraSelection" class="form-control mb-3">
                                    <option value="">Pilih Kamera</option>
                                </select>
                                <button id="startButton" class="btn btn-primary">Mulai Scan</button>
                                <button id="stopButton" class="btn btn-danger d-none">Stop Scan</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="scan-result" class="alert alert-success d-none">
                                <h5 class="alert-heading">Hasil Scan</h5>
                                <div id="user-details"></div>
                                <div id="attendance-details"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let html5QrCode = null;

            // Get available cameras
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    devices.forEach(device => {
                        $('#cameraSelection').append(
                            `<option value="${device.id}">${device.label}</option>`);
                    });
                } else {
                    $('#scan-result')
                        .text('Tidak ada kamera yang terdeteksi')
                        .removeClass('d-none')
                        .removeClass('alert-success')
                        .addClass('alert-danger');
                }
            }).catch(err => {
                $('#scan-result')
                    .text('Error mengakses kamera: ' + err)
                    .removeClass('d-none')
                    .removeClass('alert-success')
                    .addClass('alert-danger');
            });

            $('#startButton').click(function() {
                const cameraId = $('#cameraSelection').val();
                if (!cameraId) {
                    alert('Silakan pilih kamera terlebih dahulu');
                    return;
                }

                html5QrCode = new Html5Qrcode("reader");
                const config = {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    },
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    },
                    aspectRatio: 1.0,
                    focusMode: "continuous",
                    rememberLastUsedCamera: true
                };

                html5QrCode.start(
                    cameraId,
                    config,
                    onScanSuccess,
                    onScanError
                ).then(() => {
                    $('#startButton').addClass('d-none');
                    $('#stopButton').removeClass('d-none');
                    $('#cameraSelection').prop('disabled', true);
                }).catch(err => {
                    $('#scan-result')
                        .text('Error memulai scanner: ' + err)
                        .removeClass('d-none')
                        .removeClass('alert-success')
                        .addClass('alert-danger');
                });
            });

            $('#stopButton').click(function() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        $('#startButton').removeClass('d-none');
                        $('#stopButton').addClass('d-none');
                        $('#cameraSelection').prop('disabled', false);
                    });
                }
            });

            function onScanSuccess(qrData) {
                $.ajax({
                    url: '{{ route('absensi.scan') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        qr_data: qrData
                    },
                    success: function(response) {
                        console.log(response);
                        const user = response.data.user;
                        const attendance = response.data.attendance;

                        let userDetails = `
                        <p><strong>Nama:</strong> ${user.name}</p>
                        <p><strong>Email:</strong> ${user.email}</p>
                    `;

                        let attendanceDetails = `
                        <p><strong>Tanggal:</strong> ${attendance.date}</p>
                        <p><strong>Check-in:</strong> ${attendance.check_in || '-'}</p>
                        <p><strong>Check-out:</strong> ${attendance.check_out || '-'}</p>
                        <p><strong>Status:</strong> ${attendance.status}</p>
                    `;

                        $('#user-details').html(userDetails);
                        $('#attendance-details').html(attendanceDetails);
                        $('#scan-result')
                            .removeClass('d-none alert-danger')
                            .addClass('alert-success')
                            .find('.alert-heading')
                            .text(response.message);
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        $('#scan-result')
                            .removeClass('d-none alert-success')
                            .addClass('alert-danger')
                            .find('.alert-heading')
                            .text(response.message || 'Terjadi kesalahan saat memproses QR code');
                        $('#user-details, #attendance-details').empty();
                    }
                });
            }

            function onScanError(error) {
                // Only log significant errors, not the common "no QR code found" error
                if (!error.includes('No QR code found') &&
                    !error.includes('No MultiFormat Readers')) {
                    console.error('Scan error:', error);
                }
            }
        });
    </script>
@endpush

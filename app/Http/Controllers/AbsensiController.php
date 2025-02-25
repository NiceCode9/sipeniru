<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    // Konstanta untuk minimum waktu (dalam menit) antara check-in dan check-out
    const MIN_HOURS_BETWEEN_CHECKIN_CHECKOUT = 2; // 2 jam

    public function index()
    {
        return view('admin.scan');
    }

    public function scan(Request $request)
    {
        try {
            $userId = $request->qr_data;
            $user = User::findOrFail($userId);

            $today = Carbon::now()->toDateString();
            $currentTime = Carbon::now();

            $absensi = Absensi::firstOrNew([
                'user_id' => $userId,
                'tanggal' => $today
            ]);

            if ($absensi->check_in == null) {
                // Process check-in
                $absensi->check_in = $currentTime;
                $message = 'Check-in berhasil';
                $status = 'check-in';
            } else if ($absensi->check_out == null) {
                // Validasi minimum waktu antara check-in dan check-out
                $timeSinceCheckIn = $currentTime->diffInHours($absensi->check_in, true);

                if ($timeSinceCheckIn < self::MIN_HOURS_BETWEEN_CHECKIN_CHECKOUT) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda belum bisa melakukan check-out. Minimal waktu kerja adalah ' .
                            self::MIN_HOURS_BETWEEN_CHECKIN_CHECKOUT . ' jam.'
                    ], 400);
                }

                // Process check-out
                $absensi->check_out = $currentTime;
                $message = 'Check-out berhasil';
                $status = 'check-out';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-in dan check-out hari ini'
                ], 400);
            }

            $absensi->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'attendance' => [
                        'date' => $today,
                        'check_in' => $absensi->check_in ? $absensi->check_in->format('H:i:s') : null,
                        'check_out' => $absensi->check_out ? $absensi->check_out->format('H:i:s') : null,
                        'status' => $status
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function absensiList(Request $request)
    {
        $query = Absensi::with('user');

        // Filter berdasarkan guru
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('filter_start_date') && $request->filled('filter_end_date')) {
            $query->whereBetween('tanggal', [
                $request->filter_start_date,
                $request->filter_end_date
            ]);
        }

        $data = $query->orderByRaw('CASE
            WHEN check_out IS NULL THEN 1
            WHEN nilai_kerapian IS NULL THEN 2
            ELSE 3 END')
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        $users = User::where('role', 'guru')->get();

        return view('admin.absensi_list', compact('data', 'users'));
    }

    public function inputKerapian(Request $request)
    {
        $absensi = Absensi::where('user_id', $request->user_id)
            // ->where('date', $request->date)
            ->firstOrFail();

        $absensi->update([
            'nilai_kerapian' => $request->nilai_kerapian
        ]);

        return response()->json(['message' => 'Nilai kerapian berhasil disimpan']);
    }
}

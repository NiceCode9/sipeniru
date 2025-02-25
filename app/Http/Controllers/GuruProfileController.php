<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;

class GuruProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $qrCode = QrCode::size(200)->generate($user->id);

        return view('guru.profile', compact('user', 'qrCode'));
    }
}

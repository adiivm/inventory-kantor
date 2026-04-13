<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Penting untuk urusan login

class LoginController extends Controller
{
    public function index() {
        return view('login_view'); // Tampilkan form login
    }

    public function login_proses(Request $request) {
        // Ambil input email & password
        $kredensial = $request->only('email', 'password');

        // Cek ke database, apakah cocok?
        if (Auth::attempt($kredensial)) {
            // Jika cocok, buatkan SESSION (Kartu Absen)
            $request->session()->regenerate();
            return redirect()->intended('/dashboard'); // Lanjut ke halaman utama
        }

        // Jika salah, balikkan ke login dengan pesan error
        return back()->with('loginError', 'Email atau Password salah, Mas Bro!');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
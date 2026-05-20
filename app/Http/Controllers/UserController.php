<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Munculkan Tabel User
    public function index()
    {
        // Proteksi: Cuma Admin yang boleh liat daftar user
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak! Anda bukan Admin.');
        }

        $users = User::all();
        return view('users_index', compact('users')); // Pastikan file blade namanya users_index.blade.php
    }

    // Simpan User Baru (Fungsi yang Mas Bro cari)
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,staff', // Validasi pilihan role
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Password WAJIB di-hash
            'role'     => $request->role, // Menentukan role (Admin/Staff)
        ]);

        return back()->with('success', 'User ' . $request->name . ' berhasil ditambahkan!');
    }

    // Update User & Ganti Password Orang Lain
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'role'  => 'required|in:admin,staff',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;

        // Jika kotak password di modal edit diisi, baru kita ganti
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return back()->with('success', 'Data user berhasil diperbarui!');
    }

    // Hapus User
    public function destroy($id)
    {
        // Jangan biarkan admin bunuh diri (hapus akun sendiri)
        if ($id == Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User berhasil dihapus!');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class UserController extends Controller
{
    // Munculkan Tabel User
    public function index()
    {
        Gate::authorize('admin-only');

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
            'role'     => ['required', Rule::enum(UserRole::class)],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'can_approve' => $request->boolean('can_approve'),
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
            'role'  => ['required', Rule::enum(UserRole::class)],
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;
        $user->can_approve = $request->boolean('can_approve');

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
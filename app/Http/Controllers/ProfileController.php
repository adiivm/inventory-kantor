<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $users = [];

        if (Gate::allows('admin-only')) {
            $users = User::all();
        }

        return view('profile_index', compact('user', 'users'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240', // Max 2MB
        ]);

        $user->name = $request->name;

        // Logika Upload Foto
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo) {
                Storage::disk('public')->delete('photos/'.$user->photo);
            }

            // Simpan foto baru ke folder: storage/app/public/photos
            $fileName = time().'.'.$request->photo->extension();
            $request->file('photo')->storeAs('photos', $fileName, 'public');

            $user->photo = $fileName;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Input
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // 'confirmed' artinya butuh input 'new_password_confirmation'
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password minimal 6 karakter.',
        ]);

        // 2. Cek apakah password lama benar
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama kamu salah, Mas Bro!']);
        }

        // 3. Update Password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password berhasil diganti! Jaga kerahasiaannya ya.');
    }
}

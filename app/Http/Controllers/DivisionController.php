<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $request->validate(['name' => 'required|unique:divisions,name'
        ], [
            'name.unique' => 'Nama divisi ini sudah ada di daftar!' // Pesan custom
        ]);

        // Simpan ke database
        $division = Division::create(['name' => $request->name]);

        // Kirim balik data dalam bentuk JSON agar ditangkap oleh JavaScript
        return response()->json($division);
    }
}

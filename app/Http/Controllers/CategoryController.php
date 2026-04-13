<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|unique:categories,name' // sesuaikan nama tabel
        ], [
            'name.unique' => 'Nama kategori ini sudah ada di daftar!' // Pesan custom
        ]);

        // Simpan ke database
        $category = Category::create(['name' => $request->name]);

        // Kirim balik data dalam bentuk JSON agar ditangkap oleh JavaScript
        return response()->json($category);
    }
}
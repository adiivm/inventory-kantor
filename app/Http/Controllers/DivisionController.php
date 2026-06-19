<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DivisionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:divisions,name'
        ], [
            'name.unique' => 'Nama divisi ini sudah ada di daftar!'
        ]);

        $division = Division::create(['name' => $request->name]);

        return response()->json($division);
    }

    public function destroy($id)
    {
        Gate::authorize('admin-only');

        $division = Division::findOrFail($id);

        if ($division->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Divisi tidak bisa dihapus karena masih digunakan oleh ' . $division->products()->count() . ' produk.'
            ], 422);
        }

        $division->delete();

        return response()->json([
            'success' => true,
            'message' => 'Divisi berhasil dihapus.'
        ]);
    }

    public function getAll()
    {
        return response()->json(Division::orderBy('name')->get());
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DivisionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:divisions,name',
        ], [
            'name.unique' => 'Nama divisi ini sudah ada di daftar!',
        ]);

        $division = Division::create(['name' => $request->name]);

        Activity::logCreate('master', "Divisi {$division->name}", $division, $division->toArray());

        return response()->json($division);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|unique:divisions,name,'.$id,
        ], [
            'name.unique' => 'Nama divisi ini sudah ada di daftar!',
        ]);

        $division = Division::findOrFail($id);
        $old = $division->name;
        $division->update(['name' => $request->name]);

        Activity::logUpdate('master', "Divisi {$old} → {$division->name}", $division, $division->toArray());

        return response()->json(['success' => true, 'message' => 'Divisi berhasil diupdate.', 'data' => $division]);
    }

    public function destroy($id)
    {
        Gate::authorize('staff-access');

        $division = Division::findOrFail($id);

        if ($division->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Divisi tidak bisa dihapus karena masih digunakan oleh '.$division->products()->count().' produk.',
            ], 422);
        }

        $division->delete();

        Activity::logDelete('master', "Divisi {$division->name}", $division, $division->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Divisi berhasil dihapus.',
        ]);
    }

    public function getAll()
    {
        return response()->json(Division::orderBy('name')->get());
    }
}

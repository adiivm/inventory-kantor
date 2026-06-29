<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name',
        ], [
            'name.unique' => 'Nama kategori ini sudah ada di daftar!',
        ]);

        $category = Category::create(['name' => $request->name]);

        Activity::logCreate('master', "Kategori {$category->name}", $category, $category->toArray());

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,'.$id,
        ], [
            'name.unique' => 'Nama kategori ini sudah ada di daftar!',
        ]);

        $category = Category::findOrFail($id);
        $old = $category->name;
        $category->update(['name' => $request->name]);

        Activity::logUpdate('master', "Kategori {$old} → {$category->name}", $category, $category->toArray());

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diupdate.', 'data' => $category]);
    }

    public function destroy($id)
    {
        Gate::authorize('staff-access');

        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh '.$category->products()->count().' produk.',
            ], 422);
        }

        $category->delete();

        Activity::logDelete('master', "Kategori {$category->name}", $category, $category->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}

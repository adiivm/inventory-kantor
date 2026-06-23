<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Helpers\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name'
        ], [
            'name.unique' => 'Nama kategori ini sudah ada di daftar!'
        ]);

        $category = Category::create(['name' => $request->name]);

        Activity::logCreate('master', "Kategori {$category->name}", $category, $category->toArray());

        return response()->json($category);
    }

    public function destroy($id)
    {
        Gate::authorize('admin-only');

        $category = Category::findOrFail($id);

        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh ' . $category->products()->count() . ' produk.'
            ], 422);
        }

        $category->delete();

        Activity::logDelete('master', "Kategori {$category->name}", $category, $category->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.'
        ]);
    }
}
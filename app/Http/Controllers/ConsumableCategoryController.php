<?php

namespace App\Http\Controllers;

use App\Models\ConsumableCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ConsumableCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ConsumableCategory::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('items_count', fn ($row) => $row->items()->count())
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning" onclick="editCategory('.$row->id.')"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory('.$row->id.')"><i class="bi bi-trash"></i></button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('consumable.categories');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:consumable_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $category = ConsumableCategory::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category,
        ]);
    }

    public function edit($id)
    {
        return response()->json(ConsumableCategory::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $category = ConsumableCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consumable_categories,name,'.$id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        $category = ConsumableCategory::findOrFail($id);

        if ($category->items()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih memiliki '.$category->items()->count().' barang.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ]);
    }

    public function getAll()
    {
        return response()->json(ConsumableCategory::orderBy('name')->get());
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ConsumableUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ConsumableUnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ConsumableUnit::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning" onclick="editUnit(' . $row->id . ')"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUnit(' . $row->id . ')"><i class="bi bi-trash"></i></button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('consumable.units');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:consumable_units,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $unit = ConsumableUnit::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil ditambahkan',
            'data' => $unit,
        ]);
    }

    public function edit($id)
    {
        return response()->json(ConsumableUnit::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $unit = ConsumableUnit::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:consumable_units,name,' . $id,
        ]);

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        $unit = ConsumableUnit::findOrFail($id);
        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Satuan berhasil dihapus',
        ]);
    }

    public function getAll()
    {
        return response()->json(ConsumableUnit::orderBy('name')->get());
    }
}

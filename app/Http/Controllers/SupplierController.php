<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Helpers\Activity;
use App\Imports\SupplierImport;
use App\Exports\SupplierTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Supplier::oldest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-sm btn-warning" onclick="editSupplier(' . $row->id . ')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSupplier(' . $row->id . ')">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('suppliers');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'name.unique' => 'Nama supplier sudah ada! Silakan gunakan nama lain.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $supplier = Supplier::create($validator->validated());

        Activity::logCreate('master', "Supplier {$supplier->name}", $supplier, $supplier->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ], [
            'name.unique' => 'Nama supplier sudah ada! Silakan gunakan nama lain.'
        ]);

        $oldValues = $supplier->toArray();
        $supplier->update($validated);

        Activity::logUpdate('master', "Supplier {$supplier->name}", $supplier, $oldValues, $supplier->fresh()->toArray());

        return redirect()->back()->with('success', 'Supplier berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        Activity::logDelete('master', "Supplier {$supplier->name}", $supplier, $supplier->toArray());

        return redirect()->back()->with('success', 'Supplier berhasil dihapus!');
    }

    public function getAll()
    {
        return response()->json(Supplier::orderBy('name')->get());
    }

    public function edit($id)
    {
        return response()->json(Supplier::findOrFail($id));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        try {
            $import = new SupplierImport;
            Excel::import($import, $request->file('file'));

            $count = $import->getCount();
            $errors = $import->getErrors();

            if ($count > 0) {
                Activity::log('master', 'import', "Import {$count} supplier dari Excel");
            }

            $message = "Import selesai. {$count} supplier ditambahkan.";
            if (!empty($errors)) {
                $message .= ' ' . implode(' | ', array_slice($errors, 0, 5));
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new SupplierTemplateExport, 'supplier_import_template.xlsx');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Division;
use App\Models\Location;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $conditions = ['ready' => 'Ready', 'repair' => 'Servis', 'broken' => 'Rusak', 'disposed' => 'Dibuang'];

        return view('reports.index', compact('categories', 'divisions', 'locations', 'conditions'));
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'columns' => 'required|array|min:1',
        ], [
            'columns.required' => 'Pilih minimal satu kolom untuk diexport!',
        ]);

        $selectedColumns = $request->columns;

        $filters = $request->only([
            'category_id', 'division_id', 'location_id',
            'condition', 'purchase_date_start', 'purchase_date_end',
            'include_inactive'
        ]);
        $filters = array_filter($filters, fn($v) => !is_null($v) && $v !== '');

        $namaFile = 'Laporan_Aset_' . date('d_m_Y') . '.xlsx';

        return Excel::download(new ProductExport($selectedColumns, $filters), $namaFile);
    }
}

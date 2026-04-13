<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // 1. Menampilkan halaman form export
    public function index()
    {
        return view('reports.index');
    }

    // 2. Memproses request export
    public function exportExcel(Request $request)
    {
        // Validasi: pastikan minimal 1 kolom dicentang
        $request->validate([
            'columns' => 'required|array|min:1',
        ], [
            'columns.required' => 'Pilih minimal satu kolom untuk diexport!',
        ]);

        $selectedColumns = $request->columns;
        $namaFile = 'Laporan_Aset_' . date('d_m_Y') . '.xlsx';

        return Excel::download(new ProductExport($selectedColumns), $namaFile);
    }
}
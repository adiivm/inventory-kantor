<?php

namespace App\Http\Controllers;

use App\Models\ConsumableCategory;
use App\Models\ConsumableItem;
use App\Models\ConsumableUnit;
use App\Models\DistributionHeader;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Imports\ConsumableImport;
use App\Exports\ConsumableTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ConsumableItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ConsumableItem::with('category', 'supplier');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('current_stock', function ($row) {
                    $badge = 'success';
                    if ($row->current_stock <= 0) {
                        $badge = 'danger';
                    } elseif ($row->current_stock <= $row->min_stock) {
                        $badge = 'warning text-dark';
                    }
                    return "<span class='badge bg-{$badge}'>{$row->current_stock}</span>";
                })
                ->addColumn('supplier_name', function ($row) {
                    return $row->supplier?->name ?? $row->supplier_name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <a href="' . route('consumable.items.history', $row->id) . '" class="btn btn-sm btn-info"><i class="bi bi-clock-history"></i></a>
                        <button class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '"><i class="bi bi-trash"></i></button>
                    ';
                })
                ->rawColumns(['current_stock', 'action'])
                ->make(true);
        }

        $categories = ConsumableCategory::orderBy('name')->get();
        $units = ConsumableUnit::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('consumable.items', compact('categories', 'units', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:consumable_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'min_stock' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $data = $validator->validated();
        if (empty($data['supplier_id'])) {
            $data['supplier_id'] = null;
        }
        ConsumableUnit::firstOrCreate(['name' => $data['unit']]);
        $item = ConsumableItem::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => $item,
        ]);
    }

    public function edit($id)
    {
        return response()->json(ConsumableItem::with('category', 'supplier')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $item = ConsumableItem::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:consumable_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'min_stock' => 'required|integer|min:0',
            'current_stock' => 'required|integer|min:0',
        ]);

        if (empty($validated['supplier_id'])) {
            $validated['supplier_id'] = null;
        }
        ConsumableUnit::firstOrCreate(['name' => $validated['unit']]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        $item = ConsumableItem::findOrFail($id);

        if ($item->stockTransactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak bisa dihapus karena sudah memiliki riwayat transaksi.',
            ], 422);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus',
        ]);
    }

    public function getAll()
    {
        return response()->json(ConsumableItem::with('category')->orderBy('name')->get());
    }

    public function history($id)
    {
        $item = ConsumableItem::with('category')->findOrFail($id);

        $transactions = StockTransaction::where('consumable_item_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = 0;
        foreach ($transactions as $t) {
            if ($t->type === 'in') {
                $runningBalance += $t->qty;
            } elseif ($t->type === 'out') {
                $runningBalance -= $t->qty;
            } elseif ($t->type === 'adjustment') {
                $runningBalance += $t->qty;
            }
            $t->running_balance = $runningBalance;

            // Cari data distribusi jika type 'out'
            if ($t->type === 'out' && $t->reference_number) {
                $header = DistributionHeader::where('reference_number', $t->reference_number)
                    ->with('division')
                    ->first();
                if ($header) {
                    $t->distribution_info = $header;
                }
            }
        }

        $transactions = $transactions->reverse()->values();

        return view('consumable.item_history', compact('item', 'transactions'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new ConsumableImport;
            Excel::import($import, $request->file('file'));

            $count = $import->getCount() ?? 0;

            if ($count > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Import berhasil! {$count} barang telah ditambahkan.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Import gagal: File tidak memiliki data yang valid.',
            ], 422);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = collect($e->failures())->map(fn($f) =>
                "Row {$f->row()}: " . implode(', ', $f->errors())
            )->implode(' | ');

            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $errors,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new ConsumableTemplateExport, 'template_import_consumable.xlsx');
    }
}

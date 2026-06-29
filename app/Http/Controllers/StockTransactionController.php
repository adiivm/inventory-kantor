<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\ConsumableItem;
use App\Models\StockTransaction;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\StockInPendingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StockTransaction::with('consumableItem.category', 'approver');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('consumable_item_id')) {
                $query->where('consumable_item_id', $request->consumable_item_id);
            }

            if ($request->filled('date_start')) {
                $query->whereDate('date', '>=', $request->date_start);
            }

            if ($request->filled('date_end')) {
                $query->whereDate('date', '<=', $request->date_end);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('type', function ($row) {
                    $map = [
                        'in' => 'badge bg-primary',
                        'out' => 'badge bg-danger',
                        'adjustment' => 'badge bg-secondary',
                    ];
                    $class = $map[$row->type] ?? 'badge bg-secondary';

                    return "<span class='{$class}'>".ucfirst($row->type).'</span>';
                })
                ->editColumn('qty', function ($row) {
                    $sign = $row->type === 'in' ? '+' : ($row->type === 'out' ? '-' : '');

                    return $sign.$row->qty;
                })
                ->editColumn('date', fn ($row) => $row->date->format('d/m/Y'))
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending' => 'badge bg-warning text-dark',
                        'approved' => 'badge bg-success',
                        'rejected' => 'badge bg-danger',
                    ];
                    $class = $map[$row->status] ?? 'badge bg-secondary';

                    return "<span class='{$class}'>".ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function ($row) {
                    if ($row->status === 'pending' && auth()->check() && auth()->user()->can_approve) {
                        return '
                            <button class="btn btn-sm btn-success btn-approve-stock" data-id="'.$row->id.'"><i class="bi bi-check-lg"></i></button>
                            <button class="btn btn-sm btn-danger btn-reject-stock" data-id="'.$row->id.'"><i class="bi bi-x-lg"></i></button>
                        ';
                    }

                    return '-';
                })
                ->rawColumns(['type', 'status_badge', 'action'])
                ->make(true);
        }

        $items = ConsumableItem::orderBy('name')->get();

        return view('consumable.transactions', compact('items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consumable_item_id' => 'required|exists:consumable_items,id',
            'type' => 'required|in:in,adjustment',
            'qty' => [
                'required', 'integer',
                function ($attr, $value, $fail) use ($request) {
                    if ($request->type === 'in' && $value < 1) {
                        $fail('Qty barang masuk minimal 1.');
                    }
                    if ($request->type === 'adjustment' && $value == 0) {
                        $fail('Qty penyesuaian tidak boleh 0.');
                    }
                },
            ],
            'unit_price' => 'nullable|integer|min:0',
            'reference_number' => 'nullable|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $tx = StockTransaction::create($request->only([
            'consumable_item_id', 'type', 'qty', 'unit_price',
            'reference_number', 'date', 'notes',
        ]) + ['status' => 'pending']);

        $item = ConsumableItem::find($request->consumable_item_id);

        // Kirim notifikasi ke approvers
        $approvers = User::where('can_approve', true)->get();
        if ($approvers->isNotEmpty()) {
            Notification::send($approvers, new StockInPendingNotification($tx, $item));
        }

        Activity::logCreate('consumable', "Transaksi {$tx->type} - {$item->name} ({$tx->qty})", $tx, $tx->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Transaksi stok dicatat, menunggu approval.',
        ]);
    }

    public function approve($id)
    {
        if (! auth()->user()->can_approve) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki wewenang.'], 403);
        }

        $tx = StockTransaction::findOrFail($id);

        if ($tx->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah '.$tx->status.'.'], 422);
        }

        DB::beginTransaction();
        try {
            $tx->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            $item = ConsumableItem::findOrFail($tx->consumable_item_id);

            if ($tx->qty > 0) {
                $item->increment('current_stock', $tx->qty);
            } elseif ($tx->qty < 0) {
                $item->decrement('current_stock', abs($tx->qty));
            }

            $verb = $tx->type === 'adjustment'
                ? ($tx->qty > 0 ? 'Stok ditambah' : 'Stok dikurangi')
                : 'Stok bertambah';

            Activity::log('consumable', 'approve', "Transaksi {$tx->reference_number} disetujui - {$item->name}", $tx);

            DB::commit();

            $item->refresh();
            if ($item->current_stock <= $item->min_stock) {
                $recipients = User::whereIn('role', ['admin', 'manager', 'staff'])->get();
                Notification::send($recipients, new LowStockNotification($item));
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi disetujui. '.$verb.'.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal approve: '.$e->getMessage(),
            ], 500);
        }
    }

    public function reject($id)
    {
        if (! auth()->user()->can_approve) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki wewenang.'], 403);
        }

        $tx = StockTransaction::findOrFail($id);

        if ($tx->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah '.$tx->status.'.'], 422);
        }

        $tx->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        Activity::log('consumable', 'reject', "Transaksi {$tx->reference_number} ditolak", $tx);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi ditolak.',
        ]);
    }
}

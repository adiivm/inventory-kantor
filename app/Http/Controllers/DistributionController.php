<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\ConsumableItem;
use App\Models\DistributionHeader;
use App\Models\DistributionDetail;
use App\Models\StockTransaction;
use App\Notifications\DistributionPendingNotification;
use App\Notifications\LowStockNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\Facades\DataTables;

class DistributionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DistributionHeader::with(['details.consumableItem', 'division', 'approver']);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($row) {
                    $map = [
                        'pending' => 'badge bg-warning text-dark',
                        'approved' => 'badge bg-success',
                        'rejected' => 'badge bg-danger',
                    ];
                    $class = $map[$row->status] ?? 'badge bg-secondary';
                    $label = ucfirst($row->status);
                    return "<span class='{$class}'>{$label}</span>";
                })
                ->addColumn('items_list', function ($row) {
                    return $row->details->map(fn($d) =>
                        $d->consumableItem->name . ' x' . $d->qty
                    )->implode('<br>');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<button class="btn btn-sm btn-info me-1" onclick="detailDistribution(' . $row->id . ')" title="Detail"><i class="bi bi-eye"></i></button>';
                    if ($row->status === 'pending' && auth()->user()->can_approve) {
                        $btn .= '<button class="btn btn-sm btn-success me-1" onclick="approveDistribution(' . $row->id . ')"><i class="bi bi-check-lg"></i></button>';
                        $btn .= '<button class="btn btn-sm btn-danger me-1" onclick="rejectDistribution(' . $row->id . ')"><i class="bi bi-x-lg"></i></button>';
                    }
                    if ($row->status === 'approved' && !$row->admin_signature) {
                        $btn .= '<button class="btn btn-sm btn-primary" onclick="signDistribution(' . $row->id . ')"><i class="bi bi-pencil-square"></i></button>';
                    }
                    $btn .= '<button class="btn btn-sm btn-secondary ms-1" onclick="printDistribution(' . $row->id . ')" title="Cetak"><i class="bi bi-printer"></i></button>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'items_list', 'action'])
                ->make(true);
        }

        $divisions = Division::orderBy('name')->get();
        $items = ConsumableItem::where('current_stock', '>', 0)->orderBy('name')->get();
        $heldBies = \App\Models\HeldBy::orderBy('name')->get();

        return view('consumable.distributions', compact('divisions', 'items', 'heldBies'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'requester_name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'items' => 'required|array|min:1',
            'items.*.consumable_item_id' => 'required|exists:consumable_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $referenceNumber = 'DIST-' . now()->format('Ymd') . '-' . str_pad(
                DistributionHeader::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT
            );

            $header = DistributionHeader::create([
                'reference_number' => $referenceNumber,
                'requester_name' => $request->requester_name,
                'division_id' => $request->division_id,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                DistributionDetail::create([
                    'distribution_header_id' => $header->id,
                    'consumable_item_id' => $item['consumable_item_id'],
                    'qty' => $item['qty'],
                ]);
            }

            DB::commit();

            $approvers = User::where('can_approve', true)->get();
            if ($approvers->isNotEmpty()) {
                Notification::send($approvers, new DistributionPendingNotification($header));
            }

            return response()->json([
                'success' => true,
                'message' => 'Permintaan distribusi berhasil dibuat. Menunggu approval.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permintaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function approve($id)
    {
        if (!auth()->user()->can_approve) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang untuk approve.',
            ], 403);
        }

        $header = DistributionHeader::with('details.consumableItem')->findOrFail($id);

        if ($header->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ini sudah ' . $header->status . '.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $errors = [];
            foreach ($header->details as $detail) {
                $item = $detail->consumableItem;
                if ($item->current_stock < $detail->qty) {
                    $errors[] = "{$item->name}: stok tersedia {$item->current_stock}, diminta {$detail->qty}.";
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi untuk beberapa barang:',
                    'errors' => $errors,
                ], 422);
            }

            foreach ($header->details as $detail) {
                $item = $detail->consumableItem;
                $item->decrement('current_stock', $detail->qty);

                StockTransaction::create([
                    'consumable_item_id' => $item->id,
                    'type' => 'out',
                    'qty' => $detail->qty,
                    'date' => now()->toDateString(),
                    'reference_number' => $header->reference_number,
                    'notes' => "Distribusi: {$header->reference_number}",
                ]);

                $item->refresh();
                if ($item->current_stock <= $item->min_stock) {
                    $recipients = User::whereIn('role', ['admin', 'staff'])->get();
                    Notification::send($recipients, new LowStockNotification($item));
                }
            }

            $header->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            // Tandai semua notifikasi distribusi ini sebagai sudah dibaca
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('type', 'App\Notifications\DistributionPendingNotification')
                ->where('data->distribution_id', $id)
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Distribusi berhasil disetujui. Stok telah dikurangi.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reject($id)
    {
        if (!auth()->user()->can_approve) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki wewenang.',
            ], 403);
        }

        $header = DistributionHeader::findOrFail($id);

        if ($header->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan ini sudah ' . $header->status . '.',
            ], 422);
        }

        $header->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Tandai semua notifikasi distribusi ini sebagai sudah dibaca
        \Illuminate\Support\Facades\DB::table('notifications')
            ->where('type', 'App\Notifications\DistributionPendingNotification')
            ->where('data->distribution_id', $id)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan distribusi ditolak.',
        ]);
    }

    public function saveSignature(Request $request, $id)
    {
        $header = DistributionHeader::findOrFail($id);

        if ($header->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya distribusi yang sudah approved yang bisa ditandatangani.',
            ], 422);
        }

        $rules = [];
        $data = [];

        if ($request->filled('admin_signature')) {
            $rules['admin_signature'] = 'required|string';
            $data['admin_signature'] = $request->admin_signature;
        }

        if ($request->filled('receiver_signature')) {
            $rules['receiver_signature'] = 'required|string';
            $data['receiver_signature'] = $request->receiver_signature;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->filled('admin_signature') && $request->filled('receiver_signature')) {
            $data['received_at'] = now();
        }

        $header->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan berhasil disimpan.',
            'received_at' => $header->received_at,
        ]);
    }

    public function show($id)
    {
        $header = DistributionHeader::with(['details.consumableItem', 'division', 'approver'])->findOrFail($id);

        return response()->json($header);
    }

    public function printPdf($id)
    {
        $header = DistributionHeader::with(['details.consumableItem', 'division', 'approver'])->findOrFail($id);

        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate(route('distribution.verify', $id)));

        $pdf = Pdf::loadView('consumable.pdf_bukti', compact('header', 'qrCode'));

        return $pdf->download('Bukti-Serah-Terima-' . $header->reference_number . '.pdf');
    }
}

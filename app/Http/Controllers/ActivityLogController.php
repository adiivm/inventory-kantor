<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ActivityLog::latest('created_at');

            if ($request->filled('module')) {
                $query->where('module', $request->module);
            }

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('date_start')) {
                $query->whereDate('created_at', '>=', $request->date_start);
            }

            if ($request->filled('date_end')) {
                $query->whereDate('created_at', '<=', $request->date_end);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('created_at', fn ($row) => $row->created_at->format('d/m/Y H:i'))
                ->addColumn('module_badge', function ($row) {
                    $colors = [
                        'asset' => 'bg-primary',
                        'consumable' => 'bg-success',
                        'distribution' => 'bg-warning text-dark',
                        'master' => 'bg-info text-dark',
                        'user' => 'bg-secondary',
                    ];
                    $color = $colors[$row->module] ?? 'bg-dark';

                    return "<span class='badge {$color}'>".ucfirst($row->module).'</span>';
                })
                ->addColumn('action_badge', function ($row) {
                    $colors = [
                        'create' => 'bg-success',
                        'update' => 'bg-primary',
                        'delete' => 'bg-danger',
                        'approve' => 'bg-success',
                        'reject' => 'bg-danger',
                        'archive' => 'bg-warning text-dark',
                        'restore' => 'bg-info text-dark',
                    ];
                    $color = $colors[$row->action] ?? 'bg-secondary';

                    return "<span class='badge {$color}'>".ucfirst($row->action).'</span>';
                })
                ->addColumn('detail', function ($row) {
                    $hasOld = $row->old_values && count($row->old_values) > 0;
                    $hasNew = $row->new_values && count($row->new_values) > 0;
                    if (! $hasOld && ! $hasNew) {
                        return '-';
                    }

                    return '<button class="btn btn-sm btn-outline-info btn-detail" data-id="'.$row->id.'"><i class="bi bi-eye"></i></button>';
                })
                ->rawColumns(['module_badge', 'action_badge', 'detail'])
                ->make(true);
        }

        return view('activity_logs');
    }

    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);

        return response()->json($log);
    }
}

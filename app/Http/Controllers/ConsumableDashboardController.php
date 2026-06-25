<?php

namespace App\Http\Controllers;

use App\Models\ConsumableItem;
use App\Models\DistributionDetail;
use App\Models\DistributionHeader;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;

class ConsumableDashboardController extends Controller
{
    public function index()
    {
        // Row 1: KPI
        $totalItems = ConsumableItem::count();
        $lowStockCount = ConsumableItem::whereColumn('current_stock', '<=', 'min_stock')->count();
        $pendingDistributionCount = DistributionHeader::where('status', 'pending')->count();

        // Row 2: Urgent Restock
        $urgentItems = ConsumableItem::whereColumn('current_stock', '<=', 'min_stock')
            ->with('category')
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        // Row 2: Pending Approvals
        $pendingDistributions = DistributionHeader::where('status', 'pending')
            ->with('division')
            ->latest()
            ->limit(5)
            ->get();

        // Row 3: Outflow Trend (7 hari terakhir)
        $outflowRaw = StockTransaction::select(DB::raw('DATE(date) as tgl'), DB::raw('SUM(qty) as total'))
            ->where('type', 'out')
            ->where('date', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('tgl')
            ->pluck('total', 'tgl');

        $outflowLabels = collect();
        $outflowData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('D');
            $outflowLabels->push($label);
            $outflowData->push($outflowRaw->get($date, 0));
        }

        // Row 3: Top 5 Requested Items (hanya approved)
        $topRequested = DistributionDetail::select('consumable_item_id', DB::raw('SUM(qty) as total_qty'))
            ->with('consumableItem')
            ->whereHas('header', fn ($q) => $q->where('status', 'approved'))
            ->groupBy('consumable_item_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('consumable.dashboard', compact(
            'totalItems',
            'lowStockCount',
            'pendingDistributionCount',
            'urgentItems',
            'pendingDistributions',
            'outflowLabels',
            'outflowData',
            'topRequested'
        ));
    }
}

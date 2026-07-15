<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CashRegisterSession;
use App\Models\Inventory;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard:stats', 60, function () {
            $todaySales = Sale::where('type', 'sale')
                ->whereDate('created_at', Carbon::today())
                ->sum('total');

            $inventoryAlerts = Inventory::where('stock', '<', 5)->count();

            $activeRegisters = CashRegisterSession::where('status', 'open')->count();

            $start = Carbon::today()->subDays(6);
            $end = Carbon::today()->endOfDay();

            $salesByDay = Sale::where('type', 'sale')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->pluck('total', 'date');

            $days = [];
            $salesData = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                $days[] = $date->format('D d');
                $salesData[] = (float) ($salesByDay[$dateKey] ?? 0);
            }

            return compact('todaySales', 'inventoryAlerts', 'activeRegisters', 'days', 'salesData');
        });

        return view('dashboard', $stats);
    }
}

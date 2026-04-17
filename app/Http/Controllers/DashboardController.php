<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Inventory;
use App\Models\CashRegisterSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main system dashboard with real-time stats.
     */
    public function index()
    {
        // 1. Today's Sales Total (excludes 'waste')
        $todaySales = Sale::where('type', 'sale')
            ->whereDate('created_at', Carbon::today())
            ->sum('total');

        // 2. Inventory Alerts (Stock < 5)
        // In a real app, this threshold could be in settings table.
        $inventoryAlerts = Inventory::where('stock', '<', 5)->count();

        // 3. Active Cash Registers (Open Sessions)
        $activeRegisters = CashRegisterSession::where('status', 'open')->count();

        // 4. Chart Data (Last 7 days of sales)
        $days = [];
        $salesData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = $date->format('D d'); // Ex: Mon 25
            
            $total = Sale::where('type', 'sale')
                ->whereDate('created_at', $date)
                ->sum('total');
                
            $salesData[] = (float)$total;
        }

        return view('dashboard', compact(
            'todaySales', 
            'inventoryAlerts', 
            'activeRegisters', 
            'days', 
            'salesData'
        ));
    }
}

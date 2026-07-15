<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['product', 'branch']);

        // Optional filtering by branch if parameter is present
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Search by product name
        if ($request->filled('search')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            });
        }

        $inventories = $query->orderBy('branch_id')
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.index', compact('inventories'));
    }
}

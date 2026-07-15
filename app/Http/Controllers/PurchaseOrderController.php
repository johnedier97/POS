<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchases = PurchaseOrder::with(['supplier', 'branch'])->latest()->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $branches = Branch::all();
        $suppliers = Supplier::all();
        $products = Product::all();

        return view('purchases.create', compact('branches', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['quantity'] * $item['unit_cost'];
            }

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'branch_id' => $request->branch_id,
                'date' => now(),
                'status' => 'pending',
                'total' => $total,
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.show', $purchaseOrder)
                ->with('success', 'Orden de Compra creada exitosamente. Esperando recepción.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error al crear la orden: '.$e->getMessage())->withInput();
        }
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load(['details.product', 'supplier', 'branch']);

        return view('purchases.show', compact('purchase'));
    }

    public function receive(PurchaseOrder $purchase)
    {
        if ($purchase->status === 'received') {
            return redirect()->back()->with('error', 'Esta orden ya fue recibida.');
        }

        try {
            DB::beginTransaction();

            // Afectar inventario
            foreach ($purchase->details as $detail) {
                $inventory = Inventory::firstOrCreate(
                    ['branch_id' => $purchase->branch_id, 'product_id' => $detail->product_id],
                    ['stock' => 0]
                );

                $inventory->stock += $detail->quantity;
                $inventory->save();
            }

            $purchase->status = 'received';
            $purchase->save();

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Mercancía recibida e inventario actualizado en sucursal.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Hubo un error al recibir la mercancía: '.$e->getMessage());
        }
    }

    public function revert(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'received') {
            return redirect()->back()->with('error', 'Solo las órdenes recibidas pueden ser revertidas.');
        }

        try {
            DB::beginTransaction();

            // Revertir inventario
            foreach ($purchase->details as $detail) {
                $inventory = Inventory::where('branch_id', $purchase->branch_id)
                    ->where('product_id', $detail->product_id)
                    ->first();
                if ($inventory) {
                    $inventory->stock -= $detail->quantity;
                    $inventory->save();
                }
            }

            $purchase->status = 'pending';
            $purchase->save();

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Orden revertida exitosamente. El inventario ha sido restado.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Hubo un error al revertir la orden: '.$e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Payment;
use App\Models\Inventory;
use App\Models\ProductComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $session = Auth::user()->sessions()->where('status', 'open')->latest()->first();
        if (!$session) {
            return redirect()->route('dashboard')->with('error', 'Debes abrir tu turno de caja antes de entrar al módulo POS.');
        }

        $products = Product::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        // Fallback for tests if no payment methods exist
        if ($paymentMethods->isEmpty()) {
            PaymentMethod::create(['name' => 'Efectivo', 'is_active' => true]);
            PaymentMethod::create(['name' => 'Tarjeta de Crédito', 'is_active' => true]);
            PaymentMethod::create(['name' => 'Transferencia', 'is_active' => true]);
            $paymentMethods = PaymentMethod::where('is_active', true)->get();
        }
        $customers = Customer::all();

        return view('pos.index', compact('products', 'paymentMethods', 'session', 'customers'));
    }

    public function store(Request $request)
    {
        $session = Auth::user()->sessions()->where('status', 'open')->latest()->first();
        if (!$session) {
            return response()->json(['error' => 'No hay caja abierta activa para realizar la transacción.'], 403);
        }

        $request->validate([
            'sale.type' => 'required|in:sale,waste',
            'sale.total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'payments' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Sale
            $sale = Sale::create([
                'session_id' => $session->id,
                'user_id' => Auth::id(),
                'customer_id' => $request->input('sale.customer_id'),
                'type' => $request->input('sale.type'), // sale or waste
                'total' => $request->input('sale.total'),
                'is_electronic_invoiced' => $request->input('sale.is_electronic_invoiced', false)
            ]);

            // 2. Create Sale Details & Calculate Inventory Consumption
            $branch_id = $session->cashRegister->branch_id;

            foreach ($request->input('items') as $item) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'cost' => $item['cost'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Reduce Inventory Recurrently
                $this->processInventory($item['product_id'], $branch_id, $item['quantity']);
            }

            // 3. Register Payments
            if ($request->input('sale.type') === 'sale' && $request->filled('payments')) {
                foreach ($request->input('payments') as $payment) {
                    Payment::create([
                        'sale_id' => $sale->id,
                        'payment_method_id' => $payment['payment_method_id'],
                        'amount' => $payment['amount']
                    ]);
                }
            }

            // Update session final calculated balance directly based on pure cash?
            // Usually cash flow implies only Cash payments sum to the box. Assuming for now total sales sum.
            
            DB::commit();
            
            return response()->json([
                'success' => true, 
                'sale_id' => $sale->id, 
                'message' => '¡Venta procesada y almacenada con éxito!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Resuelve inventario para productos compuestos/recetas de forma recursiva.
     */
    private function processInventory($product_id, $branch_id, $consume_quantity)
    {
        $product = Product::with('components')->find($product_id);

        if (!$product) return;

        if ($product->is_composite && $product->components->count() > 0) {
            // Recipe: Reduce ingredients
            foreach ($product->components as $component) {
                $total_needed = $component->quantity * $consume_quantity;
                $this->processInventory($component->child_product_id, $branch_id, $total_needed);
            }
        } else {
            // Simple product: Reduce stock directly
            $inv = Inventory::firstOrCreate(
                ['product_id' => $product_id, 'branch_id' => $branch_id],
                ['stock' => 0]
            );
            $inv->stock -= $consume_quantity;
            $inv->save();
        }
    }
}

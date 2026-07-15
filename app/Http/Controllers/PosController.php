<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $session = Auth::user()->sessions()->where('status', 'open')->latest()->first();
        if (! $session) {
            return redirect()->route('dashboard')->with('error', 'Debes abrir tu turno en una caja antes de entrar al modulo de ventas.');
        }

        $products = Product::all();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
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
        if (! $session) {
            return response()->json(['error' => 'No hay caja abierta activa para realizar la transacción.'], 403);
        }

        $request->validate([
            'sale.type' => 'required|in:sale,waste',
            'sale.total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'payments' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::create([
                'session_id' => $session->id,
                'user_id' => Auth::id(),
                'customer_id' => $request->input('sale.customer_id'),
                'type' => $request->input('sale.type'),
                'total' => $request->input('sale.total'),
                'is_electronic_invoiced' => $request->input('sale.is_electronic_invoiced', false),
            ]);

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
            }

            $productConsumption = [];
            $products = Product::whereIn('id', array_column($request->input('items'), 'product_id'))
                ->with('components.childProduct.components.childProduct')
                ->get()
                ->keyBy('id');

            foreach ($request->input('items') as $item) {
                $pid = (int) $item['product_id'];
                $qty = (float) $item['quantity'];
                if (isset($products[$pid])) {
                    $itemConsumption = $this->collectProductIds($products[$pid], $qty);
                    foreach ($itemConsumption as $leafId => $leafQty) {
                        $productConsumption[$leafId] = ($productConsumption[$leafId] ?? 0) + $leafQty;
                    }
                }
            }

            $this->processInventory($productConsumption, $branch_id);

            if ($request->input('sale.type') === 'sale' && $request->filled('payments')) {
                foreach ($request->input('payments') as $payment) {
                    Payment::create([
                        'sale_id' => $sale->id,
                        'payment_method_id' => $payment['payment_method_id'],
                        'amount' => $payment['amount'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'message' => '¡Venta procesada y almacenada con éxito!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function collectProductIds(Product $product, float $quantity): array
    {
        $map = [];

        if ($product->is_composite && $product->components->count() > 0) {
            foreach ($product->components as $component) {
                if ($component->childProduct) {
                    $subMap = $this->collectProductIds($component->childProduct, $component->quantity * $quantity);
                    foreach ($subMap as $pid => $qty) {
                        $map[$pid] = ($map[$pid] ?? 0) + $qty;
                    }
                }
            }
        } else {
            $map[$product->id] = ($map[$product->id] ?? 0) + $quantity;
        }

        return $map;
    }

    private function processInventory(array $productConsumption, int $branch_id): void
    {
        if (empty($productConsumption)) {
            return;
        }

        $productIds = array_keys($productConsumption);

        $inventories = Inventory::whereIn('product_id', $productIds)
            ->where('branch_id', $branch_id)
            ->get()
            ->keyBy('product_id');

        foreach ($productConsumption as $productId => $quantity) {
            if (isset($inventories[$productId])) {
                $inventories[$productId]->stock -= $quantity;
                $inventories[$productId]->save();
            } else {
                Inventory::create([
                    'product_id' => $productId,
                    'branch_id' => $branch_id,
                    'stock' => -$quantity,
                ]);
            }
        }
    }
}

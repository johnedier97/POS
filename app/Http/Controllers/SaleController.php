<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of sales (Sales History).
     */
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'session.cashRegister.branch'])
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * Display the specified sale details.
     */
    public function show(Sale $sale)
    {
        $sale->load([
            'details.product',
            'payments.paymentMethod',
            'customer',
            'session.cashRegister.branch',
            'user',
        ]);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the printable receipt for a sale.
     */
    public function receipt(Sale $sale)
    {
        // Security: Only the owner of the sale can print it
        if ($sale->user_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver este comprobante.');
        }

        $sale->load([
            'details.product',
            'payments.paymentMethod',
            'customer',
            'session.cashRegister.branch',
        ]);

        // Get business settings (name, logo, address, footer_note, etc.)
        $settings = Setting::pluck('value', 'key');

        return view('sales.receipt', compact('sale', 'settings'));
    }

    /**
     * Mock: Mark a sale as electronically invoiced.
     */
    public function invoiceMock(Sale $sale)
    {
        if ($sale->user_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado.'], 403);
        }

        if ($sale->is_electronic_invoiced) {
            return response()->json(['error' => 'Esta venta ya fue facturada electrónicamente.'], 422);
        }

        // MOCK: In production, this would call DIAN/AFIP API
        $sale->update([
            'is_electronic_invoiced' => true,
        ]);

        return response()->json([
            'success' => true,
            'cufe' => strtoupper(bin2hex(random_bytes(10))), // Mock CUFE
            'invoice_no' => 'FE-'.str_pad($sale->id, 6, '0', STR_PAD_LEFT),
            'message' => 'Factura Electrónica emitida (simulación). CUFE asignado.',
        ]);
    }
}

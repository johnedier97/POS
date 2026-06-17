<?php

namespace App\Http\Controllers;

use App\Models\CashRegisterSession;
use App\Models\CashRegister;
use App\Models\Payment;
use App\Models\User;
use App\Mail\CashRegisterClosedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CashRegisterSessionController extends Controller
{
    public function create()
    {
        // Verifica si el usuario actual tiene una sesión abierta
        $activeSession = Auth::user()->sessions()->where('status', 'open')->latest()->first();
        if ($activeSession) {
            return redirect()->route('dashboard')->with('info', 'Ya tienes una caja abierta en turno.');
        }

        // Mostrar solo cajas activas que NO tengan una sesión abierta
        $registers = CashRegister::with('branch')
            ->where('is_active', true)
            ->whereDoesntHave('sessions', function($q) {
                $q->where('status', 'open');
            })->get();

        return view('sessions.create', compact('registers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'initial_balance' => 'required|numeric|min:0'
        ]);

        CashRegisterSession::create([
            'cash_register_id' => $request->cash_register_id,
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'initial_balance' => $request->initial_balance,
            'status' => 'open'
        ]);

        return redirect()->route('dashboard')->with('success', 'Caja abierta exitosamente. ¡El turno ha comenzado!');
    }

    public function edit(CashRegisterSession $session)
    {
        if ($session->status !== 'open') {
            return redirect()->route('dashboard')->with('error', 'Esta caja ya está cerrada.');
        }

        // Logic for closing box view
        return view('sessions.edit', compact('session'));
    }

    public function update(Request $request, CashRegisterSession $session)
    {
        $request->validate([
            'final_reported_balance' => 'required|numeric|min:0'
        ]);

        // Calculate expected balance based on cash payments + initial balance
        $cashPaymentMethodId = \App\Models\PaymentMethod::where('name', 'Efectivo')->value('id');
        
        $totalCashSales = 0;
        if ($cashPaymentMethodId) {
            $totalCashSales = \App\Models\Payment::whereHas('sale', function ($query) use ($session) {
                $query->where('session_id', $session->id)->where('type', 'sale');
            })->where('payment_method_id', $cashPaymentMethodId)->sum('amount');
        } else {
            // Fallback if 'Efectivo' payment method is missing
            $totalCashSales = \App\Models\Sale::where('session_id', $session->id)->where('type', 'sale')->sum('total');
        }

        $calculated_balance = $session->initial_balance + $totalCashSales;

        // Verify if reported matches calculated (with rounding to avoid float precision issues)
        if (round($request->final_reported_balance, 2) !== round($calculated_balance, 2)) {
            return redirect()->back()
                ->with('error', 'El monto FÍSICO reportado ($' . number_format($request->final_reported_balance, 2) . ') no coincide con el balance CALCULADO del sistema ($' . number_format($calculated_balance, 2) . '). Verifica el dinero en gaveta o la validez de las ventas antes de cerrar.');
        }

        $session->update([
            'closed_at' => now(),
            'final_reported_balance' => $request->final_reported_balance,
            'final_calculated_balance' => $calculated_balance, 
            'status' => 'closed'
        ]);

        // Notificar a los administradores vía Cola (Queue)
        try {
            $admins = User::whereHas('role', function($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new CashRegisterClosedMail($session, $totalCashSales, $request->final_reported_balance));
            }
        } catch (\Exception $e) {
            \Log::error("Error enviando correos de cierre de caja: " . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'Turno cerrado. Caja arqueada exitosamente con un balance de $' . number_format($calculated_balance, 2));
    }
}

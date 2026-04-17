<?php

namespace App\Http\Controllers;

use App\Models\CashRegisterSession;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $session->update([
            'closed_at' => now(),
            'final_reported_balance' => $request->final_reported_balance,
            // TODO: final_calculated_balance will be aggregated later when Sales module is done
            'final_calculated_balance' => $session->initial_balance, 
            'status' => 'closed'
        ]);

        return redirect()->route('dashboard')->with('success', 'Turno cerrado. Caja arqueada exitosamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Branch;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index()
    {
        $registers = CashRegister::with('branch', 'currentSession')->latest()->paginate(10);
        return view('registers.index', compact('registers'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('registers.form', [
            'register' => new CashRegister(),
            'branches' => $branches
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);
        
        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        
        CashRegister::create($data);
        
        return redirect()->route('registers.index')->with('success', 'Caja registradora creada exitosamente.');
    }

    public function edit(CashRegister $register)
    {
        $branches = Branch::all();
        return view('registers.form', compact('register', 'branches'));
    }

    public function update(Request $request, CashRegister $register)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);
        
        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        
        $register->update($data);
        
        return redirect()->route('registers.index')->with('success', 'Caja registradora actualizada exitosamente.');
    }

    public function destroy(CashRegister $register)
    {
        $register->delete();
        return redirect()->route('registers.index')->with('success', 'Caja registradora eliminada exitosamente.');
    }
}

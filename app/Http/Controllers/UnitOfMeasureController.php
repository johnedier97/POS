<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasure;
use Illuminate\Http\Request;

class UnitOfMeasureController extends Controller
{
    public function index()
    {
        $units = UnitOfMeasure::latest()->paginate(10);
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.form', ['unit' => new UnitOfMeasure()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10'
        ]);

        UnitOfMeasure::create($request->all());

        return redirect()->route('units.index')->with('success', 'Unidad creada exitosamente.');
    }

    public function edit(UnitOfMeasure $unit)
    {
        return view('units.form', compact('unit'));
    }

    public function update(Request $request, UnitOfMeasure $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10'
        ]);

        $unit->update($request->all());

        return redirect()->route('units.index')->with('success', 'Unidad actualizada exitosamente.');
    }

    public function destroy(UnitOfMeasure $unit)
    {
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unidad eliminada exitosamente.');
    }
}

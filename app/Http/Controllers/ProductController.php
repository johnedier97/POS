<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use App\Models\ProductComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('unitOfMeasure')->latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $units = UnitOfMeasure::all();
        $allProducts = Product::all(); // For selecting components
        return view('products.form', [
            'product' => new Product(),
            'units' => $units,
            'allProducts' => $allProducts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit_of_measure_id' => 'nullable|exists:unit_of_measures,id',
            'image' => 'nullable|image|max:2048',
            'components' => 'nullable|array',
        ]);

        $data = $request->except(['image', 'components']);
        $data['is_composite'] = $request->boolean('is_composite');
        
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        
        $product = Product::create($data);

        if ($data['is_composite'] && $request->filled('components')) {
            foreach ($request->components as $component) {
                if(isset($component['id']) && isset($component['quantity'])) {
                    ProductComponent::create([
                        'parent_product_id' => $product->id,
                        'child_product_id' => $component['id'],
                        'quantity' => $component['quantity']
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        $units = UnitOfMeasure::all();
        $allProducts = Product::where('id', '!=', $product->id)->get();
        $product->load('components.childProduct');
        
        return view('products.form', compact('product', 'units', 'allProducts'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'unit_of_measure_id' => 'nullable|exists:unit_of_measures,id',
            'image' => 'nullable|image|max:2048',
            'components' => 'nullable|array',
        ]);

        $data = $request->except(['image', 'components']);
        $data['is_composite'] = $request->boolean('is_composite');

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Update components if composite
        if ($data['is_composite']) {
            $product->components()->delete(); // Clear old components
            if ($request->filled('components')) {
                foreach ($request->components as $component) {
                    if(isset($component['id']) && isset($component['quantity'])) {
                        ProductComponent::create([
                            'parent_product_id' => $product->id,
                            'child_product_id' => $component['id'],
                            'quantity' => $component['quantity']
                        ]);
                    }
                }
            }
        } else {
            // Not composite anymore, delete components
            $product->components()->delete();
        }

        return redirect()->route('products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}

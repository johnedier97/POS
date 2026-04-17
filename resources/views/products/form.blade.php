<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ $product->exists ? __('Editar Producto') : __('Crear Producto') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        isComposite: {{ old('is_composite', $product->is_composite) ? 'true' : 'false' }},
        components: [
            @if(old('components'))
                @foreach(old('components') as $comp)
                    { id: '{{ $comp['id'] ?? '' }}', quantity: '{{ $comp['quantity'] ?? '' }}' },
                @endforeach
            @elseif($product->exists && $product->components)
                @foreach($product->components as $comp)
                    { id: '{{ $comp->child_product_id }}', quantity: '{{ $comp->quantity }}' },
                @endforeach
            @endif
        ],
        addComponent() {
            this.components.push({ id: '', quantity: 1 });
        },
        removeComponent(index) {
            this.components.splice(index, 1);
        }
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                
                <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @if($product->exists)
                        @method('PUT')
                    @endif

                    <!-- Basic Info -->
                    <div>
                        <h3 class="text-lg leading-6 font-bold text-gray-900 border-b pb-2">Información Básica</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700">Nombre del Producto *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                                @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="unit_of_measure_id" class="block text-sm font-semibold text-gray-700">Unidad de Medida (Opcional)</label>
                                <select name="unit_of_measure_id" id="unit_of_measure_id" class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_of_measure_id', $product->unit_of_measure_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->abbreviation }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_of_measure_id') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-semibold text-gray-700">Descripción</label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">{{ old('description', $product->description) }}</textarea>
                                @error('description') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Image -->
                    <div>
                        <h3 class="text-lg leading-6 font-bold text-gray-900 border-b pb-2">Precios e Imagen</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <label for="cost" class="block text-sm font-semibold text-gray-700">Costo Base ($) *</label>
                                <input type="number" step="0.01" name="cost" id="cost" value="{{ old('cost', $product->cost ?? 0) }}" required
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                                @error('cost') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-semibold text-gray-700">Precio de Venta ($) *</label>
                                <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price ?? 0) }}" required
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150">
                                @error('price') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700">Fotografía del Producto</label>
                                @if($product->image_path)
                                    <div class="mb-3 mt-2">
                                        <img src="{{ Storage::url($product->image_path) }}" alt="Current Image" class="w-32 h-32 object-cover rounded-lg shadow-sm border border-gray-200">
                                    </div>
                                @endif
                                <input type="file" name="image" id="image" accept="image/*"
                                    class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                                @error('image') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Recipe / Composite Checkbox -->
                    <div>
                        <h3 class="text-lg leading-6 font-bold text-gray-900 border-b pb-2">Producción y Receta</h3>
                        <div class="mt-4 flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_composite" name="is_composite" type="checkbox" value="1" x-model="isComposite"
                                    class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded transition duration-150">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_composite" class="font-bold text-gray-700">Es un producto compuesto / Receta</label>
                                <p class="text-gray-500">Marca esta opción si este producto se elabora a partir de otros productos del inventario (ej. Fresas con Crema, Hamburguesa, etc.)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Components Dynamic Builder -->
                    <div x-show="isComposite" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;" class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-indigo-900">Ingredientes / Componentes</h4>
                            <button type="button" @click="addComponent()" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1.5 px-3 rounded shadow transition">
                                + Agregar Componente
                            </button>
                        </div>

                        <template x-for="(component, index) in components" :key="index">
                            <div class="flex items-center gap-4 mb-3 pb-3 border-b border-indigo-100/50">
                                <div class="flex-grow">
                                    <label class="sr-only">Producto</label>
                                    <select x-model="component.id" :name="'components['+index+'][id]'" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                        <option value="">Selecciona un sub-producto</option>
                                        @foreach($allProducts as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} (@if($p->unitOfMeasure){{ $p->unitOfMeasure->abbreviation }}@else Unidades @endif)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-32">
                                    <label class="sr-only">Cantidad</label>
                                    <input type="number" step="0.0001" x-model="component.quantity" :name="'components['+index+'][quantity]'" class="block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Cantidad" required>
                                </div>
                                <div>
                                    <button type="button" @click="removeComponent(index)" class="text-rose-500 hover:text-rose-700 bg-rose-50 p-2 rounded-lg transition" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <p x-show="components.length === 0" class="text-sm text-indigo-400 italic">No hay ingredientes añadidos. Haz clic en agregar.</p>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                        <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700 font-medium transition">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-8 rounded-lg shadow-md transition duration-300 text-lg">
                            {{ $product->exists ? 'Guardar Cambios' : 'Crear Producto' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>

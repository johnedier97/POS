<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nueva Orden de Compra') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="purchaseForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf

                <!-- Header Document Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Documento</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <x-input-label for="supplier_id" :value="__('Proveedor')" />
                                <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Seleccione un proveedor...</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="branch_id" :value="__('Sucursal de Destino (Bodega)')" />
                                <select id="branch_id" name="branch_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Seleccione una sucursal...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Productos de la Orden</h3>
                            <button type="button" @click="addRow()" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-bold py-2 px-4 rounded transition">
                                + Agregar Fila
                            </button>
                        </div>
                        
                        <x-input-error :messages="$errors->get('items')" class="mt-2 text-center" />

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Cantidad</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-48">Costo Unit. ($)</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-32">Subtotal</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase w-20">X</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="item.id">
                                        <tr class="border-b border-gray-100">
                                            <td class="px-4 py-2">
                                                <select x-model="item.product_id" :name="'items['+index+'][product_id]'" class="block w-full text-sm border-gray-300 focus:border-indigo-500 rounded-md" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="0.01" min="0.01" x-model.number="item.quantity" :name="'items['+index+'][quantity]'" class="block w-full text-sm border-gray-300 focus:border-indigo-500 rounded-md" required>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" step="0.01" min="0" x-model.number="item.unit_cost" :name="'items['+index+'][unit_cost]'" class="block w-full text-sm border-gray-300 focus:border-indigo-500 rounded-md" required>
                                            </td>
                                            <td class="px-4 py-2 text-gray-700 font-semibold align-middle" x-text="'$' + (item.quantity * item.unit_cost).toFixed(2)">
                                            </td>
                                            <td class="px-4 py-2 text-right align-middle">
                                                <button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700 font-bold" title="Eliminar">-</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- General Totals -->
                        <div class="mt-6 flex justify-end">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 min-w-[250px]">
                                <div class="flex justify-between items-center text-xl font-black text-gray-800">
                                    <span>Total:</span>
                                    <span x-text="'$' + calculateTotal().toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none disabled:opacity-25 transition mr-4">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none transition">
                        Registrar Orden
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchaseForm', () => ({
                items: [
                    { id: Date.now(), product_id: '', quantity: 1, unit_cost: 0 }
                ],
                addRow() {
                    this.items.push({ id: Date.now(), product_id: '', quantity: 1, unit_cost: 0 });
                },
                removeRow(index) {
                    if(this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        alert('La orden debe tener al menos un producto.');
                    }
                },
                calculateTotal() {
                    return this.items.reduce((acc, item) => {
                        let total = item.quantity * item.unit_cost;
                        return acc + (isNaN(total) ? 0 : total);
                    }, 0);
                }
            }))
        })
    </script>
    @endpush
</x-app-layout>

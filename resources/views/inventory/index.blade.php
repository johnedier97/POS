<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoreo de Inventario Físico') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Barra de Herramientas y Filtros (Solo Diseño) -->
            <div class="bg-white p-4 shadow-sm sm:rounded-lg mb-6 border border-gray-200 flex flex-col sm:flex-row justify-between items-center">
                <form method="GET" action="{{ route('inventory.index') }}" class="w-full sm:w-1/2 flex space-x-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar nombre de producto..." class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-white uppercase hover:bg-indigo-700 transition">
                        Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">Limpiar</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th> <!-- Image -->
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sucursal (Ubicación)</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Disponible</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Última Actualización</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($inventories as $inventory)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap w-20">
                                    @if($inventory->product && $inventory->product->image_path)
                                        <img src="{{ Storage::url($inventory->product->image_path) }}" alt="{{ $inventory->product->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold border border-gray-300">
                                            {{ substr($inventory->product->name ?? '?', 0, 1) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $inventory->product->name ?? 'Desconocido' }}</div>
                                    <div class="text-xs text-gray-500">Unidad: {{ $inventory->product->unitOfMeasure->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full inline-block">{{ $inventory->branch->name ?? 'Sin Sucursal' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-lg font-black text-gray-800">
                                    {{ rtrim(rtrim(number_format($inventory->stock, 2), '0'), '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($inventory->stock <= 0)
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">
                                            Agotado!
                                        </span>
                                    @elseif($inventory->stock <= 10)
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">
                                            Poco Stock
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    {{ $inventory->updated_at->diffForHumans() }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    No hay existencias registradas en ninguna sucursal todavía.<br>
                                    Crea una orden de compra y recíbela para inflar el inventario.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $inventories->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

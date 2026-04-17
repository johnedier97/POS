<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Historial de Ventas y Novedades') }}
            </h2>
            <a href="{{ route('pos.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition shadow-sm">
                Ir al POS
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form action="{{ route('sales.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Tipo</label>
                        <select name="type" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Ventas</option>
                            <option value="waste" {{ request('type') == 'waste' ? 'selected' : '' }}>Novedades</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Desde</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Hasta</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-grow bg-indigo-50 text-indigo-700 font-bold py-2 px-4 rounded-lg border border-indigo-200 hover:bg-indigo-100 transition">
                            Filtrar
                        </button>
                        <a href="{{ route('sales.index') }}" class="bg-gray-50 text-gray-500 font-bold py-2 px-4 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Total</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Facturado</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $sale->created_at->format('d/m/Y') }}<br>
                                    <span class="text-xs text-gray-400">{{ $sale->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full {{ $sale->type == 'sale' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $sale->type == 'sale' ? 'Venta' : 'Novedad' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $sale->customer->name ?? 'Consumidor Final' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">
                                    ${{ number_format($sale->total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($sale->is_electronic_invoiced)
                                        <span class="text-emerald-500" title="Factura Electrónica Emitida">
                                            <svg class="h-5 w-5 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        </span>
                                    @else
                                        <span class="text-gray-300" title="No Facturado">
                                            <svg class="h-5 w-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm">
                                        Ver Recibo
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                    No se encontraron registros de ventas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

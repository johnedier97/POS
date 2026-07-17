<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight border-l-4 border-indigo-600 pl-4">
                {{ __('Historial de Ventas') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('pos.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg transition-all transform hover:scale-105 text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Nuevo Pedido
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-8">
                    
                    <!-- Filters Section -->
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6 mb-8">
                        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Filtrar por Tipo</label>
                                <select name="type" class="w-full border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5">
                                    <option value="">Todas las transacciones</option>
                                    <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Ventas Directas</option>
                                    <option value="waste" {{ request('type') == 'waste' ? 'selected' : '' }}>Novedades / Bajas</option>
                                    <option value="consumo" {{ request('type') == 'consumo' ? 'selected' : '' }}>Consumo Interno</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Desde Fecha</label>
                                <input type="date" name="from" value="{{ request('from') }}" class="w-full border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Hasta Fecha</label>
                                <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-black py-2.5 px-4 rounded-xl shadow-sm transition-all text-sm border border-indigo-100">
                                    Aplicar Filtros
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sales Table -->
                    <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-white text-gray-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                <tr>
                                    <th class="px-6 py-5 text-left font-black">Identificador</th>
                                    <th class="px-6 py-5 text-left font-black">Local / Sucursal</th>
                                    <th class="px-6 py-5 text-left font-black">Responsable</th>
                                    <th class="px-6 py-5 text-left font-black">Fecha Realización</th>
                                    <th class="px-6 py-5 text-left font-black text-right pr-12">Monto Total</th>
                                    <th class="px-6 py-5 text-center font-black">Ver</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @forelse($sales as $sale)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-9 w-9 flex items-center justify-center rounded-xl {{ $sale->type === 'sale' ? 'bg-emerald-100 text-emerald-600' : ($sale->type === 'waste' ? 'bg-orange-100 text-orange-600' : 'bg-amber-100 text-amber-600') }} group-hover:scale-110 transition-transform">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-black text-gray-900 tracking-tight">Pedido #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                                                <div class="text-[9px] uppercase font-black tracking-widest {{ $sale->type === 'sale' ? 'text-emerald-500' : ($sale->type === 'waste' ? 'text-orange-500' : 'text-amber-500') }}">
                                                    {{ $sale->type === 'sale' ? 'PROCESADA' : ($sale->type === 'waste' ? 'NOVEDAD' : 'CONSUMO') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-gray-700 font-bold uppercase tracking-tight">{{ $sale->session->cashRegister->branch->name ?? 'PRINCIPAL' }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium">Caja: {{ $sale->session->cashRegister->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-500 uppercase">{{ substr($sale->user->name, 0, 1) }}</div>
                                            <span class="text-xs text-gray-600 font-bold tracking-tight">{{ $sale->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-gray-800 font-bold">{{ $sale->created_at->translatedFormat('d M, Y') }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $sale->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right pr-12">
                                        <div class="text-sm font-black {{ $sale->type === 'sale' ? 'text-indigo-600' : 'text-gray-400' }}">
                                            $ {{ number_format($sale->total, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center gap-3">
                                            <a href="{{ route('sales.show', $sale) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Detalles">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-20 text-center">
                                        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-bold text-sm uppercase tracking-widest">No se encontraron registros de ventas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 px-2">
                        {{ $sales->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

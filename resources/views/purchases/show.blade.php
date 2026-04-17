<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalle de Orden de Compra #') }}{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-6 bg-white border-b border-gray-200 flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-indigo-700 mb-2">
                            Orden #{{ str_pad($purchase->id, 5, '0', STR_PAD_LEFT) }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-1"><strong>Fecha:</strong>
                            {{ $purchase->date->format('d/m/Y H:i:s') }}</p>
                        <p class="text-sm text-gray-600 mb-1"><strong>Proveedor:</strong>
                            {{ $purchase->supplier->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 mb-1"><strong>Sucursal de Destino:</strong> <span
                                class="font-bold text-gray-900">{{ $purchase->branch->name ?? 'N/A' }}</span></p>
                        <p class="text-sm text-gray-600">
                            <strong>Estatus Actual:</strong>
                            @if($purchase->status === 'received')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Recibida
                                    (Inventario Actualizado)</span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente
                                    de Llegada</span>
                            @endif
                        </p>
                    </div>

                    <div class="text-right">
                        <div class="text-sm text-gray-500 uppercase tracking-wide">Total a Pagar</div>
                        <div class="text-3xl font-black text-gray-900">${{ number_format($purchase->total, 2) }}</div>
                    </div>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad
                                    Solicitada</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo Unit.
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchase->details as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ $item->product->name ?? 'Producto Desconocido' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        {{ number_format($item->quantity, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        ${{ number_format($item->unit_cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold text-right">
                                        ${{ number_format($item->total_cost, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-200">
                <a href="{{ route('purchases.index') }}"
                    class="text-indigo-600 hover:text-indigo-900 font-semibold transition-colors">
                    &larr; Volver a la Lista
                </a>

                @if($purchase->status === 'pending')
                    <form action="{{ route('purchases.receive', $purchase) }}" method="POST"
                        onsubmit="return confirm('¿Confirmas que la mercancía fue entregada físicamente? Esto inyectará el stock al inventario de la sucursal asignada.');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-lg font-bold text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 shadow-md transition transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            Confirmar Recepción de Mercancía
                        </button>
                    </form>
                @elseif($purchase->status === 'received')
                    <form action="{{ route('purchases.revert', $purchase) }}" method="POST"
                        onsubmit="return confirm('ATENCIÓN: Revertir la orden restará las cantidades asociadas del inventario de la sucursal actual. ¿Deseas deshacer este ingreso?');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-100 border border-red-300 rounded-md font-semibold text-red-800 tracking-wide hover:bg-red-200 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                            Revertir Recepción (Descontar Inventario)
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
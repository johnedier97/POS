<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-rose-800 leading-tight">
            {{ __('Cerrar Turno de Caja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('error'))
                <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded shadow-md" role="alert">
                    <p class="font-bold">¡Atención!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded shadow-md" role="alert">
                    <p class="font-bold">Éxito</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                
                <form action="{{ route('sessions.update', $session) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg mb-6 text-sm text-gray-600">
                        <p><strong>Caja:</strong> {{ $session->cashRegister->name }}</p>
                        <p><strong>Operador:</strong> {{ $session->user->name }}</p>
                        <p><strong>Apertura:</strong> {{ $session->opened_at->format('d/m/Y H:i A') }}</p>
                        <p><strong>Base Inicial:</strong> ${{ number_format($session->initial_balance, 2) }}</p>
                    </div>

                    <div>
                        <label for="final_reported_balance" class="block text-sm font-semibold text-gray-700">Dinero Total Físico en Caja (Efectivo) $</label>
                        <input type="number" step="0.01" name="final_reported_balance" id="final_reported_balance" required min="0" value="0"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 text-xl font-bold text-gray-900 border-2">
                        <p class="text-xs text-gray-500 mt-2">Cuentra el dinero físico de la gaveta e ingresa el total exacto. El sistema calculará si hay descuadres respecto a las ventas del sistema.</p>
                        @error('final_reported_balance') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 text-lg uppercase tracking-wide">
                            Reportar y Cerrar Caja
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Abrir Turno de Caja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                
                <form action="{{ route('sessions.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mb-6">
                        <p class="text-indigo-800 font-medium">Estás a punto de iniciar un nuevo turno. Selecciona una caja disponible e ingresa el monto de base o apertura.</p>
                    </div>

                    <div>
                        <label for="cash_register_id" class="block text-sm font-semibold text-gray-700">Seleccionar Caja Módulo</label>
                        <select name="cash_register_id" id="cash_register_id" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Elige una caja --</option>
                            @foreach($registers as $reg)
                            <option value="{{ $reg->id }}">{{ $reg->name }} ({{ $reg->branch->name }})</option>
                            @endforeach
                        </select>
                        @error('cash_register_id') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="initial_balance" class="block text-sm font-semibold text-gray-700">Saldo Inicial en Caja (Efectivo Base) $</label>
                        <input type="number" step="0.01" name="initial_balance" id="initial_balance" required min="0" value="0"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xl font-bold text-gray-900">
                        @error('initial_balance') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 text-lg uppercase tracking-wide">
                            Iniciar Turno
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

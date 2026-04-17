<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ $unit->exists ? __('Editar Unidad de Medida') : __('Crear Unidad de Medida') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                
                <form action="{{ $unit->exists ? route('units.update', $unit) : route('units.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @if($unit->exists)
                        @method('PUT')
                    @endif

                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700">Nombre de la Unidad</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $unit->name) }}" required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                            placeholder="Ej. Kilos, Gramos, Unidades, Litros">
                        @error('name') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="abbreviation" class="block text-sm font-semibold text-gray-700">Abreviación</label>
                        <input type="text" name="abbreviation" id="abbreviation" value="{{ old('abbreviation', $unit->abbreviation) }}" required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150"
                            placeholder="Ej. KG, GR, UND, LT">
                        @error('abbreviation') <span class="text-rose-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('units.index') }}" class="text-gray-500 hover:text-gray-700 font-medium transition">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-300">
                            {{ $unit->exists ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

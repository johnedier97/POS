<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ $register->exists ? __('Editar Caja Registradora') : __('Nueva Caja Registradora') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                
                <form action="{{ $register->exists ? route('registers.update', $register) : route('registers.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @if($register->exists) @method('PUT') @endif

                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700">Identificador / Nombre de la Caja</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $register->name) }}" required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Caja 1 Principal">
                        @error('name') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="branch_id" class="block text-sm font-semibold text-gray-700">Sucursal Asignada</label>
                        <select name="branch_id" id="branch_id" required class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $register->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $register->exists ? $register->is_active : true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm font-semibold text-gray-700">Caja Activa (Disponible para turnos)</label>
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                            {{ $register->exists ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

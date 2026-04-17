<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ $branch->exists ? __('Editar Sucursal') : __('Crear Sucursal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-8">
                
                <form action="{{ $branch->exists ? route('branches.update', $branch) : route('branches.store') }}" method="POST" class="space-y-6">
                    @csrf
                    @if($branch->exists) @method('PUT') @endif

                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700">Nombre de la Sucursal</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $branch->name) }}" required
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-semibold text-gray-700">Dirección</label>
                        <textarea name="address" id="address" rows="3"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $branch->address) }}</textarea>
                        @error('address') <span class="text-rose-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end pt-4">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                            {{ $branch->exists ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

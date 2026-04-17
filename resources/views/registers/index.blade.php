<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Cajas Registradoras') }}
            </h2>
            <a href="{{ route('registers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                + Nueva Caja
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600 uppercase">
                            <th class="px-6 py-4">ID / Nombre</th>
                            <th class="px-6 py-4">Sucursal</th>
                            <th class="px-6 py-4">Estado / Sesión</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($registers as $register)
                        <tr class="hover:bg-indigo-50">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $register->name }}</p>
                                <p class="text-xs text-gray-500">#{{ $register->id }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $register->branch->name }}</td>
                            <td class="px-6 py-4">
                                @if($register->is_active)
                                    @if($register->currentSession)
                                        <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs font-bold">En Uso (Abierta)</span>
                                    @else
                                        <span class="bg-emerald-100 text-emerald-800 px-2 py-1 rounded text-xs font-bold">Activa (Disponible)</span>
                                    @endif
                                @else
                                    <span class="bg-rose-100 text-rose-800 px-2 py-1 rounded text-xs font-bold">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('registers.edit', $register) }}" class="text-indigo-600 hover:text-indigo-900 mx-2">Editar</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay cajas registradoras.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

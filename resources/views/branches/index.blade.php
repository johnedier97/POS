<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Sucursales / Puntos Venta') }}
            </h2>
            <a href="{{ route('branches.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                + Nueva Sucursal
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600 uppercase">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Nombre / Ubicación</th>
                            <th class="px-6 py-4">Cajas Registradoras</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($branches as $branch)
                        <tr class="hover:bg-indigo-50">
                            <td class="px-6 py-4 font-bold text-gray-500">#{{ $branch->id }}</td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">{{ $branch->name }}</p>
                                <p class="text-sm text-gray-500">{{ $branch->address ?? 'Sin dirección' }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-semibold">{{ $branch->cash_registers_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('branches.edit', $branch) }}" class="text-indigo-600 hover:text-indigo-900 mx-2">Editar</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay sucursales.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border border-gray-200">
                
                <form action="{{ route('users.update', $user) }}" method="POST" class="max-w-xl">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-4">
                        <x-input-label for="name" :value="__('Nombre Completo')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('Correo Electrónico')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <x-input-label for="role_id" :value="__('Rol del Sistema')" />
                        <select id="role_id" name="role_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                            <option value="">Seleccione un rol...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name === 'admin' ? 'Administrador' : ($role->name === 'sales' ? 'Vendedor' : ucfirst($role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Si se desactiva por ser auto-edición, enviamos hidden el valor -->
                        @if(auth()->id() === $user->id)
                            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                            <p class="mt-1 text-sm text-gray-500">No puedes cambiar tu propio rol.</p>
                        @endif
                        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                    </div>

                    <hr class="my-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actualizar Contraseña</h3>
                    <p class="text-sm text-gray-500 mb-4">Déjalo en blanco si no deseas cambiar la contraseña.</p>

                    <!-- Password -->
                    <div class="mb-4">
                        <x-input-label for="password" :value="__('Nueva Contraseña (Opcional)')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline mr-4">
                            Cancelar
                        </a>
                        <x-primary-button>
                            {{ __('Guardar Cambios') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>

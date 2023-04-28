<x-guest-layout>
    <h1 class="py-3 pb-0 text-3xl font-bold text-center text-blue-900 mb-1">¿Olvidaste tu contraseña?</h1>

    <div class="mb-4 text-sm text-black">
        {{ __('Ningún problema. Simplemente háganos saber su dirección de correo electrónico y le enviaremos un enlace de restablecimiento de contraseña que le permitirá elegir una nueva.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-center mt-4">
            <x-primary-button>
                {{ __('Enlace de restablecimiento de contraseña') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

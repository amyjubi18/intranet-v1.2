<title>{{ 'Iniciar Sesión' }}</title>
<x-guest-layout>
    <body>



    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="w-full mb-1 h-200 touch-none">
        <h1 class="py-3 pb-0 text-4xl font-bold text-center text-blue-900">Iniciar Sesión</h1>
    </div>
    <form method="POST" action="{{ route('login') }}">
        @csrf


        <!-- Email Address -->

        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class=" block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-900 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ml-2 text-sm text-black">{{ __('Recordarme') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-black hover:text-blue-500 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" href="{{ route('password.request') }}">
                    {{ __('¿Ólvidaste tu Contraseña?') }}
                </a>
            @endif


            <x-primary-button class="ml-3">
                {{ __('Ingresar') }}
            </x-primary-button>
        </div>
    </form>
</body>
</x-guest-layout>

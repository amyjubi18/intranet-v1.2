<section>
    <header>
        <h2 class="text-2xl font-medium text-blue-900">
            {{ __('Información del Perfil') }}
        </h2>


    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Usuario:')"/>
            <h3 class="mt-1 block w-full text-black text-xl">{{old('name', $user->name)}}</h3>
            {{-- <x-text-input id="name" name="name" type="label" class="mt-1 block w-full font-semibold " :value="old('name', $user->name)"  autocomplete="name" /> --}}
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Correo Electrónico:')" />
            <h3 class="mt-1 block w-full  text-black text-xl">{{old('name', $user->email)}}</h3>
            {{-- <x-text-input id="email" name="email" type="label" class="mt-1 block w-full font-semibold" :value="old('email', $user->email)" required autocomplete="username" /> --}}
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

           {{--  @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif --}}
        </div>
        <div>
            <x-input-label for="role" :value="__('Gerencia:')"/>
            @forelse ($user->roles as $role)
            <h3 class="mt-1 block w-full text-black text-xl">{{old('name', $role->name)}}</h3>
            @empty
            <h3 class="mt-1 block w-full text-red text-xl">No tiene Gerencia</h3>
            {{-- <x-text-input id="name" name="name" type="label" class="mt-1 block w-full font-semibold " :value="old('name', $user->name)"  autocomplete="name" /> --}}
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                @endforelse
        </div>

        {{-- <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div> --}}
    </form>
</section>

<nav x-data="{ open: false }" class="bg-blue-900 border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('inicio') }}">
                        <x-application-logo class="block w-auto text-gray-800 fill-current h-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('inicio')" :active="request()->routeIs('inicio')">

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" class="mr-1" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M12.71 2.29a1 1 0 0 0-1.42 0l-9 9a1 1 0 0 0 0 1.42A1 1 0 0 0 3 13h1v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7h1a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71zM6 20v-9.59l6-6 6 6V20z"></path></svg> {{ __('Inicio') }}
                    </x-nav-link>

<!----------------Menu desplegable GTIC--------------------------->
@can('user_index')
                        <div class="hidden space-x-8 sm:flex sm:items-center sm:ml-6">
                            <x-dropdown align="right" width="46">
                                <x-slot name="trigger">

                                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-10 text-white transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:text-white focus:outline-none ">

                                        <x-nav-link-user :href="route('users.index')" :active="request()->routeIs('users.index')" >
                                            {{ __('Usuarios') }}
                                        </x-nav-link-user>


                                        <div class="ml-1">
                                            <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">

                                    <x-dropdown-link-register  :href="route('users.create')" :active="request()->routeIs('users.create')">
                                     {{ __('Registrar') }}
                                    </x-dropdown-link-register>


                                    <x-dropdown-link-roles :href="route('gtic.roles.index')" :active="request()->routeIs('gtic.roles.index')">

                                        {{ __('Roles') }}
                                    </x-dropdown-link-roles>


                                    <x-dropdown-link-permisos :href="route('gtic.permisos.index')" :active="request()->routeIs('gtic.permisos.index')">

                                        {{ __('Permisos') }}
                                    </x-dropdown-link-permisos>


                                </x-slot>
                            </x-dropdown>
                        </div>

@endcan



<!------------------menu desplegable administracion------------------------->
@can('documentos_index')
<div class="hidden space-x-8 sm:flex sm:items-center sm:ml-6">

    <x-dropdown align="right" width="46">
        <x-slot name="trigger">

            <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-10 text-white transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:text-white focus:outline-none ">

                <x-nav-link-documento :href="route('administracion.docs.index')" :active="request()->routeIs('admnistracion.docs.index')">
                     {{ __('Documentos') }}
                </x-nav-link-documento>


                <div class="ml-1">
                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>

            </button>
        </x-slot>

        <x-slot name="content">

            <x-dropdown-link-cargar-documento  :href="route('administracion.docs.create')" :active="request()->routeIs('administracion.docs.create')">
             {{ __('Cargar Documento') }}
            </x-dropdown-link-cargar-documento>

            <x-dropdown-link-categorias :href="route('administracion.categorias.index')" :active="request()->routeIs('administracion.categorias.index')">

                {{ __('Categorías') }}
            </x-dropdown-link-categorias>



        </x-slot>
    </x-dropdown>
</div>
    @endcan



<!--------->

<!------------------menu desplegable Bienes------------------------->
@can('bienes_index')
<div class="hidden space-x-8 sm:flex sm:items-center sm:ml-6">
    <x-dropdown align="right" width="46">
        <x-slot name="trigger">
            {{-- @can('documentos_index') --}}
            <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-10 text-white transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:text-white focus:outline-none ">
                {{-- @can('documentos_index') --}}
                <x-nav-link-bienes :href="route('administracion.bienes_publicos.index')" :active="request()->routeIs('administracion.bienes_publicos.index')">
                     {{ __('Bienes') }}
                </x-nav-link-bienes>
                  {{-- @endcan
            @endcan --}}

                <div class="ml-1">
                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>

        <x-slot name="content">

            <x-dropdown-link-formulario  :href="route('administracion.bienes_publicos.create')" :active="request()->routeIs('administracion.bienes_publicos.create')">
             {{ __('Formulario') }}
            </x-dropdown-link-formulario>
            <x-dropdown-link-unidad-admin  :href="route('administracion.unidad_administrativa.index')" :active="request()->routeIs('administracion.unidad_administrativa.index')">
                {{ __('Unidad Administrativa') }}
               </x-dropdown-link-unidad-admin>
               <x-dropdown-link-forma-adqui  :href="route('administracion.forma_adquisicion.index')" :active="request()->routeIs('administracion.forma_adquisicion.index')">
                {{ __('Forma de Adquisición') }}
               </x-dropdown-link-forma-adqui>

               <x-dropdown-link-estado-uso :href="route('administracion.estado_del_uso.index')" :active="request()->routeIs('administracion.estado_del_uso.index')">

                {{ __('Estado del Uso') }}
            </x-dropdown-link-estado-uso>

               <x-dropdown-link-categoria-ge :href="route('administracion.categoria_general.index')" :active="request()->routeIs('administracion.categoria-general.index')">
                {{ __('Categoría General') }}
               </x-dropdown-link-categoria-ge>

            <x-dropdown-link-subcategoria :href="route('administracion.sub_categoria.index')" :active="request()->routeIs('administracion.sub_categoria.index')">

                {{ __('Subcategoría') }}
            </x-dropdown-link-subcategoria>

            <x-dropdown-link-categoria-es :href="route('administracion.categoria_especifica.index')" :active="request()->routeIs('administracion.categoria_especifica.index')">
                {{ __('Categoría Específica') }}
               </x-dropdown-link-categoria-es>
        </x-slot>
    </x-dropdown>
</div>

@endcan

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md text-gray-50 hover:text-white focus:outline-none">

                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;margin-right:4px;"><path d="M12 2A10.13 10.13 0 0 0 2 12a10 10 0 0 0 4 7.92V20h.1a9.7 9.7 0 0 0 11.8 0h.1v-.08A10 10 0 0 0 22 12 10.13 10.13 0 0 0 12 2zM8.07 18.93A3 3 0 0 1 11 16.57h2a3 3 0 0 1 2.93 2.36 7.75 7.75 0 0 1-7.86 0zm9.54-1.29A5 5 0 0 0 13 14.57h-2a5 5 0 0 0-4.61 3.07A8 8 0 0 1 4 12a8.1 8.1 0 0 1 8-8 8.1 8.1 0 0 1 8 8 8 8 0 0 1-2.39 5.64z"></path><path d="M12 6a3.91 3.91 0 0 0-4 4 3.91 3.91 0 0 0 4 4 3.91 3.91 0 0 0 4-4 3.91 3.91 0 0 0-4-4zm0 6a1.91 1.91 0 0 1-2-2 1.91 1.91 0 0 1 2-2 1.91 1.91 0 0 1 2 2 1.91 1.91 0 0 1-2 2z"></path></svg><div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link-edit-profile :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link-edit-profile>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link-logout :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link-logout>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="flex items-center -mr-2 sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-white transition duration-150 ease-in-out rounded-md hover:text-white hover:bg-blue-500 focus:outline-none focus:bg-blue-900 focus:text-white">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1 ">
            <x-responsive-nav-link :href="route('inicio')" :active="request()->routeIs('inicio')">
                {{ __('Inicio') }}
            </x-responsive-nav-link>
            @can('documentos_create')
            <x-responsive-nav-link :href="route('administracion.docs.create')" :active="request()->routeIs('administracion.docs.create')">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m21.706 5.292-2.999-2.999A.996.996 0 0 0 18 2H6a.996.996 0 0 0-.707.293L2.294 5.292A.994.994 0 0 0 2 6v13c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2V6a.994.994 0 0 0-.294-.708zM6.414 4h11.172l1 1H5.414l1-1zM4 19V7h16l.002 12H4z"></path><path d="M14 9h-4v3H7l5 5 5-5h-3z"></path></svg>  {{ __('Cargar ') }}
            </x-responsive-nav-link>
            @endcan
            @can('documentos_index')
            <x-responsive-nav-link :href="route('administracion.docs.index')" :active="request()->routeIs('admnistracion.docs.index')">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19.903 8.586a.997.997 0 0 0-.196-.293l-6-6a.997.997 0 0 0-.293-.196c-.03-.014-.062-.022-.094-.033a.991.991 0 0 0-.259-.051C13.04 2.011 13.021 2 13 2H6c-1.103 0-2 .897-2 2v16c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2V9c0-.021-.011-.04-.013-.062a.952.952 0 0 0-.051-.259c-.01-.032-.019-.063-.033-.093zM16.586 8H14V5.414L16.586 8zM6 20V4h6v5a1 1 0 0 0 1 1h5l.002 10H6z"></path><path d="M8 12h8v2H8zm0 4h8v2H8zm0-8h2v2H8z"></path></svg>{{ __('Documentos') }}
            </x-responsive-nav-link>
            @endcan

                 @can('user_create')
             <x-responsive-nav-link :href="route('users.create')" :active="request()->routeIs('users.create')">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 8h-2v3h-3v2h3v3h2v-3h3v-2h-3zM4 8a3.91 3.91 0 0 0 4 4 3.91 3.91 0 0 0 4-4 3.91 3.91 0 0 0-4-4 3.91 3.91 0 0 0-4 4zm6 0a1.91 1.91 0 0 1-2 2 1.91 1.91 0 0 1-2-2 1.91 1.91 0 0 1 2-2 1.91 1.91 0 0 1 2 2zM4 18a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v1h2v-1a5 5 0 0 0-5-5H7a5 5 0 0 0-5 5v1h2z"></path></svg> {{ __('Registrar') }}
             </x-responsive-nav-link>
                @endcan
                @can('categorias_index')
                <x-responsive-nav-link :href="route('administracion.categorias.create')" :active="request()->routeIs('administracion.categorias.create')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M20 2H8c-1.103 0-2 .897-2 2v12c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2V4c0-1.103-.897-2-2-2zM8 16V4h12l.002 12H8z"></path><path d="M4 8H2v12c0 1.103.897 2 2 2h12v-2H4V8zm11-2h-2v3h-3v2h3v3h2v-3h3V9h-3z"></path></svg> {{ __('Categoria') }}
                </x-responsive-nav-link>
                @endcan
                @can('user_index')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1z"></path></svg> {{ __('Usuarios') }}
                </x-responsive-nav-link>
                @endcan
                @can('role_index')
                <x-responsive-nav-link  :href="route('gtic.roles.index')" :active="request()->routeIs('gtic.roles.index')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M9.5 12c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4zm1.5 1H8c-3.309 0-6 2.691-6 6v1h15v-1c0-3.309-2.691-6-6-6z"></path><path d="M16.604 11.048a5.67 5.67 0 0 0 .751-3.44c-.179-1.784-1.175-3.361-2.803-4.44l-1.105 1.666c1.119.742 1.8 1.799 1.918 2.974a3.693 3.693 0 0 1-1.072 2.986l-1.192 1.192 1.618.475C18.951 13.701 19 17.957 19 18h2c0-1.789-.956-5.285-4.396-6.952z"></path></svg>{{ __('Roles') }}
                </x-responsive-nav-link>
                @endcan
                @can('permission_index
                ')
                <x-responsive-nav-link :href="route('gtic.permisos.index')" :active="request()->routeIs('gtic.permisos.index')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M20 2H8c-1.103 0-2 .897-2 2v12c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2V4c0-1.103-.897-2-2-2zM8 16V4h12l.002 12H8z"></path><path d="M4 8H2v12c0 1.103.897 2 2 2h12v-2H4V8zm8.933 3.519-1.726-1.726-1.414 1.414 3.274 3.274 5.702-6.84-1.538-1.282z"></path></svg>{{ __('Permisos') }}
                </x-responsive-nav-link>
                @endcan


        </div>






















        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="flex text-base font-medium text-white"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;margin-right:4px;"><path d="M12 2A10.13 10.13 0 0 0 2 12a10 10 0 0 0 4 7.92V20h.1a9.7 9.7 0 0 0 11.8 0h.1v-.08A10 10 0 0 0 22 12 10.13 10.13 0 0 0 12 2zM8.07 18.93A3 3 0 0 1 11 16.57h2a3 3 0 0 1 2.93 2.36 7.75 7.75 0 0 1-7.86 0zm9.54-1.29A5 5 0 0 0 13 14.57h-2a5 5 0 0 0-4.61 3.07A8 8 0 0 1 4 12a8.1 8.1 0 0 1 8-8 8.1 8.1 0 0 1 8 8 8 8 0 0 1-2.39 5.64z"></path><path d="M12 6a3.91 3.91 0 0 0-4 4 3.91 3.91 0 0 0 4 4 3.91 3.91 0 0 0 4-4 3.91 3.91 0 0 0-4-4zm0 6a1.91 1.91 0 0 1-2-2 1.91 1.91 0 0 1 2-2 1.91 1.91 0 0 1 2 2 1.91 1.91 0 0 1-2 2z"></path></svg>{{ Auth::user()->name }}</div>

            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg>{{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:; margin-right:4px;"><path d="M16 13v-2H7V8l-5 4 5 4v-3z"></path><path d="M20 3h-9c-1.103 0-2 .897-2 2v4h2V5h9v14h-9v-4H9v4c0 1.103.897 2 2 2h9c1.103 0 2-.897 2-2V5c0-1.103-.897-2-2-2z"></path></svg> {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<section>

<div class="header-content">
      <div class="header-title">
       <div style="background: linear-gradient(135deg, #1e3a8a 0%, #1e3a8a 100%);" " class="header-icon">
        <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /> <circle cx="12" cy="7" r="4" />
        </svg>
       </div>
       <div class="header-text">
        <h1>Mi Perfil</h1>
        <p>Gestiona tu información personal y configuración de cuenta</p>
       </div>
      </div>
      <div class="status-badge"><span class="status-dot"></span> Cuenta Activa
      </div>
     </div>
    <!-- Formulario de Información Personal -->
    <form id="profileForm" method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5" enctype="multipart/form-data" onsubmit="handleProfileUpdate(event)">
        @csrf
        @method('patch')

        <!-- Foto de Perfil -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ __('Foto de Perfil') }}
                </h3>
            </div>

            <div class="p-5">
                <div class="flex items-center gap-5">
                    <!-- Foto Actual -->
                    <div class="relative">
                        <div class="w-28 h-28 rounded-xl overflow-hidden ring-4 ring-blue-100 bg-gradient-to-br from-blue-500 to-blue-100 flex items-center justify-center">
                            @if($user->profile_photo)
                                <img src="{{ asset('archivos/avatars/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover profile-photo">
                            @else
                                <span class="text-3xl font-bold text-white">{{ substr($user->name, 0, 2) }}</span>
                            @endif
                        </div>

                        <!-- Botón de cambio de foto -->
                        <label for="profile_photo" class="absolute -bottom-2 -right-2 w-9 h-9 bg-blue-900 hover:bg-blue-700 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg transition-all duration-200 hover:scale-105">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="hidden" onchange="previewImage(event)">
                    </div>

                    <!-- Info de la foto -->
                    <div class="flex-1">
                        <p class="text-sm text-gray-600 mb-1">{{ __('Allowed: PNG, JPG, GIF. Max: 2MB') }}</p>
                        <p class="text-sm text-gray-500">{{ __('Click the camera icon to change your photo') }}</p>
                        @error('profile_photo')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Preview de la nueva foto -->
                <div id="photoPreview" class="hidden mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm font-medium text-blue-700 mb-2">{{ __('Nueva foto seleccionada:') }}</p>
                    <img id="previewImg" src="" alt="Preview" class="w-20 h-20 rounded-lg object-cover ring-2 ring-blue-200">
                </div>
            </div>
        </div>

        <!-- Información Personal -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6 ">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ __('Información Personal') }}
                </h3>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre -->
                <div class="block">
                    <div class="flex items-center gap-2 mb-1">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nombre') }} <span class="text-red-500">*</span></label>
                    </div>
                    <div class="relative flex">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                        </div>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                            class="pl-9 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                            autocomplete="name" placeholder="{{ __('Ingresa tu nombre') }}">
                    </div>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido -->
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Apellido') }}</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                        </div>
                        <input type="text" id="lastname" name="lastname" value="{{ old('lastname', $user->lastname) }}"
                            class="pl-9 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                            autocomplete="family-name" placeholder="{{ __('Ingresa tu apellido') }}">
                    </div>
                    @error('lastname')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cédula de Identidad -->
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                            </svg>
                    <label for="cedula" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Cédula de Identidad') }}</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                        </div>
                        <input type="text" id="cedula" name="cedula" value="{{ old('cedula', $user->cedula) }}"
                            class="pl-9 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="V-12345678">
                    </div>
                    @error('cedula')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de Nacimiento -->
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                    <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Fecha de Nacimiento') }}</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                        </div>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                            value="{{ old('fecha_nacimiento', $user->fecha_nacimiento ? $user->fecha_nacimiento->format('Y-m-d') : '') }}"
                            class="pl-9 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @error('fecha_nacimiento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Departamento -->
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                    <label for="departamento" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Departamento') }}</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                        </div>
                        <input type="text" id="departamento" name="departamento" value="{{ old('departamento', $user->departamento) }}"
                            class="pl-9 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" disabled
                            placeholder="{{ __('Ingresa tu departamento') }}">
                    </div>
                    @error('departamento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Correo Electrónico -->
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Correo Electronico') }}</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                            class="pl-9 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="{{ __('Ingresa tu email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>


        <!-- Botones de Acción -->
        <div class="flex justify-end items-center gap-3 pt-2 pb-2 pl-3 mt-2 w-fit">
            <button type="submit" class="btn-save w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Guardar Cambios') }}
            </button>

            @if (session('status') === 'profile-updated')
                <div class="flex items-center gap-2 text-sm text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('¡Guardado exitosamente!') }}
                </div>
            @endif
        </div>
        </div>
    </form>

    <!-- Rol y Permisos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style=" margin-top: 20px;">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                {{ __('Rol') }}
            </h3>
        </div>

        <div class="p-5">
            <div class="flex items-center justify-start gap-4 flex-wrap">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ __('Tu Rol Actual') }}</p>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-900 text-white rounded-lg font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        @forelse($user->roles as $role)
                            {{ $role->name }}
                        @empty
                            {{ __('Sin rol asignado') }}
                        @endforelse
                    </div>

                <button type="button" class="view-permissions-btn" id="togglePermissions" onclick="togglePermissions()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                    {{ __('Ver Permisos') }}
                </button>
                </div>
            </div>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>

    <!-- Modal de Permisos -->
    <div id="permissionsModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ __('Permisos Asignados') }}
                </h2>
                <button type="button" class="modal-close" onclick="closePermissionsModal()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="permissions-grid grid grid-cols-1 gap-3">
                    @forelse($user->getAllPermissions() as $permission)
                        <div class="permission-item flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">{{ $permission->name }}</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">{{ __('No hay permisos asignados.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Preview de la imagen de perfil
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');
    const avatarImg = document.querySelector('.profile-photo');

    if (file) {
        if (!file.type.match('image.*')) {
            Swal.fire({
                title: 'Archivo no válido',
                text: 'Por favor selecciona un archivo de imagen válido (PNG, JPG, GIF)',
                icon: 'warning',
                confirmButtonColor: '#14368b',
                confirmButtonText: 'Entendido'
            });
            event.target.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                title: 'Archivo muy grande',
                text: 'El archivo no debe superar los 2MB',
                icon: 'warning',
                confirmButtonColor: '#14368b',
                confirmButtonText: 'Entendido'
            });
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');

            if (avatarImg) {
                avatarImg.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}

// Modal de permisos
function togglePermissions() {
    const modal = document.getElementById('permissionsModal');
    modal.classList.add('show');
}

function closePermissionsModal() {
    const modal = document.getElementById('permissionsModal');
    modal.classList.remove('show');
}

// Cerrar modal al hacer clic fuera del contenido
document.addEventListener('click', function(event) {
    const modal = document.getElementById('permissionsModal');
    if (event.target === modal) {
        closePermissionsModal();
    }
});

// Cerrar modal con la tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePermissionsModal();
    }
});

// Confirmar actualización de perfil con SweetAlert2
function handleProfileUpdate(event) {
    event.preventDefault();

    const form = event.target;

    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas guardar los cambios realizados en tu perfil?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#14368b',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, guardar cambios',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>

<style>
.view-permissions-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: white;
    color: #053e7e;
    border: 2px solid #051b7e;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
.view-permissions-btn:hover {
    background: #053e7e;
    color: white;
}
</style>


@section('title', 'Editar Usuario')
@section('subtitle', 'Modificar cuenta de usuario')
<x-app-layout>

    <x-slot name="scripts">
        <script src="https://cdn.tailwindcss.com"></script>
    </x-slot>

    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1e3a8a;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 0.75rem;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .photo-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed rgba(0,0,0,0.1);
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: rgba(255,255,255,0.9);
            position: relative;
            overflow: hidden;
        }
        .btn-save {
      flex: 1;
      padding: 1.125rem 2rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.625rem;
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26, 91, 175, 0.4);
    }

    .btn-save:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .btn-save svg {
      width: 20px;
      height: 20px;
    }
        .photo-preview:hover {
            border-color: #1e3a8a;
        }

        .photo-preview.has-image {
            border-style: solid;
            border-color: #22C55E;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0.75rem;
        }

        .photo-placeholder {
            text-align: center;
            color: #64748b;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            border-radius: 0.75rem;
        }

        .photo-preview:hover .photo-overlay {
            opacity: 1;
        }

        .status-switch {
            cursor: pointer;
        }

        .switch-track {
            width: 60px;
            height: 30px;
            background: #dc2626;
            border-radius: 15px;
            position: relative;
            transition: all 0.3s ease;
            border: 2px solid rgba(220, 38, 38, 0.2);
        }

        .switch-track.active {
            background: #22C55E;
            border-color: rgba(34, 197, 94, 0.2);
        }

        .switch-thumb {
            width: 22px;
            height: 22px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .switch-thumb.active {
            transform: translateX(30px);
        }

        .status-label {
            font-weight: 600;
            transition: color 0.3s ease;
            color: #dc2626;
        }

        .status-label.active {
            color: #22C55E;
        }
    </style>

    <!-- Formulario de edición -->
    <div class="px-6 pb-8">
        <div class="max-w-4xl mx-auto">
            <form id="editForm" method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data" class="glass rounded-2xl p-8 fade-in" onsubmit="handleFormSubmit(event)">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Columna izquierda -->
                    <div class="space-y-6">

                        <!-- Información personal -->
                        <div>
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Información Personal
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Nombre -->
                                <div class="form-group">
                                    <label class="form-label" for="name">Nombre *</label>
                                    <input type="text" id="name" name="name" class="form-input" placeholder="Ingrese el nombre" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Apellido -->
                                <div class="form-group">
                                    <label class="form-label" for="lastname">Apellido *</label>
                                    <input type="text" id="lastname" name="lastname" class="form-input" placeholder="Ingrese el apellido" value="{{ old('lastname', $user->lastname) }}" required>
                                    @error('lastname')
                                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Cédula -->
                                <div class="form-group">
                                    <label class="form-label" for="cedula">Cédula *</label>
                                    <input type="text" id="cedula" name="cedula" class="form-input" placeholder="12.345.678" value="{{ old('cedula', $user->cedula) }}" required>
                                    @error('cedula')
                                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fecha de nacimiento -->
                                <div class="form-group">
                                    <label class="form-label" for="fecha_nacimiento">Fech. de Nacimiento</label>
                                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-input" value="{{ old('fecha_nacimiento', isset($user->fecha_nacimiento) ? (is_string($user->fecha_nacimiento) ? $user->fecha_nacimiento : $user->fecha_nacimiento->format('Y-m-d')) : '') }}">
                                    @error('fecha_nacimiento')
                                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Información de cuenta -->
                        <div>
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                Información de Cuenta
                            </h3>

                            <!-- Usuario -->
                            <div class="form-group">
                                <label class="form-label" for="username">Usuario *</label>
                                <input type="text" id="username" name="username" class="form-input" placeholder="Nombre de usuario" value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Correo -->
                            <div class="form-group">
                                <label class="form-label" for="email">Correo Electrónico *</label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="usuario@codecyt.com" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contraseña (opcional en edición) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label" for="password">Nueva Contraseña</label>
                                    <div class="relative">
                                        <input type="password" id="password" name="password" class="form-input pr-10" placeholder="Dejar en blanco para mantener">
                                        <button type="button" onclick="togglePasswordVisibility('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none" title="Ver / Ocultar contraseña">
                                            <svg id="eye-icon-password" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="password_confirmation">Repetir Nueva Contraseña</label>
                                    <div class="relative">
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input pr-10" placeholder="Repita la nueva contraseña">
                                        <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none" title="Ver / Ocultar contraseña">
                                            <svg id="eye-icon-password_confirmation" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-6">

                        <!-- Foto de perfil -->
                        <div>
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                                Foto de Perfil
                            </h3>

                            <div class="flex items-start gap-4">
                                <div class="photo-preview {{ $user->profile_photo ? 'has-image' : '' }}" onclick="document.getElementById('profile_photo').click()">
                                    @if($user->profile_photo)
                                        <img id="photoPreview" src="{{ asset('archivos/avatars/' . $user->profile_photo) }}" alt="Foto de perfil">
                                    @else
                                        <div class="photo-placeholder" id="photoPlaceholder">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto mb-2">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="9" cy="9" r="2"/>
                                                <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                            </svg>
                                            <p class="text-xs">Seleccionar foto</p>
                                        </div>
                                        <img id="photoPreview" style="display: none;" alt="Vista previa">
                                    @endif
                                    <div class="photo-overlay">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-700 mb-2" onclick="document.getElementById('profile_photo').click()">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7,10 12,15 17,10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                        Cambiar Foto
                                    </button>
                                    <p class="text-xs text-slate-400">Formatos: JPG, PNG, GIF<br>Tamaño máximo: 2MB</p>
                                </div>
                            </div>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;" onchange="previewPhoto(event)">
                            @error('profile_photo')
                                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Asignación organizacional -->
                        <div>
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                </svg>
                                Asignación Organizacional
                            </h3>

                            <!-- Rol -->
                            <div class="form-group">
                                <label class="form-label" for="roles">Rol *</label>
                                <select id="roles" name="roles" class="form-input" required>
                                    <option value="">Seleccione el rol</option>
                                    @foreach ($roles as $id => $role)
                                    <option value="{{ $id }}" {{ old('roles', $user->roles->first()?->id) == $id ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                </select>
                                @error('roles')
                                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Departamento -->
                            <div class="form-group">
                                <label class="form-label" for="departamento">Departamento *</label>
                                <select id="departamento" name="departamento" class="form-input" required>
                                    <option value="">Seleccione el departamento</option>
                                    @php
                                        $currentDepto = old('departamento', $user->departamento);
                                    @endphp
                                    <option value="GTIC" {{ $currentDepto == 'GTIC' ? 'selected' : '' }}>GTIC</option>
                                    <option value="OGH" {{ $currentDepto == 'OGH' ? 'selected' : '' }}>OGH</option>
                                    <option value="OAC" {{ $currentDepto == 'OAC' ? 'selected' : '' }}>OAC</option>
                                    <option value="Consultoria Juridica" {{ $currentDepto == 'Consultoria Juridica' ? 'selected' : '' }}>Consultoria Juridica</option>
                                    <option value="GEDP" {{ $currentDepto == 'GEDP' ? 'selected' : '' }}>GEDP</option>
                                    <option value="OPP" {{ $currentDepto == 'OPP' ? 'selected' : '' }}>Operaciones</option>
                                    <option value="OGA" {{ $currentDepto == 'OGA' ? 'selected' : '' }}>OGA</option>
                                    <option value="Presidencia" {{ $currentDepto == 'Presidencia' ? 'selected' : '' }}>Presidencia</option>
                                    <option value="Auditoría Interna" {{ $currentDepto == 'Auditoría Interna' ? 'selected' : '' }}>Auditoría Interna</option>
                                </select>
                                @error('departamento')
                                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado del usuario -->
                            <div class="form-group">
                                <label class="form-label">Estado del Usuario</label>
                                <div class="flex items-center gap-3">
                                    <div class="status-switch" onclick="toggleStatus()">
                                        <div class="switch-track {{ $user->status ? 'active' : '' }}" id="switchTrack">
                                            <div class="switch-thumb {{ $user->status ? 'active' : '' }}" id="switchThumb"></div>
                                        </div>
                                    </div>
                                    <span class="status-label {{ $user->status ? 'active' : '' }}" id="statusLabel">{{ $user->status ? 'Activo' : 'Inactivo' }}</span>
                                </div>
                                <input type="hidden" id="status" name="status" value="{{ old('status', $user->status) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                    <x-primary-link href="{{ route('users.index') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                            <path d="M19 12H5"/>
                            <path d="M12 19l-7-7 7-7"/>
                        </svg>
                        Regresar
                    </x-primary-link>
                    <x-primary-button class="btn-save" type="submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Actualizar Registro
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var userStatus = {{ $user->status ? 'true' : 'false' }};

        // Funciones del switch de estado
        function toggleStatus() {
            userStatus = !userStatus;
            var track = document.getElementById('switchTrack');
            var thumb = document.getElementById('switchThumb');
            var label = document.getElementById('statusLabel');
            var input = document.getElementById('status');

            if (userStatus) {
                track.classList.add('active');
                thumb.classList.add('active');
                label.classList.add('active');
                label.textContent = 'Activo';
                input.value = '1';
            } else {
                track.classList.remove('active');
                thumb.classList.remove('active');
                label.classList.remove('active');
                label.textContent = 'Inactivo';
                input.value = '0';
            }
        }

        // Formatear cédula automáticamente
        document.getElementById('cedula').addEventListener('input', function(e) {
            var value = e.target.value.replace(/[^\d]/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '.' + value.substring(2);
            }
            if (value.length >= 6) {
                value = value.substring(0, 6) + '.' + value.substring(6);
            }
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });

        // Toggle visibilidad de contraseña
        function togglePasswordVisibility(inputId) {
            var input = document.getElementById(inputId);
            var eyeIcon = document.getElementById('eye-icon-' + inputId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeIcon) {
                    eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
                }
            } else {
                input.type = 'password';
                if (eyeIcon) {
                    eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
                }
            }
        }

        // Previsualización de foto
        function previewPhoto(event) {
            var file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 2MB.');
                    event.target.value = '';
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert('Por favor seleccione una imagen válida.');
                    event.target.value = '';
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('photoPreview');
                    var placeholder = document.getElementById('photoPlaceholder');
                    var container = document.querySelector('.photo-preview');

                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'photoPreview';
                        preview.style.display = 'block';
                        container.appendChild(preview);
                    }

                    preview.src = e.target.result;
                    preview.style.display = 'block';

                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    container.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</x-app-layout>


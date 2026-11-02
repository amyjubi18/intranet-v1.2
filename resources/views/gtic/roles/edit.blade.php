@section('title', 'Editar Rol')
@section('subtitle', 'Modificar rol de usuario')
<x-app-layout>

    <x-slot name="scripts">
        <script src="https://cdn.tailwindcss.com"></script>
    </x-slot>
    <style>
        body {
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 25%, #bfdbfe 50%, #93c5fd 75%, #60a5fa 100%);
            overflow-x: hidden;
        }

        .main-wrapper {
            min-height: 100%;
            width: 100%;
            padding: 2rem;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(59, 130, 246, 0.2);
            backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.1);
        }

        .title-section h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
            text-decoration: none;
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
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
        .btn-primary:hover {
            background: #1e40af;
            border-color: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
        }

        .btn-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: #475569;
            border-color: rgba(100, 116, 139, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(100, 116, 139, 0.2);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            border-color: rgba(220, 38, 38, 0.2);
        }

        .btn-danger:hover {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.75rem;
            font-size: 0.9rem;
            background: white;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .permissions-input-container {
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.75rem;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.02);
        }

        .permission-input-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .btn-icon {
            padding: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .added-permissions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(59, 130, 246, 0.1);
        }

        .permission-tag {
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .permission-tag button {
            background: none;
            border: none;
            color: #1e40af;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .permission-tag button:hover {
            color: #dc2626;
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: white;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 2000;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .toast.success {
            border-color: rgba(34, 197, 94, 0.3);
            background: rgba(34, 197, 94, 0.05);
        }

        .toast.error {
            border-color: rgba(220, 38, 38, 0.3);
            background: rgba(220, 38, 38, 0.05);
        }

        .toast-icon {
            font-size: 1.25rem;
        }

        .toast-message {
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e3a8a;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                padding: 1rem;
            }

            .title-section h1 {
                font-size: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="main-wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="title-section">
                    <h1>
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        Editar Rol
                    </h1>
                </div>
            </div>

            <!-- Formulario Flotante -->
            <div style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
                <div style="background: rgba(255, 255, 255, 0.98); border: 3px solid rgba(59, 130, 246, 0.3); backdrop-filter: blur(16px); border-radius: 2rem; padding: 3rem; max-width: 700px; width: 100%; box-shadow: 0 25px 80px rgba(30, 58, 138, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;">
                    <div style="text-align: center; margin-bottom: 2.5rem;">
                        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); width: 80px; height: 80px; border-radius: 1.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                        </div>
                        <h2 style="font-size: 1.75rem; font-weight: 800; color: #1e3a8a; margin-bottom: 0.5rem;">Editar Rol</h2>
                        <p style="color: #64748b; font-size: 0.95rem;">Modifica el nombre y los permisos del rol</p>
                    </div>

                    <form id="roleForm" method="POST" action="{{ route('gtic.roles.update', $role->id) }}" onsubmit="saveRole(event)">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nombre del Rol *</label>
                            <input type="text" id="name" name="name" class="form-input"
                                   placeholder="Ej: Gerente_OGH" value="{{ old('name', $role->name) }}"
                                   style="font-size: 1rem; padding: 1rem;" required>
                            @error('name')
                                <div class="text-red-500 text-xs mt-1" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Permisos del Rol *</span>
                                <button type="button" class="btn btn-secondary" onclick="applyAllPermissions()" style="padding: 0.5rem 1rem; font-size: 0.8rem;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        <path d="M9 12l2 2 4-4" />
                                    </svg>
                                    Administrador
                                </button>
                            </label>
                            <div class="permissions-input-container" style="padding: 1.5rem;">
                                <div class="permission-input-row">
                                    <select id="permissionSelect" class="form-input" style="font-size: 0.95rem;">
                                        <option value="">Seleccionar permiso...</option>
                                        @foreach ($permissions as $id => $permission)
                                            <option value="{{ $id }}">{{ $permission }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-primary btn-icon" onclick="addPermission()" style="padding: 1rem;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                    </button>
                                </div>
                                <div id="addedPermissions" class="added-permissions" style="min-height: 100px; max-height: 250px; overflow-y: auto;">
                                    @if(old('permissions'))
                                        @foreach(old('permissions') as $permId)
                                            @php
                                                $permName = $permissions[$permId] ?? '';
                                            @endphp
                                            @if($permName)
                                                <div class="permission-tag" data-perm-id="{{ $permId }}">
                                                    {{ $permName }}
                                                    <input type="hidden" name="permissions[]" value="{{ $permId }}">
                                                    <button type="button" onclick="removePermission(this)">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <line x1="18" y1="6" x2="6" y2="18"/>
                                                            <line x1="6" y1="6" x2="18" y2="18"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        @foreach($role->permissions as $permission)
                                            <div class="permission-tag" data-perm-id="{{ $permission->id }}">
                                                {{ $permission->name }}
                                                <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                                                <button type="button" onclick="removePermission(this)">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                        @if($role->permissions->count() === 0)
                                            <p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 2rem 0;">No hay permisos agregados aún</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @error('permissions')
                                <div class="text-red-500 text-xs mt-1" style="color: #dc2626; font-size: 0.8rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="justify-end" style="display: flex; gap: 1rem; margin-top: 2.5rem; flex-wrap: wrap;">
                            <x-primary-button class="btn-save" style="width: fit-content;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17,21 17,13 7,13 7,21" />
                                    <polyline points="7,3 7,8 15,8" />
                                </svg>
                                Actualizar Rol
                            </x-primary-button>
                            <x-primary-link href="{{ route('gtic.roles.index') }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                            <path d="M19 12H5"/>
                            <path d="M12 19l-7-7 7-7"/>
                                </svg>
                                Regresar
                            </x-primary-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <div class="toast-icon" id="toastIcon">✅</div>
        <div class="toast-message" id="toastMessage">Mensaje</div>
    </div>

    <script>
        // Variables globales
        let rolePermissions = @json(old('permissions', $role->permissions->pluck('id')->toArray()));

        // Lista de permisos disponibles (para el botón Administrador)
        const allPermissionIds = @json(array_keys($permissions->toArray()));

        // Objeto de todos los permisos para usar en JS
        const allPermissions = @json($permissions);

        console.log('Permissions loaded:', allPermissions);
        console.log('Role permissions:', rolePermissions);

        // Data handler
        const dataHandler = {
            onDataChanged(data) {
                console.log('Data changed:', data);
            }
        };

        // Inicializar SDK si está disponible
        async function initApp() {
            if (typeof window.dataSdk !== 'undefined') {
                const result = await window.dataSdk.init(dataHandler);
                if (!result.isOk) {
                    console.warn('SDK initialization warning:', result.error);
                }
            }
        }

        // Actualizar select de permisos - solo ocultar los ya agregados
        function updatePermissionSelect() {
            const select = document.getElementById('permissionSelect');

            // Restaurar todas las opciones primero
            const allOptions = select.querySelectorAll('option');
            allOptions.forEach(option => {
                option.style.display = '';
            });

            // Ocultar los permisos ya agregados (excepto la opción por defecto)
            rolePermissions.forEach(permId => {
                const option = select.querySelector(`option[value="${permId}"]`);
                if (option) {
                    option.style.display = 'none';
                }
            });
        }

        // Añadir permiso
        function addPermission() {
            const select = document.getElementById('permissionSelect');
            const permission = select.value;

            if (!permission) {
                showToast('Seleccione un permiso válido', 'error');
                return;
            }

            if (rolePermissions.includes(permission)) {
                showToast('Este permiso ya fue agregado', 'error');
                return;
            }

            rolePermissions.push(permission);
            updatePermissionSelect();
            renderPermissions();
            showToast('Permiso agregado correctamente', 'success');
        }

        // Aplicar todos los permisos (Administrador)
        function applyAllPermissions() {
            rolePermissions = [...allPermissionIds];
            updatePermissionSelect();
            renderPermissions();
            showToast('Todos los permisos aplicados', 'success');
        }

        // Renderizar permisos
        function renderPermissions() {
            const container = document.getElementById('addedPermissions');

            if (rolePermissions.length === 0) {
                container.innerHTML = '<p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 2rem 0;">No hay permisos agregados aún</p>';
                return;
            }

            container.innerHTML = '';

            rolePermissions.forEach((permId) => {
                if (allPermissions[permId]) {
                    const tag = document.createElement('div');
                    tag.className = 'permission-tag';
                    tag.setAttribute('data-perm-id', permId);
                    tag.innerHTML = `
                        ${allPermissions[permId]}
                        <input type="hidden" name="permissions[]" value="${permId}">
                        <button type="button" onclick="removePermission(this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    `;
                    container.appendChild(tag);
                }
            });
        }

        // Eliminar permiso
        function removePermission(button) {
            const tag = button.closest('.permission-tag');
            const permId = tag.getAttribute('data-perm-id');

            rolePermissions = rolePermissions.filter(p => p != permId);
            tag.remove();

            if (rolePermissions.length === 0) {
                document.getElementById('addedPermissions').innerHTML = '<p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 2rem 0;">No hay permisos agregados aún</p>';
            }

            updatePermissionSelect();
        }

        // Guardar rol - validación antes del envío estándar
        function saveRole(event) {
            if (rolePermissions.length === 0) {
                event.preventDefault();
                showToast('Agregue al menos un permiso', 'error');
                return false;
            }

            // El formulario se envía normalmente al servidor
            return true;
        }

        // Mostrar toast
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const messageEl = document.getElementById('toastMessage');

            const icons = {
                success: '✅',
                error: '❌'
            };

            icon.textContent = icons[type] || icons.success;
            messageEl.textContent = message;

            toast.className = `toast ${type} show`;

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            updatePermissionSelect();
            initApp();
        });
    </script>
</x-app-layout>


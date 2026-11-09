@can('color_create')
<x-app-layout>
    @section('title', 'Crear Color')
    @section('subtitle', 'Registrar nuevo color')

    <style>
        body {
            box-sizing: border-box;
        }

        :root{
            --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B;
            --bg-from:#f8fafc; --bg-to:#e2e8f0;
            --card-bg:rgb(255, 255, 255); --card-border:rgba(15,23,42,0.08);
            --muted:#64748b; --text:#0f172a;
            --link-hover-bg: rgba(15,23,42,0.06);
            --active-bg: rgba(30,58,138,0.12);
            --active-text: #0f172a;
        }
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            background: linear-gradient(135deg,var(--bg-from),var(--bg-to));
            color:var(--text);
        }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .btn-secondary{ background:rgba(15,23,42,0.06) }
        .btn-success{ background:var(--color-secondary); color:#fff }
        .btn-warning{ background:var(--color-accent); color:#111827 }

        /* Formulario */
        .form-input{
            backdrop-filter:blur(8px);
            border-radius:.75rem;
            padding:.75rem 1rem;
            width:100%;
            color:var(--text);
            transition:.2s;
        }
        .form-input:focus{
            outline:none;
            border-color:var(--color-primary);
            box-shadow:0 0 0 3px rgba(30,58,138,0.1);
        }
        .form-label{
            display:block;
            font-weight:600;
            margin-bottom:.5rem;
            color:var(--text);
        }

        /* Animaciones */
        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }
    </style>

    <!-- Contenido principal -->
    <div class="container max-w-4xl px-6 pb-20 mx-auto">
        <div class="p-8 glass rounded-2xl fade-in">
            <div class="mb-8 text-center">
                <div class="grid w-16 h-16 mx-auto mb-4 rounded-2xl place-items-center bg-blue-500/20">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <h2 class="mb-2 text-2xl font-bold">Crear Color</h2>
                <p class="text-slate-400">Registra un nuevo color en el sistema</p>
            </div>

            <form id="crearColorForm" action="{{ route('administracion.colors.store') }}" method="POST">
                @csrf

                <!-- Campo de color y Selector -->
                <div class="mb-6">
                    <label class="form-label">Color:</label>

                    <div class="p-5 glass rounded-2xl border border-gray-200/80 mb-4 bg-white/70">
                        <!-- Muestra visual y campo de entrada -->
                        <div class="flex flex-col sm:flex-row items-center gap-4 mb-4">
                            <!-- Visualizador del color en tiempo real -->
                            <div class="flex items-center gap-3">
                                <div id="colorPreview"
                                     class="w-14 h-14 rounded-2xl shadow-md border-2 border-white ring-2 ring-gray-300 transition-all flex items-center justify-center"
                                     style="background-color: #3b82f6;">
                                </div>
                                <!-- Selector nativo tipo color picker -->
                                <label for="nativeColorPicker" class="cursor-pointer group flex flex-col items-center">
                                    <input type="color" id="nativeColorPicker" value="#3b82f6" class="sr-only">
                                    <span class="px-3 py-2 text-xs font-semibold rounded-xl bg-blue-50 text-blue-700 border border-blue-200 group-hover:bg-blue-100 transition-colors">
                                        🎨 Paleta
                                    </span>
                                </label>
                            </div>

                            <!-- Input de texto -->
                            <div class="flex-1 w-full">
                                <input
                                    type="text"
                                    name="color"
                                    id="colorInput"
                                    class="bg-blue-50 border border-blue-200 form-input font-medium text-base"
                                    placeholder="Nombre del color (ej. Azul, Rojo, Negro)..."
                                    value="{{ old('color') }}"
                                    required
                                    autofocus
                                >
                            </div>
                        </div>

                        <!-- Selector rápido de colores predefinidos -->
                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-2">Colores sugeridos rápidos:</p>
                            <div class="flex flex-wrap gap-2" id="presetColors">
                                @php
                                    $presets = [
                                        ['nombre' => 'Negro', 'hex' => '#1f2937'],
                                        ['nombre' => 'Blanco', 'hex' => '#ffffff'],
                                        ['nombre' => 'Gris', 'hex' => '#6b7280'],
                                        ['nombre' => 'Gris Claro', 'hex' => '#9ca3af'],
                                        ['nombre' => 'Gris Oscuro', 'hex' => '#374151'],
                                        ['nombre' => 'Azul', 'hex' => '#3b82f6'],
                                        ['nombre' => 'Azul Marino', 'hex' => '#1e3a8a'],
                                        ['nombre' => 'Celeste', 'hex' => '#7dd3fc'],
                                        ['nombre' => 'Rojo', 'hex' => '#ef4444'],
                                        ['nombre' => 'Rojo Vino', 'hex' => '#722f37'],
                                        ['nombre' => 'Verde', 'hex' => '#22c55e'],
                                        ['nombre' => 'Verde Oscuro', 'hex' => '#15803d'],
                                        ['nombre' => 'Amarillo', 'hex' => '#eab308'],
                                        ['nombre' => 'Naranja', 'hex' => '#f97316'],
                                        ['nombre' => 'Morado', 'hex' => '#a855f7'],
                                        ['nombre' => 'Rosa', 'hex' => '#ec4899'],
                                        ['nombre' => 'Café', 'hex' => '#78350f'],
                                        ['nombre' => 'Beige', 'hex' => '#f5f5dc'],
                                        ['nombre' => 'Plateado', 'hex' => '#e5e7eb'],
                                        ['nombre' => 'Dorado', 'hex' => '#fbbf24'],
                                    ];
                                @endphp
                                @foreach($presets as $p)
                                    <button type="button"
                                            onclick="selectPresetColor('{{ $p['nombre'] }}', '{{ $p['hex'] }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-lg border border-gray-200 bg-white hover:bg-gray-50 shadow-sm transition-all hover:scale-105 active:scale-95"
                                            title="{{ $p['nombre'] }} ({{ $p['hex'] }})">
                                        <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $p['hex'] }};"></span>
                                        <span class="font-medium text-gray-700">{{ $p['nombre'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @error('color')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-between gap-4 pt-6 border-t border-white/10">
                    <x-primary-link href="{{ route('administracion.colors.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                        {{ __('Regresar') }}
                    </x-primary-link>
                    <x-primary-button type="submit" class="btn-save">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Guardar
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
        <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>

    <script>
    <script>
        // Mapeo JS para vista previa inmediata
        const colorNameMap = {
            'negro': '#1f2937', 'blanco': '#ffffff', 'gris': '#6b7280', 'gris claro': '#9ca3af',
            'gris oscuro': '#374151', 'plata': '#cbd5e1', 'plateado': '#e5e7eb', 'azul': '#3b82f6',
            'azul oscuro': '#1e40af', 'azul claro': '#60a5fa', 'azul marino': '#1e3a8a',
            'azul rey': '#2563eb', 'celeste': '#7dd3fc', 'turquesa': '#14b8a6', 'rojo': '#ef4444',
            'rojo oscuro': '#b91c1c', 'rojo vino': '#722f37', 'vino': '#722f37', 'verde': '#22c55e',
            'verde oscuro': '#15803d', 'verde claro': '#4ade80', 'amarillo': '#eab308',
            'naranja': '#f97316', 'morado': '#a855f7', 'violeta': '#8b5cf6', 'lila': '#c084fc',
            'fucsia': '#d946ef', 'rosa': '#ec4899', 'rosado': '#ffc0cb', 'cafe': '#78350f',
            'café': '#78350f', 'marron': '#78350f', 'marrón': '#78350f', 'beige': '#f5f5dc',
            'dorado': '#fbbf24', 'oro': '#ffd700'
        };

        function resolveColor(text) {
            if (!text) return '#3b82f6';
            const clean = text.trim().toLowerCase();
            if (/^#([a-f0-9]{3}){1,2}$/i.test(clean)) return clean;
            if (colorNameMap[clean]) return colorNameMap[clean];
            for (const key in colorNameMap) {
                if (clean.includes(key)) return colorNameMap[key];
            }
            return '#3b82f6';
        }

        function updatePreview(hex) {
            const preview = document.getElementById('colorPreview');
            const nativePicker = document.getElementById('nativeColorPicker');
            if (preview) preview.style.backgroundColor = hex;
            if (nativePicker && /^#[0-9a-f]{6}$/i.test(hex)) nativePicker.value = hex;
        }

        function selectPresetColor(nombre, hex) {
            const input = document.getElementById('colorInput');
            if (input) {
                input.value = nombre;
                updatePreview(hex);
            }
        }

        // Sincronizar inputs
        document.addEventListener('DOMContentLoaded', () => {
            const colorInput = document.getElementById('colorInput');
            const nativePicker = document.getElementById('nativeColorPicker');

            if (colorInput) {
                colorInput.addEventListener('input', (e) => {
                    const resolved = resolveColor(e.target.value);
                    updatePreview(resolved);
                });
                if (colorInput.value) {
                    updatePreview(resolveColor(colorInput.value));
                }
            }

            if (nativePicker) {
                nativePicker.addEventListener('input', (e) => {
                    updatePreview(e.target.value);
                });
            }
        });

        // Toast
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');

            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // Envío del formulario
        document.getElementById('crearColorForm').addEventListener('submit', (e) => {
            showToast('Creando color...', 'info');
        });

        // Inicialización
        function init() {
            showToast('Módulo de creación de color cargado', 'info');
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan


<section>
    <div class="section-header">
      <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /> <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </svg>
      <h2>Cambiar Contraseña</h2>
     </div>

    <form id="passwordForm" method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-grid">
            <div class="form-group">
                <label for="current_password">
                    <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /> <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg> Contraseña Actual
                </label>
                <div class="input-wrapper">
                    <input type="password" id="current_password" name="current_password" class="form-input" placeholder="Ingresa tu contraseña actual" required autocomplete="current-password">
                    <div class="input-icon">
                        <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" /> <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>
                </div>
                @error('current_password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">
                    <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg> Nueva Contraseña
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Ingresa tu nueva contraseña" required autocomplete="new-password">
                    <div class="input-icon">
                        <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                </div>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">
                    <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" />
                    </svg> Repetir Nueva Contraseña
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirma tu nueva contraseña" required autocomplete="new-password">
                    <div class="input-icon">
                        <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                </div>
                @error('password_confirmation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save" id="saveBtn">
                <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" /> <polyline points="17 21 17 13 7 13 7 21" /> <polyline points="7 3 7 8 15 8" />
                </svg> Guardar Cambios
            </button>
            <button type="button" class="btn-cancel" onclick="clearPasswordFields()">Cancelar</button>
        </div>

        @if (session('status') === 'password-updated')
            <div class="success-toast show" id="successToast">
                <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <div class="toast-content">
                    <span class="toast-title">¡Éxito!</span>
                    <span class="toast-message">Tu contraseña ha sido actualizada correctamente.</span>
                </div>
            </div>
        @endif
    </form>
</section>

<script>
function clearPasswordFields() {
    if (confirm('¿Cancelar cambio de contraseña?\\n\\nSe borrarán todos los campos ingresados.')) {
        document.getElementById('current_password').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password_confirmation').value = '';
    }
}

// Auto-hide success toast after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successToast = document.getElementById('successToast');
    if (successToast) {
        setTimeout(function() {
            successToast.classList.remove('show');
        }, 5000);
    }
});
</script>


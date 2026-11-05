/**
 * Sistema de Validación de Formularios - Intranet
 * Proporciona validación cliente-side en tiempo real para todos los formularios
 */

class FormValidator {
    constructor(formId, rules, messages = {}) {
        this.form = document.getElementById(formId);
        this.rules = rules;
        this.messages = messages;
        this.errors = {};

        if (this.form) {
            this.init();
        }
    }

    init() {
        // Configurar mensajes personalizados
        this.customMessages = {
            required: 'Este campo es obligatorio',
            email: 'Ingrese un correo electrónico válido',
            min: 'El valor mínimo es {0}',
            max: 'El valor máximo es {0}',
            minlength: 'Mínimo {0} caracteres',
            maxlength: 'Máximo {0} caracteres',
            number: 'Ingrese solo números',
            pattern: 'Formato inválido',
            unique: 'Este valor ya existe',
            ...this.messages
        };

        // Agregar eventos a los campos
        this.addEventListeners();

        // Interceptar submit
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
                this.showFormErrors();
                return false;
            }

            // Mostrar indicador de carga
            this.showLoading();
            return true;
        });
    }

    addEventListeners() {
        const fields = this.form.querySelectorAll('[name]');

        fields.forEach(field => {
            const name = field.name;
            if (!this.rules[name]) return;

            // Validar en blur (al perder el foco)
            field.addEventListener('blur', () => {
                this.validateField(field);
            });

            // Validar en input (con debounce para mejor rendimiento)
            let debounceTimer;
            field.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.validateField(field);
                }, 300);
            });

            // Limpiar error al cambiar valor
            field.addEventListener('change', () => {
                this.clearFieldError(field);
            });
        });
    }

    validateField(field) {
        const name = field.name;
        const value = field.value;
        const fieldRules = this.rules[name];

        if (!fieldRules) return true;

        this.errors[name] = [];

        // Validar requerido
        if (fieldRules.required && !value.trim()) {
            this.errors[name].push(this.getMessage('required', name));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Si está vacío y no es requerido, pasar siguientes validaciones
        if (!value.trim()) return true;

        // Validar email
        if (fieldRules.email && !this.isValidEmail(value)) {
            this.errors[name].push(this.getMessage('email', name));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar número
        if (fieldRules.number && !this.isValidNumber(value)) {
            this.errors[name].push(this.getMessage('number', name));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar mínimo
        if (fieldRules.minlength && value.length < fieldRules.minlength) {
            this.errors[name].push(this.getMessage('minlength', name, fieldRules.minlength));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar máximo
        if (fieldRules.maxlength && value.length > fieldRules.maxlength) {
            this.errors[name].push(this.getMessage('maxlength', name, fieldRules.maxlength));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar mínimo numérico
        if (fieldRules.min && parseFloat(value) < fieldRules.min) {
            this.errors[name].push(this.getMessage('min', name, fieldRules.min));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar máximo numérico
        if (fieldRules.max && parseFloat(value) > fieldRules.max) {
            this.errors[name].push(this.getMessage('max', name, fieldRules.max));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar patrón (regex)
        if (fieldRules.pattern && !fieldRules.pattern.test(value)) {
            this.errors[name].push(this.getMessage('pattern', name));
            this.showFieldError(field, this.errors[name][0]);
            return false;
        }

        // Validar confirmación de contraseña
        if (fieldRules.confirmPassword) {
            const passwordField = this.form.querySelector(`[name="${fieldRules.confirmPassword}"]`);
            if (passwordField && value !== passwordField.value) {
                this.errors[name].push('Las contraseñas no coinciden');
                this.showFieldError(field, this.errors[name][0]);
                return false;
            }
        }

        // Campo válido
        this.clearFieldError(field);
        return true;
    }

    validate() {
        let isValid = true;
        const fields = this.form.querySelectorAll('[name]');

        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFormErrors() {
        // Enfocar primer campo con error
        const firstErrorField = this.form.querySelector('[name]');
        if (firstErrorField) {
            // Scroll al primer error
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Mostrar toast de errores
        const errorCount = Object.keys(this.errors).filter(k => this.errors[k].length > 0).length;
        if (errorCount > 0) {
            this.showToast(`Hay ${errorCount} campo(s) con errores. Por favor corríjalos.`, 'error');
        }
    }

    showFieldError(field, message) {
        // Remover error previo
        this.clearFieldError(field);

        // Agregar clase de error
        field.classList.add('border-red-500', 'focus:border-red-500');
        field.classList.remove('border-none');

        // Crear elemento de error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
        errorDiv.textContent = message;

        // Insertar después del campo
        const parent = field.closest('div') || field.parentElement;
        if (parent) {
            parent.appendChild(errorDiv);

            // Para selects, insertar después del select
            if (field.tagName === 'SELECT') {
                const nextSibling = parent.querySelector('.error-message');
                if (!nextSibling) {
                    parent.parentElement.appendChild(errorDiv);
                }
            }
        }

        // Agregar evento para limpiar al interactuar
        field.addEventListener('input', () => this.clearFieldError(field), { once: true });
    }

    clearFieldError(field) {
        field.classList.remove('border-red-500', 'focus:border-red-500');

        const parent = field.closest('div') || field.parentElement;
        if (parent) {
            const errorDiv = parent.querySelector('.error-message');
            if (errorDiv) errorDiv.remove();

            // También buscar en el padre del padre para selects
            if (parent.parentElement) {
                const errorDiv2 = parent.parentElement.querySelector('.error-message');
                if (errorDiv2) errorDiv2.remove();
            }
        }
    }

    getMessage(rule, fieldName, param = null) {
        const key = `${fieldName}.${rule}`;
        if (this.messages[key]) {
            return this.messages[key].replace('{0}', param);
        }

        let message = this.customMessages[rule] || `Error en ${fieldName}`;
        return message.replace('{0}', param);
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidNumber(value) {
        return /^-?\d+(\.\d+)?$/.test(value);
    }

    showLoading() {
        // Encontrar botón de submit
        const submitBtn = this.form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Guardando...`;
        }
    }

    showToast(message, type = 'info') {
        if (typeof Swal !== 'undefined' && (type === 'error' || type === 'warning')) {
            Swal.fire({
                icon: type,
                title: type === 'error' ? 'Error' : 'Atención',
                text: message,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        if (typeof window.showAppToast === 'function') {
            window.showAppToast(message, type);
        } else if (typeof window.showToast === 'function' && window.showToast !== showToast) {
            window.showToast(message, type);
        }
    }
}

// Funciones helper para inicializar validaciones
function initFormValidation(formId, rules, messages = {}) {
    return new FormValidator(formId, rules, messages);
}

// Validación específica para formularios de usuarios
function initUserValidation() {
    initFormValidation('registrationForm', {
        name: { required: true, minlength: 2, maxlength: 50 },
        lastname: { required: true, minlength: 2, maxlength: 50 },
        cedula: { required: true, pattern: /^\d{1,2}\.\d{3}\.\d{3}$/ },
        fecha_nacimiento: { required: true },
        username: { required: true, minlength: 3, maxlength: 20 },
        email: { required: true, email: true },
        password: { required: true, minlength: 8 },
        password_confirmation: { required: true, confirmPassword: 'password' },
        roles: { required: true },
        departamento: { required: true }
    }, {
        'name.required': 'El nombre es obligatorio',
        'name.minlength': 'Mínimo 2 caracteres',
        'lastname.required': 'El apellido es obligatorio',
        'cedula.required': 'La cédula es obligatoria',
        'cedula.pattern': 'Formato: 12.345.678',
        'email.email': 'Correo electrónico inválido',
        'password.minlength': 'Mínimo 8 caracteres',
        'password_confirmation.confirmPassword': 'Las contraseñas no coinciden',
        'roles.required': 'Seleccione un rol',
        'departamento.required': 'Seleccione un departamento'
    });
}

// Validación para formularios de áreas
function initAreaValidation() {
    initFormValidation('crearAreaForm', {
        area: { required: true, minlength: 2, maxlength: 100 }
    }, {
        'area.required': 'El nombre del área es obligatorio',
        'area.minlength': 'Mínimo 2 caracteres',
        'area.maxlength': 'Máximo 100 caracteres'
    });
}

// Validación para formularios de artículos
function initArticuloValidation() {
    initFormValidation('crearArticuloForm', {
        articulo: { required: true, minlength: 2, maxlength: 100 },
        unidad_medida_id: { required: true },
        stock_min: { required: true, number: true, min: 0 }
    }, {
        'articulo.required': 'El nombre del artículo es obligatorio',
        'articulo.minlength': 'Mínimo 2 caracteres',
        'unidad_medida_id.required': 'Seleccione una unidad de medida',
        'stock_min.required': 'El stock mínimo es obligatorio',
        'stock_min.number': 'Ingrese un número válido',
        'stock_min.min': 'El valor no puede ser negativo'
    });
}

// Validación para formularios de edición de artículos
function initArticuloEditValidation() {
    initFormValidation('editarArticuloForm', {
        articulo: { required: true, minlength: 2, maxlength: 100 },
        unidad_medida_id: { required: true },
        stock_min: { required: true, number: true, min: 0 }
    }, {
        'articulo.required': 'El nombre del artículo es obligatorio',
        'articulo.minlength': 'Mínimo 2 caracteres',
        'unidad_medida_id.required': 'Seleccione una unidad de medida',
        'stock_min.required': 'El stock mínimo es obligatorio',
        'stock_min.number': 'Ingrese un número válido',
        'stock_min.min': 'El valor no puede ser negativo'
    });
}

// Validación para formularios de roles
function initRoleValidation() {
    const forms = document.querySelectorAll('form[id="roleForm"]');

    forms.forEach(form => {
        // Obtener el ID del rol si existe (para edición)
        const roleId = form.dataset.roleId || null;

        // Validación en tiempo real del nombre del rol
        const nameInput = form.querySelector('[name="name"]');
        if (nameInput) {
            nameInput.addEventListener('blur', async function() {
                const name = this.value.trim();
                if (name.length < 2) return;

                try {
                    let url = `/gtic/roles/check-name?name=${encodeURIComponent(name)}`;
                    if (roleId) {
                        url += `&exclude_id=${roleId}`;
                    }

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (data.exists) {
                        // Mostrar error
                        clearFieldError(this);
                        this.classList.add('border-red-500', 'focus:border-red-500');
                        this.classList.remove('border-none');

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
                        errorDiv.textContent = 'Este nombre de rol ya está registrado';

                        const parent = this.closest('div');
                        if (parent) {
                            parent.appendChild(errorDiv);
                        }
                    }
                } catch (error) {
                    console.error('Error validando nombre de rol:', error);
                }
            });
        }

        form.addEventListener('submit', function(e) {
            const permissions = window.rolePermissions || [];

            // Validar que hay permisos seleccionados
            /* if (permissions.length === 0) {
                e.preventDefault();
                showToast('Debe agregar al menos un permiso', 'error');
                return false;
            } */

            // Validar nombre del rol
            const nameInput = form.querySelector('[name="name"]');
            if (nameInput) {
                const name = nameInput.value.trim();
                if (!name) {
                    e.preventDefault();
                    showToast('El nombre del rol es obligatorio', 'error');
                    return false;
                }
                if (name.length < 2) {
                    e.preventDefault();
                    showToast('El nombre del rol debe tener al menos 2 caracteres', 'error');
                    return false;
                }
            }

            // Mostrar indicador de carga
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> Guardando...`;
            }
            return true;
        });
    });
}

// Mostrar notificación / alerta (función global)
function showToast(message, type = 'info') {
    // Si es error o warning, mostrar SweetAlert para máxima visibilidad y estilo limpio
    if (typeof Swal !== 'undefined' && (type === 'error' || type === 'warning')) {
        Swal.fire({
            icon: type,
            title: type === 'error' ? 'Error' : 'Atención',
            text: message,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    // Delegar al toast del layout
    if (typeof window.showAppToast === 'function') {
        window.showAppToast(message, type);
    } else if (typeof window.showToast === 'function' && window.showToast !== showToast) {
        window.showToast(message, type);
    }
}

// Auto-inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Validar formularios automáticamente por ID
    if (document.getElementById('registrationForm')) {
        initUserValidation();
    }

    if (document.getElementById('crearAreaForm')) {
        initAreaValidation();
    }

    if (document.getElementById('editarAreaForm')) {
        initAreaEditValidation();
    }

    if (document.getElementById('crearArticuloForm')) {
        initArticuloValidation();
    }

    if (document.getElementById('editarArticuloForm')) {
        initArticuloEditValidation();
    }

    if (document.getElementById('roleForm')) {
        initRoleValidation();
    }

    // Otros formularios comunes - validación automática por nombre de campo
    initCommonFormValidation();
});

// Validación automática para formularios comunes
function initCommonFormValidation() {
    // Validar todos los formularios con campos requeridos
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // No validar si ya tiene un validador específico
        if (form.id && (
            form.id === 'registrationForm' ||
            form.id === 'crearAreaForm' ||
            form.id === 'editarAreaForm' ||
            form.id === 'crearArticuloForm' ||
            form.id === 'editarArticuloForm' ||
            form.id === 'roleForm' ||
            form.id === 'roleEditForm'
        )) {
            return;
        }

        // No validar formularios que contengan el campo permissions (formularios de roles)
        const hasPermissionsField = form.querySelector('[name="permissions[]"]');
        if (hasPermissionsField) {
            return;
        }

        form.addEventListener('submit', function(e) {
            let hasErrors = false;
            const fields = form.querySelectorAll('[name]');

            fields.forEach(field => {
                const name = field.name;
                const value = field.value;

                // Limpiar errores previos
                clearFieldError(field);

                // Validar campos requeridos
                if (field.hasAttribute('required') && !value.trim()) {
                    showFieldError(field, 'Este campo es obligatorio');
                    hasErrors = true;
                }

                // Validar email
                if (field.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    showFieldError(field, 'Ingrese un correo electrónico válido');
                    hasErrors = true;
                }

                // Validar números
                if (field.type === 'number' && value) {
                    if (field.hasAttribute('min') && parseFloat(value) < parseFloat(field.getAttribute('min'))) {
                        showFieldError(field, `El valor mínimo es ${field.getAttribute('min')}`);
                        hasErrors = true;
                    }
                    if (field.hasAttribute('max') && parseFloat(value) > parseFloat(field.getAttribute('max'))) {
                        showFieldError(field, `El valor máximo es ${field.getAttribute('max')}`);
                        hasErrors = true;
                    }
                }
            });

            if (hasErrors) {
                e.preventDefault();
                showToast('Por favor complete todos los campos requeridos', 'error');
                return false;
            }

            // Mostrar indicador de carga
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> Guardando...`;

                // Restaurar después de 5 segundos si hay error de red
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }

            return true;
        });

        // Validación en blur
        const fields = form.querySelectorAll('[name]');
        fields.forEach(field => {
            field.addEventListener('blur', function() {
                clearFieldError(this);

                if (this.hasAttribute('required') && !this.value.trim()) {
                    showFieldError(this, 'Este campo es obligatorio');
                }

                if (this.type === 'email' && this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                    showFieldError(this, 'Ingrese un correo electrónico válido');
                }
            });
        });
    });
}

// Función para mostrar error en campo
function showFieldError(field, message) {
    clearFieldError(field);

    field.classList.add('border-red-500', 'focus:border-red-500');
    field.classList.remove('border-none');

    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
    errorDiv.textContent = message;

    const parent = field.closest('div');
    if (parent) {
        parent.appendChild(errorDiv);
    }
}

// Función para limpiar error de campo
function clearFieldError(field) {
    field.classList.remove('border-red-500', 'focus:border-red-500');

    const parent = field.closest('div');
    if (parent) {
        const errorDiv = parent.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
    }
}

// Validación para formularios de edición de áreas
function initAreaEditValidation() {
    initFormValidation('editarAreaForm', {
        area: { required: true, minlength: 2, maxlength: 100 }
    }, {
        'area.required': 'El nombre del área es obligatorio',
        'area.minlength': 'Mínimo 2 caracteres',
        'area.maxlength': 'Máximo 100 caracteres'
    });
}

// Exportar para uso global
window.FormValidator = FormValidator;
window.initFormValidation = initFormValidation;
window.initUserValidation = initUserValidation;
window.initAreaValidation = initAreaValidation;
window.initAreaEditValidation = initAreaEditValidation;
// Validación para formularios de permisos
function initPermissionValidation() {
    const crearForm = document.getElementById('crearPermisoForm');
    const editarForm = document.getElementById('editarPermisoForm');

    // Función para agregar validación a un formulario
    const setupFormValidation = (form) => {
        if (!form) return;

        // Obtener el ID del permiso si existe (para edición)
        const permissionId = form.dataset.permissionId || null;

        const nameInput = form.querySelector('[name="name"]');
        if (nameInput) {
            // Validar en tiempo real al perder el foco
            nameInput.addEventListener('blur', async function() {
                const name = this.value.trim();
                if (name.length < 2) return;

                try {
                    let url = `/gtic/permisos/check-name?name=${encodeURIComponent(name)}`;
                    if (permissionId) {
                        url += `&exclude_id=${permissionId}`;
                    }

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (data.exists) {
                        // Mostrar error
                        clearFieldError(this);
                        this.classList.add('border-red-500', 'focus:border-red-500');
                        this.classList.remove('border-none');

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
                        errorDiv.textContent = 'Este permiso ya está registrado';

                        const parent = this.closest('div');
                        if (parent) {
                            parent.appendChild(errorDiv);
                        }
                    }
                } catch (error) {
                    console.error('Error validando nombre de permiso:', error);
                }
            });
        }

        // Validar al enviar
        form.addEventListener('submit', function(e) {
            const nameInput = form.querySelector('[name="name"]');
            if (nameInput) {
                const name = nameInput.value.trim();

                if (!name) {
                    e.preventDefault();
                    showToast('El nombre del permiso es obligatorio', 'error');
                    return false;
                }

                // Validar formato (solo letras, números, guiones bajos y guiones)
                const regex = /^[a-zA-Z_][a-zA-Z0-9_-]*$/;
                if (!regex.test(name)) {
                    e.preventDefault();
                    showToast('El nombre solo puede contener letras, números, guiones y guiones bajos', 'error');
                    return false;
                }
            }

            // Mostrar indicador de carga
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg> Guardando...`;
            }
            return true;
        });
    };

    setupFormValidation(crearForm);
    setupFormValidation(editarForm);
}

// Auto-inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Validar formularios automáticamente por ID
    if (document.getElementById('registrationForm')) {
        initUserValidation();
    }

    if (document.getElementById('crearAreaForm')) {
        initAreaValidation();
    }

    if (document.getElementById('crearArticuloForm')) {
        initArticuloValidation();
    }

    if (document.getElementById('editarArticuloForm')) {
        initArticuloEditValidation();
    }

    if (document.getElementById('roleForm')) {
        initRoleValidation();
    }

    // Inicializar validación de permisos
    if (document.getElementById('crearPermisoForm') || document.getElementById('editarPermisoForm')) {
        initPermissionValidation();
    }

    // Otros formularios comunes - validación automática por nombre de campo
    initCommonFormValidation();
});

window.initArticuloValidation = initArticuloValidation;
window.initArticuloEditValidation = initArticuloEditValidation;
window.initRoleValidation = initRoleValidation;
window.initPermissionValidation = initPermissionValidation;
window.showToast = showToast;


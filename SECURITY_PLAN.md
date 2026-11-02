# Plan de Implementación de Capas de Seguridad

## Resumen Ejecutivo

Este documento presenta un plan integral para implementar múltiples capas de seguridad en el sistema intranet, protegiendo contra amenazas comunes y fortaleciendo la postura de seguridad general.

---

## Estado Actual de Seguridad

### ✅ Capas Existentes:
| Capa | Estado | Implementación |
|------|--------|----------------|
| Autenticación | ✅ Activo | Laravel Auth + Spatie Permissions |
| Control de Sesiones | ✅ Activo | CheckSessionTimeout (120 min) |
| Verificación de Estado | ✅ Activo | CheckUserStatus (cuenta activa) |
| CSRF Protection | ✅ Activo | VerifyCsrfToken |
| Cookie Encryption | ✅ Activo | EncryptCookies |
| CORS | ✅ Configurado | Orígenes específicos |

### ❌ Capas Faltantes:
- Rate Limiting (intentos de login)
- Two-Factor Authentication (2FA)
- Requisitos de fortaleza de contraseña
- Notificaciones de login sospechoso
- Encabezados de seguridad HTTP
- Bloqueo de cuenta por intentos fallidos
- Caducidad de contraseña
- Auditoría detallada de seguridad
- Validación de entrada robusta

---

## Plan de Implementación por Fases

### **FASE 1: Seguridad de Autenticación** (Alta Prioridad)

#### 1.1 Rate Limiting para Login
**Archivo**: `app/Providers/RouteServiceProvider.php` o `routes/web.php`

```php
// Implementar limitación de intentos de login
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});
```

#### 1.2 Password Strength Requirements
**Archivo**: `app/Rules/PasswordStrength.php` (nuevo)

Reglas:
- Mínimo 8 caracteres
- Al menos 1 mayúscula
- Al menos 1 número
- Al menos 1 carácter especial

#### 1.3 Account Lockout
**Archivo**: `app/Http/Middleware/AccountLockout.php` (nuevo)

Después de 5 intentos fallidos:
- Bloquear cuenta por 30 minutos
- O enviar email de alerta

#### 1.4 Password Expiration
**Archivo**: `database/migrations/XXXX_XX_XX_add_password_expiration.php` (nuevo)

- Requerir cambio de contraseña cada 90 días
- No permitir reutilizar últimas 5 contraseñas

---

### **FASE 2: Seguridad de Sesión** (Media Prioridad)

#### 2.1 Concurrent Session Control
**Archivo**: `app/Http/Middleware/PreventConcurrentSessions.php` (nuevo)

- Máximo 2 sesiones simultáneas
- Invalidar sesión anterior al iniciar nueva

#### 2.2 Session Fingerprinting
**Archivo**: `app/Http/Middleware/SessionFingerprint.php` (nuevo)

Verificar:
- User Agent
- IP Address
- Dispositivo

#### 2.3 Login Notifications
**Archivo**: `app/Listeners/SendLoginNotification.php` (nuevo)

Notificar por email:
- Nuevos dispositivos
- Nuevas ubicaciones
- Intentos de login fallidos

---

### **FASE 3: Seguridad de Aplicación** (Media Prioridad)

#### 3.1 HTTP Security Headers
**Archivo**: `app/Http/Middleware/SecurityHeaders.php` (nuevo)

Encabezados:
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security (HTTPS)
- Content-Security-Policy

#### 3.2 Input Validation Enhancement
**Archivos**: `app/Http/Requests/*`

- Validación más estricta en todos los forms
- Sanitización de entradas
- Protección SQL Injection (Laravel ya lo hace)

#### 3.3 File Upload Security
**Archivos**: `app/Http/Controllers/*` con upload

Validar:
- Extensiones permitidas
- Tamaño máximo
- Tipo MIME real
- Escaneo de malware
- Almacenamiento fuera de webroot

---

### **FASE 4: Autorización y Auditoría** (Baja Prioridad)

#### 4.1 Enhanced Audit Logging
**Archivo**: `app/Providers/EventServiceProvider.php`

Events a auditar:
- Login/Logout
- Cambios de contraseña
- Cambios de permisos
- Acceso a datos sensibles
- Exportaciones de datos

#### 4.2 Two-Factor Authentication (2FA)
**Archivos**: Nuevos

- Integrar con Laravel 2FA
- QR Code para apps autenticadoras
- Códigos de respaldo

---

## Archivos a Crear/Modificar

### Nuevos Archivos:
```
app/Http/Middleware/AccountLockout.php
app/Http/Middleware/SecurityHeaders.php
app/Http/Middleware/PreventConcurrentSessions.php
app/Http/Middleware/SessionFingerprint.php
app/Rules/PasswordStrength.php
app/Rules/PasswordHistory.php
app/Observers/UserObserver.php
app/Listeners/LoginFailedListener.php
app/Listeners/LoginSuccessListener.php
app/Notifications/NewDeviceLogin.php
app/Notifications/PasswordChanged.php
app/Notifications/SecurityAlert.php
database/migrations/XXXX_XX_XX_add_password_expiration.php
database/migrations/XXXX_XX_XX_add_login_fields.php
```

### Archivos a Modificar:
```
app/Http/Kernel.php
app/Providers/RouteServiceProvider.php
app/Models/User.php
routes/web.php
config/auth.php
app/Http/Controllers/Auth/*Controller.php
```

---

## Dependencias Necesarias

```json
{
    "require": {
        "pragmarx/google2fa": "^8.0",
        "bacon/bacon-qr-code": "^2.0"
    }
}
```

---

## Configuración Recomendada

### `.env` Security Settings:
```env
# Seguridad de Contraseña
PASSWORD_MIN_LENGTH=8
PASSWORD_EXPIRATION_DAYS=90
PASSWORD_HISTORY_COUNT=5
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION_MINUTES=30

# Sesión
MAX_CONCURRENT_SESSIONS=2
SESSION_FINGERPRINT=true

# Notificaciones
NOTIFY_LOGIN_NEW_DEVICE=true
NOTIFY_LOGIN_NEW_LOCATION=true

# 2FA
2FA_ENABLED=false
```

---

## Orden de Implementación Sugerida

1. **Semana 1**: Account Lockout + Rate Limiting
2. **Semana 2**: Password Strength + Password Expiration
3. **Semana 3**: HTTP Security Headers + Session Fingerprinting
4. **Semana 4**: Login Notifications
5. **Semana 5**: Enhanced Audit Logging
6. **Semana 6**: Two-Factor Authentication (Opcional)

---

## Métricas de Éxito

- Reducción de intentos de acceso no autorizados
- Tiempo de respuesta ante incidentes
- Cumplimiento de políticas de seguridad
- User feedback sobre seguridad

---

## Referencias

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CWE/SANS Top 25](https://www.sans.org/top25-software-errors/)


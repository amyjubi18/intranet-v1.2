<!DOCTYPE html>
<html lang="es" class="inicio-page">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Intranet - Sistema CODECYT</title>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Fuente -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
</head>


<style>


    html, body {
      height: 100%;
      width: 100%;
    }


    .container {
      background: rgba(255, 255, 255, 0.9);
      border: 2px solid rgba(59, 130, 246, 0.2);
      backdrop-filter: blur(12px);
      border-radius: 1.5rem;
      padding: 2.5rem;
      max-width: 550px;
      width: 100%;
      text-align: center;
      box-shadow: 0 20px 50px rgba(30, 58, 138, 0.15);
      animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .icon-container {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
      border-radius: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
      }
      50% {
        transform: scale(1.05);
        box-shadow: 0 15px 40px rgba(30, 58, 138, 0.4);
      }
    }

    .icon-container svg {
      animation: rotate 3s linear infinite;
    }

    @keyframes rotate {
      from {
        transform: rotate(0deg);
      }
      to {
        transform: rotate(360deg);
      }
    }

    .icon-container svg {
      width: 50px;
      height: 50px;
      color: white;
    }

    .badge {
      display: inline-block;
      background: rgba(245, 158, 11, 0.15);
      color: #d97706;
      padding: 0.5rem 1rem;
      border-radius: 0.75rem;
      font-weight: 600;
      font-size: 0.875rem;
      margin-bottom: 1.5rem;
    }

    h1 {
      font-size: 2rem;
      font-weight: 800;
      color: #1e3a8a;
      margin-bottom: 1rem;
      line-height: 1.2;
    }

    .subtitle {
      font-size: 1rem;
      color: #64748b;
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }

    .info-box {
      background: rgba(245, 158, 11, 0.1);
      border: 1px solid rgba(245, 158, 11, 0.3);
      border-radius: 0.75rem;
      padding: 1rem;
      margin: 1.5rem 0;
      text-align: left;
    }

    .info-box p {
      font-size: 0.875rem;
      color: #78350f;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: flex-start;
      gap: 0.5rem;
    }

    .info-box p:last-child {
      margin-bottom: 0;
    }

    .info-box strong {
      color: #92400e;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: #1e3a8a;
      color: white;
      border: 2px solid #1e3a8a;
      border-radius: 1rem;
      padding: 0.875rem 1.75rem;
      font-weight: 700;
      font-size: 1rem;
      transition: all 0.3s ease;
      cursor: pointer;
      text-decoration: none;
      margin-top: 1rem;
    }

    .btn:hover {
      background: #1e40af;
      border-color: #1e40af;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
    }

    .btn:active {
      transform: translateY(0);
    }

    .footer {
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(59, 130, 246, 0.2);
    }

    .footer p {
      font-size: 0.875rem;
      color: #64748b;
      margin-bottom: 0.25rem;
    }

    .company-name {
      font-weight: 700;
      color: #1e3a8a;
    }

    @media (max-width: 640px) {
      .container {
        padding: 2rem 1.5rem;
      }

      h1 {
        font-size: 1.75rem;
      }

      .icon-container {
        width: 80px;
        height: 80px;
      }

      .icon-container svg {
        width: 40px;
        height: 40px;
      }
    }
  </style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>



<body class="scroll-smooth @if(Auth::check()) @else bg-gradient-to-r from-slate-300 to-slate-500 @endif">

@if(Auth::check())
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
@include('layouts.navigation')
</aside>

<!-- Backdrop for mobile -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>
@endif

<!-- Contenido principal -->
<main class="@if(Auth::check()) main-content @endif" id="mainContent">
<!-- Header estilo tarjeta -->
<div class="p-6">
<div class="glass rounded-2xl p-6 mb-6">
<div class="flex items-center justify-between">
<!-- Logo CODECYT -->
<div class="flex items-center gap-3">
<img src="{{asset('img/logo.png')}}" class="h-20 w-auto" alt="Logo">
</div>

<!-- Título centrado -->
<div class="absolute left-1/2 transform -translate-x-1/2 text-center">
<h1 class="text-2xl font-extrabold">Intranet</h1>
<p class="text-sm text-slate-400">Bienvenido a la intranet de CODECYT</p>
</div>

<!-- Botón de Iniciar Sesión con animación -->
@auth
<div class="flex items-center gap-4">
<div class="w-11 h-11 rounded-lg bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center">
<span class="text-white font-bold text-xs">{{ substr(Auth::user()->name, 0, 2) }}</span>
</div>
<form method="POST" action="{{ route('logout') }}">
@csrf
<button type="submit" class="login-btn bg-red-600 hover:bg-red-700">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
<polyline points="16,17 21,12 16,7"/>
<line x1="21" y1="12" x2="9" y2="12"/>
</svg>
<span class="login-text">Cerrar Sesión</span>
</button>
</form>
</div>
@else
<button onclick="window.location.href='/login'" class="login-btn">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
<polyline points="10,17 15,12 10,7"/>
<line x1="15" y1="12" x2="3" y2="12"/>
</svg>
<span class="login-text">Iniciar Sesión</span>
</button>
@endauth
</div>
</div>
</div>

<!-- Contenido del Dashboard -->
<div class="px-6 pb-8 ">
<!-- Accesos Directos Principales -->
{{-- <div class="glass rounded-2xl p-6 mb-6 fade-in">
<h2 class="text-xl font-bold mb-6">⚡ Accesos Directos</h2>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-5 gap-4">

<!-- Sistema de Soporte -->
<div class="quick-access-card" onclick="openSupport()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<circle cx="12" cy="12" r="3"/>
<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Sistema de Soporte</h3>
<p class="text-xs text-slate-400 mt-1">Reportar incidencias</p>
</div>

<!-- Solicitar Constancia -->
<div class="quick-access-card" onclick="requestCertificate()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
<polyline points="14,2 14,8 20,8"/>
<line x1="16" y1="13" x2="8" y2="13"/>
<line x1="16" y1="17" x2="8" y2="17"/>
<polyline points="10,9 9,9 8,9"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Constancia de Trabajo</h3>
<p class="text-xs text-slate-400 mt-1">Solicitar documento</p>
</div>

<!-- Solicitar Recibo -->
<div class="quick-access-card" onclick="requestPayslip()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<line x1="12" y1="1" x2="12" y2="23"/>
<path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Recibo de Pago</h3>
<p class="text-xs text-slate-400 mt-1">Descargar comprobante</p>
</div>

<!-- Formato de Requisición -->
<div class="quick-access-card" onclick="requestForm()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
<path d="M12 11h4"/>
<path d="M12 16h4"/>
<path d="M8 11h.01"/>
<path d="M8 16h.01"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Formato de Requisición</h3>
<p class="text-xs text-slate-400 mt-1">Solicitar materiales</p>
</div>

<!-- Planilla de Solicitud de Vacaciones -->
<div class="quick-access-card" onclick="requestVacation()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
<circle cx="12" cy="10" r="3"/>
<path d="M8 21l4-7 4 7"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Planilla de Solicitud de Vacaciones</h3>
<p class="text-xs text-slate-400 mt-1">Solicitar vacaciones</p>
</div>

<!-- Inventario de Consumibles -->
<div class="quick-access-card" onclick="openConsumables()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.35 2.65c-.14.15-.25.32-.25.5 0 .83.67 1.5 1.5 1.5H19M7 13v4a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-4m-6 0h6"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Inventario Consumibles</h3>
<p class="text-xs text-slate-400 mt-1">Gestionar consumibles</p>
</div>

<!-- Inventario de Bienes -->
<div class="quick-access-card" onclick="openAssets()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
<line x1="8" y1="21" x2="16" y2="21"/>
<line x1="12" y1="17" x2="12" y2="21"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Inventario Bienes</h3>
<p class="text-xs text-slate-400 mt-1">Gestionar bienes</p>
</div>

<!-- Inventario de Materiales -->
<div class="quick-access-card" onclick="openMaterials()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
<polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
<line x1="12" y1="22.08" x2="12" y2="12"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Inventario Materiales</h3>
<p class="text-xs text-slate-400 mt-1">Gestionar materiales</p>
</div>

<!-- Digitalización de Documentos -->
<div class="quick-access-card" onclick="openDigitalization()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
<polyline points="14,2 14,8 20,8"/>
<line x1="16" y1="13" x2="8" y2="13"/>
<line x1="16" y1="17" x2="8" y2="17"/>
<polyline points="10,9 9,9 8,9"/>
<path d="M9 9v1a1 1 0 0 0 1 1h1m-1-3h.01"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Digitalización Documentos</h3>
<p class="text-xs text-slate-400 mt-1">Digitalizar archivos</p>
</div>

<!-- Creación de Documentos -->
<div class="quick-access-card" onclick="openDocumentCreation()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Creación Documentos</h3>
<p class="text-xs text-slate-400 mt-1">Crear documentos</p>
</div>

<!-- Consultoría -->
<div class="quick-access-card" onclick="openConsultancy()">
<div class="quick-access-icon">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0-6 0"/>
<path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
</svg>
</div>
<h3 class="font-semibold text-sm">Consultoría</h3>
<p class="text-xs text-slate-400 mt-1">Servicios consultoría</p>
</div>
</div>
</div> --}}

{{-- <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

<!-- Columna izquierda: Calendario y Accesos Directos -->
<div class="lg:col-span-1 space-y-6">

<!-- Calendario de Próximos Eventos -->
<div class="glass rounded-2xl p-6 fade-in">
<div class="flex items-center justify-between mb-4">
<h2 class="text-xl font-bold">📅 Próximos Eventos</h2>
<div class="flex items-center gap-2">
<button onclick="previousMonth()" class="btn btn-secondary text-sm p-2">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<polyline points="15,18 9,12 15,6"/>
</svg>
</button>
<span id="currentMonth" class="text-sm font-semibold px-3">Enero 2024</span>
<button onclick="nextMonth()" class="btn btn-secondary text-sm p-2">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<polyline points="9,18 15,12 9,6"/>
</svg>
</button>
</div>
</div>

<div class="calendar-grid">
<!-- Encabezados de días -->
<div class="calendar-header">Dom</div>
<div class="calendar-header">Lun</div>
<div class="calendar-header">Mar</div>
<div class="calendar-header">Mié</div>
<div class="calendar-header">Jue</div>
<div class="calendar-header">Vie</div>
<div class="calendar-header">Sáb</div>

<!-- Días del calendario -->
<div class="calendar-day other-month">31</div>
<div class="calendar-day">1</div>
<div class="calendar-day">2</div>
<div class="calendar-day">3</div>
<div class="calendar-day">4</div>
<div class="calendar-day has-event"
onmouseenter="showEventTooltip(event, 'Reunión General', 'Reunión mensual de todos los departamentos para revisar objetivos y metas del mes. Sala de juntas principal a las 10:00 AM')"
onmouseleave="hideEventTooltip()">5</div>
<div class="calendar-day">6</div>

<div class="calendar-day">7</div>
<div class="calendar-day">8</div>
<div class="calendar-day">9</div>
<div class="calendar-day">10</div>
<div class="calendar-day">11</div>
<div class="calendar-day has-event"
onmouseenter="showEventTooltip(event, 'Capacitación Técnica', 'Sesión de capacitación sobre nuevas herramientas de desarrollo y metodologías ágiles. Aula de capacitación 2:00 PM - 5:00 PM')"
onmouseleave="hideEventTooltip()">12</div>
<div class="calendar-day">13</div>

<div class="calendar-day">14</div>
<div class="calendar-day today">15</div>
<div class="calendar-day">16</div>
<div class="calendar-day">17</div>
<div class="calendar-day">18</div>
<div class="calendar-day">19</div>
<div class="calendar-day has-event"
onmouseenter="showEventTooltip(event, 'Evento Corporativo', 'Celebración anual de la empresa con cena de gala y reconocimientos. Hotel Grand Palace 6:00 PM - 11:00 PM. Código de vestimenta: Formal')"
onmouseleave="hideEventTooltip()">20</div>

<div class="calendar-day">21</div>
<div class="calendar-day">22</div>
<div class="calendar-day">23</div>
<div class="calendar-day">24</div>
<div class="calendar-day has-event"
onmouseenter="showEventTooltip(event, 'Junta Directiva', 'Reunión trimestral de la junta directiva para revisar resultados financieros y estrategias futuras. Sala ejecutiva 9:00 AM - 12:00 PM')"
onmouseleave="hideEventTooltip()">25</div>
<div class="calendar-day">26</div>
<div class="calendar-day">27</div>

<div class="calendar-day">28</div>
<div class="calendar-day">29</div>
<div class="calendar-day">30</div>
<div class="calendar-day">31</div>
<div class="calendar-day other-month">1</div>
<div class="calendar-day other-month">2</div>
<div class="calendar-day other-month">3</div>
</div>

<!-- Lista de eventos próximos -->
<div class="mt-4 space-y-2">
<div class="flex items-center gap-3 p-2 rounded-lg bg-green-500/10 border border-green-500/20">
<div class="w-3 h-3 bg-green-500 rounded-full"></div>
<div class="flex-1">
<p class="text-sm font-semibold">Reunión General</p>
<p class="text-xs text-slate-400">5 de Enero - 10:00 AM</p>
</div>
</div>
<div class="flex items-center gap-3 p-2 rounded-lg bg-blue-500/10 border border-blue-500/20">
<div class="w-3 h-3 bg-blue-500 rounded-full"></div>
<div class="flex-1">
<p class="text-sm font-semibold">Capacitación</p>
<p class="text-xs text-slate-400">12 de Enero - 2:00 PM</p>
</div>
</div>
<div class="flex items-center gap-3 p-2 rounded-lg bg-purple-500/10 border border-purple-500/20">
<div class="w-3 h-3 bg-purple-500 rounded-full"></div>
<div class="flex-1">
<p class="text-sm font-semibold">Evento Corporativo</p>
<p class="text-xs text-slate-400">20 de Enero - 6:00 PM</p>
</div>
</div>
</div>
</div>

<!-- Directorio de Empleados -->
<div class="glass rounded-2xl p-6 fade-in">
<div class="flex items-center justify-between mb-4">
<h2 class="text-xl font-bold">📞 Directorio de Extensiones</h2>
<button class="btn btn-secondary text-sm" onclick="openDirectory()">Ver todas</button>
</div>

<div class="space-y-3">
<div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 cursor-pointer" onclick="callExtension('9100')">
<div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
</svg>
</div>
<div class="flex-1">
<p class="font-semibold text-sm">Presidencia</p>
<p class="text-xs text-slate-400">Oficina ejecutiva</p>
<p class="text-xs text-blue-400 font-mono">Ext. 9100</p>
</div>
</div>

<div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 cursor-pointer" onclick="callExtension('9113')">
<div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
</svg>
</div>
<div class="flex-1">
<p class="font-semibold text-sm">Gerencia de Tecnología</p>
<p class="text-xs text-slate-400">Departamento IT</p>
<p class="text-xs text-green-400 font-mono">Ext. 9113</p>
</div>
</div>

<div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 cursor-pointer" onclick="callExtension('9114')">
<div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-full flex items-center justify-center">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white">
<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
</svg>
</div>
<div class="flex-1">
<p class="font-semibold text-sm">Gestión Administrativa</p>
<p class="text-xs text-slate-400">Administración general</p>
<p class="text-xs text-orange-400 font-mono">Ext. 9114</p>
</div>
</div>
</div>
</div>

</div> --}}

<!-- Columna central y derecha: Noticias y Cumpleañeros -->
{{-- <div class="lg:col-span-2 space-y-6">

<!-- Últimas Noticias -->
<div class="glass rounded-2xl p-6 fade-in">
<div class="flex items-center justify-between mb-4">
<h2 class="text-xl font-bold">📰 Últimas Noticias</h2>
<button class="btn btn-secondary text-sm">Ver todas</button>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<!-- Noticia 1 -->
<div class="news-card" onclick="readNews(1)">
<div class="flex items-start gap-3">
<div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
</svg>
</div>
<div class="flex-1">
<span class="text-xs bg-blue-500/20 text-blue-600 px-2 py-1 rounded">Corporativo</span>
<h3 class="font-semibold mt-2 mb-1">Nueva Política de Trabajo Remoto</h3>
<p class="text-sm text-slate-400 mb-2">Se han actualizado las políticas para el trabajo desde casa, incluyendo nuevos beneficios...</p>
<p class="text-xs text-slate-500">15 de Enero, 2024</p>
</div>
</div>
</div>

<!-- Noticia 2 -->
<div class="news-card" onclick="readNews(2)">
<div class="flex items-start gap-3">
<div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-400">
<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
</svg>
</div>
<div class="flex-1">
<span class="text-xs bg-green-500/20 text-green-600 px-2 py-1 rounded">Logros</span>
<h3 class="font-semibold mt-2 mb-1">Récord de Ventas en Q4</h3>
<p class="text-sm text-slate-400 mb-2">El equipo de ventas superó todas las expectativas alcanzando un 125% de la meta...</p>
<p class="text-xs text-slate-500">12 de Enero, 2024</p>
</div>
</div>
</div>

<!-- Noticia 3 -->
<div class="news-card" onclick="readNews(3)">
<div class="flex items-start gap-3">
<div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-purple-400">
<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
</svg>
</div>
<div class="flex-1">
<span class="text-xs bg-purple-500/20 text-purple-600 px-2 py-1 rounded">Reconocimiento</span>
<h3 class="font-semibold mt-2 mb-1">Empleado del Mes</h3>
<p class="text-sm text-slate-400 mb-2">Felicitamos a María González por su excepcional desempeño en el área de atención al cliente...</p>
<p class="text-xs text-slate-500">10 de Enero, 2024</p>
</div>
</div>
</div>

<!-- Noticia 4 -->
<div class="news-card" onclick="readNews(4)">
<div class="flex items-start gap-3">
<div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-400">
<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
<rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
</svg>
</div>
<div class="flex-1">
<span class="text-xs bg-orange-500/20 text-orange-600 px-2 py-1 rounded">Capacitación</span>
<h3 class="font-semibold mt-2 mb-1">Nuevo Programa de Formación</h3>
<p class="text-sm text-slate-400 mb-2">Inicia el programa de desarrollo profesional con cursos de liderazgo y tecnología...</p>
<p class="text-xs text-slate-500">8 de Enero, 2024</p>
</div>
</div>
</div>
</div>
</div>

<!-- Próximos Cumpleañeros -->
<div class="glass rounded-2xl p-6 fade-in">
<div class="flex items-center justify-between mb-4">
<h2 class="text-xl font-bold">🎂 Próximos Cumpleañeros</h2>
<button class="btn btn-secondary text-sm">Ver calendario</button>
</div>

<div class="space-y-4">
<!-- Cumpleañero 1 -->
<div class="birthday-card">
<div class="birthday-avatar">
<span>AC</span>
</div>
<div class="flex-1">
<h3 class="font-semibold">Ana Castillo</h3>
<p class="text-sm text-slate-400">Recursos Humanos</p>
<p class="text-xs text-green-600 font-semibold">18 de Enero - ¡En 3 días!</p>
</div>
<button class="btn btn-secondary text-sm" onclick="sendWishes('Ana Castillo')">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
</svg>
Felicitar
</button>
</div>

<!-- Cumpleañero 2 -->
<div class="birthday-card">
<div class="birthday-avatar">
<span>JM</span>
</div>
<div class="flex-1">
<h3 class="font-semibold">José Martínez</h3>
<p class="text-sm text-slate-400">Desarrollo</p>
<p class="text-xs text-blue-400 font-semibold">22 de Enero - En 7 días</p>
</div>
<button class="btn btn-secondary text-sm" onclick="sendWishes('José Martínez')">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
</svg>
Felicitar
</button>
</div>

<!-- Cumpleañero 3 -->
<div class="birthday-card">
<div class="birthday-avatar">
<span>LR</span>
</div>
<div class="flex-1">
<h3 class="font-semibold">Laura Rodríguez</h3>
<p class="text-sm text-slate-400">Marketing</p>
<p class="text-xs text-purple-400 font-semibold">25 de Enero - En 10 días</p>
</div>
<button class="btn btn-secondary text-sm" onclick="sendWishes('Laura Rodríguez')">
<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
</svg>
Felicitar
</button>
</div>
</div>
</div>

<!-- Empleados Activos -->
<div class="glass rounded-2xl p-6 fade-in">
<div class="bg-white p-4 rounded-2xl glass">
<div class="flex items-center justify-between mb-4">
<h2 class="text-xl font-bold">👥 Empleados Activos</h2>
<span class="text-xs bg-green-500/20 text-green-600 px-2 py-1 rounded">247 activos</span>
</div>

<div class="flex items-center justify-between">
<div>
<p class="text-sm text-slate-400">Total de empleados</p>
<p class="text-3xl font-bold text-green-400">247</p>
<p class="text-xs text-green-400">+3 este mes</p>
</div>
<div class="w-16 h-16 bg-green-500/20 rounded-xl flex items-center justify-center">
<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-400">
<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
<circle cx="9" cy="7" r="4"/>
<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
</svg>
</div>
</div>

<div class="mt-4 pt-4 border-t border-white/10">
<div class="grid grid-cols-3 gap-4 text-center">
<div>
<p class="text-slate-400 text-xs sm:text-sm lg:text-base">Presentes</p>
<p class="font-semibold text-green-400 text-sm sm:text-base lg:text-lg">234</p>
</div>
<div>
<p class="text-slate-400 text-xs sm:text-sm lg:text-base">Remotos</p>
<p class="font-semibold text-blue-400 text-sm sm:text-base lg:text-lg">8</p>
</div>
<div>
<p class="text-slate-400 text-xs sm:text-sm lg:text-base">Ausentes</p>
<p class="font-semibold text-orange-400 text-sm sm:text-base lg:text-lg">5</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div> --}}




<div class="container w-full" ><!-- Icono de construcción -->
   <div class="icon-container">
    <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3" /> <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
    </svg>
   </div><!-- Badge de estado -->
   <div class="badge">
    🚧 Módulo en Actualización
   </div><!-- Título principal -->
   <h1>Página en Construcción</h1><!-- Descripción -->
   <p class="subtitle">Este módulo está siendo actualizado para brindarte una mejor experiencia. Estamos trabajando en nuevas funcionalidades que estarán disponibles próximamente.</p><!-- Caja informativa -->
   <div class="info-box">
    <p>
     <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="min-width: 16px; margin-top: 2px;"><circle cx="12" cy="12" r="10" /> <line x1="12" y1="16" x2="12" y2="12" /> <line x1="12" y1="8" x2="12.01" y2="8" />
     </svg><span><strong>¿Qué está pasando?</strong> Estamos realizando mejoras y actualizaciones en este módulo para ofrecerte nuevas funcionalidades.</span></p>
    <p>
     <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="min-width: 16px; margin-top: 2px;"><path d="M12 2v6m0 0l4-4m-4 4L8 4" /> <rect x="4" y="12" width="16" height="10" rx="2" />
     </svg><span>Mientras tanto, puedes acceder a otros módulos del sistema desde el menú principal.</span></p>
   </div>



<!-- Footer con derechos de autor -->
<footer class="px-6 pb-6">
<div class="glass rounded-2xl p-4 text-center">
<p class="text-sm text-slate-500">
© 2024 CODECYT, S.A. - Desarrollado por
<span class="font-semibold text-blue-600">GTIC</span>
(Gerencia de Tecnología de la Información y Comunicación)
</p>
<p class="text-xs text-slate-400 mt-1">
Todos los derechos reservados
</p>
</div>
</footer>
</main>

<!-- Toast -->
<div id="toast" class="fixed bottom-4 right-4 hidden z-50">
<div class="glass rounded-xl px-4 py-3 flex items-center gap-2">
<div id="toastIcon">✅</div>
<div id="toastMsg" class="text-sm"></div>
</div>
</div>

<!-- Tooltip para eventos del calendario -->
<div id="eventTooltip" class="event-tooltip">
<div id="tooltipTitle" class="font-semibold text-sm mb-1"></div>
<div id="tooltipDescription" class="text-xs text-slate-400"></div>
</div>

<script>
// Estado de la aplicación
let currentMonthIndex = 0;
const months = [
'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

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

// Dropdown
function toggleDropdown() {
document.querySelector('.dropdown').classList.toggle('open');
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', (e) => {
if (!e.target.closest('.dropdown')) {
document.querySelector('.dropdown').classList.remove('open');
}
});

// Funciones del calendario
function previousMonth() {
currentMonthIndex = (currentMonthIndex - 1 + 12) % 12;
updateCalendar();
showToast(`Navegando a ${months[currentMonthIndex]}`, 'info');
}

function nextMonth() {
currentMonthIndex = (currentMonthIndex + 1) % 12;
updateCalendar();
showToast(`Navegando a ${months[currentMonthIndex]}`, 'info');
}

function updateCalendar() {
document.getElementById('currentMonth').textContent = `${months[currentMonthIndex]} 2024`;
}

// Funciones de accesos directos
function openSupport() {
showToast('Abriendo sistema de soporte técnico...', 'info');
setTimeout(() => {
showToast('Redirigiendo al portal de tickets...', 'success');
}, 1500);
}

function requestCertificate() {
showToast('Iniciando solicitud de constancia de trabajo...', 'info');
setTimeout(() => {
showToast('Formulario de constancia cargado', 'success');
}, 1500);
}

function requestPayslip() {
showToast('Accediendo a recibos de pago...', 'info');
setTimeout(() => {
showToast('Portal de nómina disponible', 'success');
}, 1500);
}

function requestForm() {
showToast('Cargando formato de requisición...', 'info');
setTimeout(() => {
showToast('Formulario de requisición listo', 'success');
}, 1500);
}

// Funciones de noticias
function readNews(newsId) {
const newsTitle = [
'',
'Nueva Política de Trabajo Remoto',
'Récord de Ventas en Q4',
'Empleado del Mes',
'Nuevo Programa de Formación'
];

showToast(`Leyendo: ${newsTitle[newsId]}...`, 'info');
}

// Funciones de cumpleañeros
function sendWishes(name) {
showToast(`Enviando felicitaciones a ${name}...`, 'info');
setTimeout(() => {
showToast(`¡Felicitaciones enviadas a ${name}!`, 'success');
}, 1500);
}

// Funciones del directorio
function openDirectory() {
showToast('Abriendo directorio completo de empleados...', 'info');
}

function contactEmployee(name) {
showToast(`Iniciando contacto con ${name}...`, 'info');
setTimeout(() => {
showToast(`Chat abierto con ${name}`, 'success');
}, 1000);
}

function callExtension(extension) {
showToast(`Marcando extensión ${extension}...`, 'info');
setTimeout(() => {
showToast(`Conectando con extensión ${extension}`, 'success');
}, 1500);
}

// Funciones de tareas
function openTasks() {
showToast('Abriendo gestor de tareas completo...', 'info');
}

function completeTask(taskId) {
const taskNames = {
1: 'Revisar propuesta cliente ABC',
2: 'Actualizar documentación',
3: 'Preparar presentación Q1'
};

showToast(`Tarea completada: ${taskNames[taskId]}`, 'success');

// Simular actualización visual
setTimeout(() => {
const checkbox = event.target;
if (checkbox.checked) {
checkbox.parentElement.style.opacity = '0.5';
checkbox.parentElement.style.textDecoration = 'line-through';
}
}, 500);
}

// Funciones de documentos
function openDocuments() {
showToast('Abriendo biblioteca de documentos...', 'info');
}

function openDocument(filename) {
showToast(`Abriendo documento: ${filename}...`, 'info');
setTimeout(() => {
showToast(`Documento ${filename} cargado`, 'success');
}, 1000);
}

function logout() {
showToast('Cerrando sesión...', 'info');
setTimeout(() => {
showToast('Sesión cerrada correctamente', 'success');
}, 1000);
}

function login() {
showToast('Redirigiendo al sistema de autenticación...', 'info');
setTimeout(() => {
showToast('Formulario de login cargado', 'success');
}, 1500);
}

// Nuevas funciones para inventarios y servicios
function openConsumables() {
showToast('Abriendo inventario de consumibles...', 'info');
setTimeout(() => {
showToast('Sistema de consumibles disponible', 'success');
}, 1500);
}

function openAssets() {
showToast('Abriendo inventario de bienes...', 'info');
setTimeout(() => {
showToast('Sistema de bienes disponible', 'success');
}, 1500);
}

function openMaterials() {
showToast('Abriendo inventario de materiales...', 'info');
setTimeout(() => {
showToast('Sistema de materiales disponible', 'success');
}, 1500);
}

function openDigitalization() {
showToast('Abriendo sistema de digitalización...', 'info');
setTimeout(() => {
showToast('Portal de digitalización listo', 'success');
}, 1500);
}

function openDocumentCreation() {
showToast('Abriendo creador de documentos...', 'info');
setTimeout(() => {
showToast('Editor de documentos disponible', 'success');
}, 1500);
}

function openConsultancy() {
showToast('Abriendo servicios de consultoría...', 'info');
setTimeout(() => {
showToast('Portal de consultoría disponible', 'success');
}, 1500);
}

function requestVacation() {
showToast('Cargando planilla de solicitud de vacaciones...', 'info');
setTimeout(() => {
showToast('Formulario de vacaciones listo', 'success');
}, 1500);
}

// Funciones del tooltip de eventos
function showEventTooltip(event, title, description) {
const tooltip = document.getElementById('eventTooltip');
const tooltipTitle = document.getElementById('tooltipTitle');
const tooltipDescription = document.getElementById('tooltipDescription');

tooltipTitle.textContent = title;
tooltipDescription.textContent = description;

// Posicionar el tooltip
const rect = event.target.getBoundingClientRect();
const tooltipRect = tooltip.getBoundingClientRect();

let left = rect.left + (rect.width / 2) - (200 / 2); // Centrar horizontalmente
let top = rect.top - tooltipRect.height - 10; // Arriba del elemento

// Ajustar si se sale de la pantalla
if (left < 10) left = 10;
if (left + 200 > window.innerWidth - 10) left = window.innerWidth - 210;
if (top < 10) top = rect.bottom + 10; // Mostrar abajo si no cabe arriba

tooltip.style.left = left + 'px';
tooltip.style.top = top + 'px';
tooltip.classList.add('show');
}

function hideEventTooltip() {
const tooltip = document.getElementById('eventTooltip');
tooltip.classList.remove('show');
}

// Inicialización
function init() {
updateCalendar();
showToast('Dashboard cargado correctamente', 'success');
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', init);
</script>

@if(Auth::check())
<script>
// Menu toggle para móvil
document.getElementById('menuToggle').addEventListener('click', () => {
const sidebar = document.querySelector('.sidebar');
const backdrop = document.getElementById('sidebarBackdrop');
sidebar.classList.toggle('open');
if (sidebar.classList.contains('open')) {
backdrop.classList.remove('hidden');
} else {
backdrop.classList.add('hidden');
}
});

function closeSidebar() {
document.querySelector('.sidebar').classList.remove('open');
document.getElementById('sidebarBackdrop').classList.add('hidden');
}
</script>
@endif

<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'98b67b8ad7bd67e1',t:'MTc1OTkzNTM2MS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script>
</body>
</html>





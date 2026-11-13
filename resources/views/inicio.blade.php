<x-app-layout>
    @section('title', 'Intranet CODECYT')
    @section('subtitle', 'Bienvenido a la intranet de CODECYT')
    <!-- SweetAlert2 se carga desde el layout principal -->
    <!-- CDN eliminado para evitar doble carga -->


    <style>
        .quick-access-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 1rem;
            padding: 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .quick-access-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(30, 58, 138, 0.15);
            border-color: rgba(30, 58, 138, 0.3);
        }
        .quick-access-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
        }
        .quick-access-icon svg { width: 24px; height: 24px; color: white; }

        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; margin-top: 1rem; }
        .calendar-header { text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--muted); padding: 0.5rem; }
        .calendar-day { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; border-radius: 0.5rem; cursor: default; transition: all 0.2s ease; position: relative; }
        .calendar-day:not(.other-month):hover { background: rgba(30, 58, 138, 0.1); }
        .calendar-day.today { background: #1e3a8a; color: white; font-weight: 600; }
        .calendar-day.has-birthday {
            position: relative;
            cursor: pointer;
            font-weight: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            line-height: 1.1;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .calendar-day.has-birthday:hover {
            transform: scale(1.12);
            z-index: 25;
        }
        .calendar-day.has-birthday.is-today-birthday {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.45);
        }
        .calendar-day.has-birthday.is-urgent-birthday {
            background: linear-gradient(135deg, #f59e0b, #ea580c) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }
        .calendar-day.has-birthday.is-near-birthday {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.35);
        }
        .calendar-day.has-birthday.is-other-birthday {
            background: linear-gradient(135deg, #8b5cf6, #6366f1) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
        }
        .calendar-day.other-month { color: var(--muted); opacity: 0.5; }

        /* Event calendar styles */
        .calendar-day.has-event { font-weight: 600; cursor: pointer; }
        .calendar-day.has-event:hover { transform: scale(1.05); }
        .calendar-day.today.has-event { color: white; }

        .event-tooltip {
            position: fixed;
            background: white;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 0.85rem;
            padding: 0.85rem;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.22);
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, transform 0.2s ease;
            max-width: 320px;
            pointer-events: none;
        }
        .event-tooltip.show { opacity: 1; visibility: visible; transform: translateY(-4px); }

        .news-card { background: rgba(255, 255, 255, 0.5); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1rem; cursor: pointer; transition: all 0.2s ease; }
        .news-card:hover { background: rgba(255, 255, 255, 0.8); transform: translateX(4px); }

        /* Accordion styles for news con animación suave */
        .news-accordion { display: none; }
        .news-accordion.show { display: block; animation: modalBackdropFadeIn 300ms cubic-bezier(0.4, 0.0, 0.2, 1); }
        .news-accordion-item { border-radius: 1rem; transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); }
        .news-accordion-header {
            cursor: pointer;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            border-radius: 1rem;
        }
        .news-accordion-header:hover { background: rgba(255, 255, 255, 0.4); }
        .news-accordion-content {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 350ms cubic-bezier(0.4, 0.0, 0.2, 1), opacity 300ms cubic-bezier(0.4, 0.0, 0.2, 1);
            opacity: 0;
            overflow: hidden;
        }
        .news-accordion-content.open,
        .news-accordion-content.grid-rows-\[1fr\] {
            grid-template-rows: 1fr;
            opacity: 1;
        }
        .news-accordion-content > div {
            min-height: 0;
            overflow: hidden;
            transition: transform 320ms cubic-bezier(0.4, 0.0, 0.2, 1);
            transform: translateY(-6px);
        }
        .news-accordion-content.open > div,
        .news-accordion-content.grid-rows-\[1fr\] > div {
            transform: translateY(0);
        }
        .news-grid-view { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .news-grid-view.hidden { display: none; }

        .birthday-card { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.5); border: 1px solid var(--card-border); border-radius: 1rem; transition: all 0.2s ease; }
        .birthday-card:hover { background: rgba(255, 255, 255, 0.8); transform: translateX(4px); }
        .birthday-card.is-today { border-color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
        .birthday-avatar { width: 48px; height: 48px; background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 0.875rem; }

        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: none !important; align-items: center; justify-content: center; z-index: 1000; }
        .modal-overlay.show { display: flex !important; animation: modalBackdropFadeIn 320ms cubic-bezier(0.4, 0, 0.2, 1) forwards !important; }
        .modal-content { background: #ffffff; border-radius: 1.25rem; padding: 1.75rem; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35); }
        .modal-overlay.show .modal-content { animation: modalExpandIn 350ms cubic-bezier(0.4, 0.0, 0.2, 1) forwards !important; transform-origin: center center; }

        @media (max-width: 640px) {
            .quick-access-card { padding: 1rem; }
            .quick-access-icon { width: 40px; height: 40px; }
            .quick-access-icon svg { width: 20px; height: 20px; }
        }
    </style>

    <main class="@if(Auth::check())  @endif " id="mainContent">
        <div class="px-6 pb-8 ">
            <div class="glass rounded-2xl p-6 mb-6 fade-in">
                <h2 class="text-xl font-bold mb-6">⚡ Accesos Directos</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div class="quick-access-card" onclick="openSupport()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></div>
                        <h3 class="font-semibold text-sm">Sistema de Soporte</h3><p class="text-xs text-slate-400 mt-1">Reportar incidencias</p>
                    </div>
                    <div class="quick-access-card" onclick="requestCertificate()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></div>
                        <h3 class="font-semibold text-sm">Constancia de Trabajo</h3><p class="text-xs text-slate-400 mt-1">Solicitar documento</p>
                    </div>
                    <div class="quick-access-card" onclick="requestPayslip()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                        <h3 class="font-semibold text-sm">Recibo de Pago</h3><p class="text-xs text-slate-400 mt-1">Descargar comprobante</p>
                    </div>
                    <div class="quick-access-card" onclick="requestForm()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/></svg></div>
                        <h3 class="font-semibold text-sm">Formato de Requisición</h3><p class="text-xs text-slate-400 mt-1">Solicitar materiales</p>
                    </div>
                    <div class="quick-access-card" onclick="requestVacation()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                        <h3 class="font-semibold text-sm">Vacaciones</h3><p class="text-xs text-slate-400 mt-1">Solicitar vacaciones</p>
                    </div>
                     @can('oga')
                     @can('inv-consumibles')
                    <div class="quick-access-card" onclick="openConsumables()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.35 2.65"/></svg></div>
                        <h3 class="font-semibold text-sm">Consumibles</h3><p class="text-xs text-slate-400 mt-1">Gestionar consumibles</p>
                    </div>
                    @endcan
                    @can('bienes')
                    <div class="quick-access-card" onclick="openAssets()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div>
                        <h3 class="font-semibold text-sm">Inventario Bienes</h3><p class="text-xs text-slate-400 mt-1">Gestionar bienes</p>
                    </div>
                    @endcan
                    @can('inv-oficina')
                    <div class="quick-access-card" onclick="openMaterials()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
                        <h3 class="font-semibold text-sm">Materiales</h3><p class="text-xs text-slate-400 mt-1">Gestionar materiales</p>
                    </div>
                    @endcan
                    @can('documentos')
                    <div class="quick-access-card" onclick="openDigitalization()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg></div>
                        <h3 class="font-semibold text-sm">Digitalización</h3><p class="text-xs text-slate-400 mt-1">Digitalizar archivos</p>
                    </div>
                    @endcan
                    {{-- <div class="quick-access-card" onclick="openConsultancy()">
                        <div class="quick-access-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0-6 0"/><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/></svg></div>
                        <h3 class="font-semibold text-sm">Consultoría</h3><p class="text-xs text-slate-400 mt-1">Servicios consultoría</p>
                    </div> --}}
                    @endcan
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass rounded-2xl p-6 fade-in">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold">📅 Eventos</h2>
                            <div class="flex items-center gap-2">
                                <button onclick="openAddEventModal()" class="btn ml-2 btn-primary hover:bg-blue-700 text-sm px-3 py-1.5">+ </button>
                                <button onclick="previousEventMonth()" class="btn btn-secondary text-sm p-2">◀</button>
                                <span id="eventCurrentMonth" class="text-sm font-semibold px-3">Enero 2024</span>
                                <button onclick="nextEventMonth()" class="btn btn-secondary text-sm p-2">▶</button>
                            </div>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-header">Dom</div><div class="calendar-header">Lun</div><div class="calendar-header">Mar</div><div class="calendar-header">Mié</div><div class="calendar-header">Jue</div><div class="calendar-header">Vie</div><div class="calendar-header">Sáb</div>
                        </div>
                        <div id="eventCalendarDays" class="calendar-grid">
                            <div class="col-span-7 text-center py-8 text-slate-400">
                                <div class="animate-spin inline-block w-6 h-6 border-3 border-blue-500 border-t-transparent rounded-full"></div>
                                <p class="text-sm mt-2">Cargando eventos...</p>
                            </div>
                        </div>
                        <div id="eventList" class="mt-4 space-y-2">
                            <div class="text-center py-4 text-slate-400 text-sm">No hay eventos para este mes</div>
                        </div>
                    </div>

                    <div class="glass rounded-2xl p-6 fade-in">
                        <div class="block items-center justify-between mb-4">
                            <h2 class="text-xl font-bold flex">📞 Directorio</h2>
                            <div class="flex items-center gap-2 justify-center">
                                @if(Auth::check() && Auth::user()->hasRole('Admin'))
                                <button class="btn bg-blue-900 hover:bg-blue-700 text-sm text-white flex" onclick="addDirectoryItem()">+ Agregar</button>
                                @endif
                                <button class="btn btn-secondary text-sm" onclick="openDirectoryModal()">Ver todas</button>
                            </div>
                        </div>
                        <div id="directoryList" class="space-y-3">
                            <div class="text-center py-4 text-slate-400">
                                <div class="animate-spin inline-block w-6 h-6 border-3 border-blue-500 border-t-transparent rounded-full"></div>
                                <p class="text-sm mt-2">Cargando directorio...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="glass rounded-2xl p-6 fade-in">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold">📰 Últimas Noticias</h2>
                            <div class="flex items-center gap-2">
                                @php
                                    $user = Auth::user();
                                    $canAddNews = $user && (
                                        $user->hasRole('admin') ||
                                        $user->hasRole('Admin') ||
                                        $user->hasRole('gtic') ||
                                        $user->hasRole('GTIC') ||
                                        $user->hasRole('gerente') ||
                                        $user->hasRole('Gerente') ||
                                        $user->hasRole('coordinacion') ||
                                        $user->hasRole('Coordinacion')
                                    );
                                @endphp
                                @if($canAddNews)
                                <button onclick="openNewsModal()" class="btn btn-primary hover:bg-blue-700 text-sm">+ Agregar</button>
                                @endif
                                <button onclick="openAllNews()" class="btn btn-secondary text-sm">Ver todas</button>
                            </div>
                        </div>
                        <div id="newsGridView" class="news-grid-view">
                            <!-- News cards will be loaded here by JavaScript -->
                            <div class="col-span-2 text-center py-8 text-slate-400">
                                <div class="animate-spin inline-block w-6 h-6 border-3 border-blue-500 border-t-transparent rounded-full"></div>
                                <p class="text-sm mt-2">Cargando noticias...</p>
                            </div>
                        </div>

                        <!-- Accordion view for all news -->
                        <div id="newsAccordion" class="news-accordion mt-4">
                            <!-- Accordion content will be loaded here -->
                        </div>
                    </div>

                    <div class="glass rounded-2xl p-6 fade-in">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold">🎂 Próximos Cumpleañeros</h2>
                            <button class="btn btn-secondary text-sm" onclick="openBirthdayCalendar()">Ver calendario</button>
                        </div>
                        <div id="birthdaysList" class="space-y-4">
                            <div class="text-center py-8 text-slate-400">
                                <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mb-2"></div>
                                <p>Cargando cumpleaños...</p>
                            </div>
                        </div>
                        <div id="noBirthdays" class="hidden text-center py-8 text-slate-400">
                            <div class="text-4xl mb-2">🎂</div>
                            <p>No hay cumpleaños este mes</p>
                        </div>
                    </div>

                    <div class="glass rounded-2xl p-6 fade-in">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold">👥 Empleados Activos</h2>
                            <span class="text-xs bg-green-500/20 text-green-600 px-2 py-1 rounded" id="userStatsBadge">Cargando...</span>
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <div>
                                <p class="text-sm text-slate-400">Total de empleados</p>
                                <p class="text-3xl font-bold text-green-400" id="totalUsers">...</p>
                                <p class="text-xs text-green-400" id="newUsersThisMonth">+0 este mes</p>
                            </div>
                            <div class="w-16 h-16 bg-green-500/20 rounded-xl flex items-center justify-center text-3xl">👥</div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/10">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div><p class="text-slate-400 text-sm">Registrados</p><p class="font-semibold text-blue-400 text-lg" id="totalRegistered">...</p></div>
                                <div><p class="text-slate-400 text-sm">Activos</p><p class="font-semibold text-green-400 text-lg" id="activeUsers">...</p></div>
                                <div><p class="text-slate-400 text-sm">Inactivos</p><p class="font-semibold text-red-400 text-lg" id="inactiveUsers">...</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="px-6 pb-6">
            <div class="glass rounded-2xl p-4 text-center">
                <p class="text-sm text-slate-500">© 2024 CODECYT, S.A. - Desarrollado por <span class="font-semibold text-blue-600">GTIC</span></p>
                <p class="text-xs text-slate-400 mt-1">Todos los derechos reservados</p>
            </div>
        </footer>
    </main>

    <div id="eventTooltip" class="event-tooltip">
        <div id="tooltipTitle" class="font-semibold text-sm mb-1"></div>
        <div id="tooltipDescription" class="text-xs text-slate-400"></div>
    </div>

    <!-- Birthday Calendar Modal -->
    <div id="birthdayModal" class="modal-overlay" style="display: none;" onclick="closeBirthdayModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">🎂 Calendario de Cumpleaños</h2>
                <button onclick="closeBirthdayModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <div class="flex items-center justify-between mb-4">
                <button onclick="previousBirthdayMonth()" class="btn btn-secondary text-sm p-2">◀</button>
                <span id="birthdayMonthYear" class="text-lg font-semibold">Enero 2024</span>
                <button onclick="nextBirthdayMonth()" class="btn btn-secondary text-sm p-2">▶</button>
            </div>
            <div class="calendar-grid">
                <div class="calendar-header">Dom</div>
                <div class="calendar-header">Lun</div>
                <div class="calendar-header">Mar</div>
                <div class="calendar-header">Mié</div>
                <div class="calendar-header">Jue</div>
                <div class="calendar-header">Vie</div>
                <div class="calendar-header">Sáb</div>
            </div>
            <div id="birthdayCalendarDays" class="calendar-grid"></div>
            <div id="birthdayModalList" class="mt-4 space-y-2 max-h-48 overflow-y-auto"></div>
        </div>
    </div>

    <!-- Birthday Tooltip -->
    <div id="birthdayTooltip" class="event-tooltip">
        <div id="birthdayTooltipNames" class="font-semibold text-sm"></div>
        <div id="birthdayTooltipDept" class="text-xs text-slate-400 mt-1"></div>
    </div>

    <!-- News Modal (Add News) -->
    <div id="newsModal" class="modal-overlay" style="display: none;" onclick="closeNewsModal(event)">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">📰 Agregar Noticia</h2>
                <button onclick="closeNewsModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <form id="newsForm" onsubmit="saveNews(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título *</label>
                        <input type="text" id="newsTitle" class="w-full p-2 border rounded-lg bg-slate-50 " placeholder="Título de la noticia" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Categoría *</label>
                        <select id="newsCategory" class="w-full p-2 border rounded-lg bg-slate-50 " required>
                            <option value="">Seleccionar categoría</option>
                            <option value="Corporativo">Corporativo</option>
                            <option value="Logros">Logros</option>
                            <option value="Reconocimiento">Reconocimiento</option>
                            <option value="Capacitación">Capacitación</option>
                            <option value="Evento">Evento</option>
                            <option value="Aviso">Aviso</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contenido *</label>
                        <textarea id="newsContent" class="w-full p-2 border rounded-lg bg-slate-50 " rows="4" placeholder="Contenido de la noticia..." required></textarea>
                    </div>
                    <div>
                         <label class="block text-sm font-medium mb-1">Publicar?</label>
                         <select name="publicada" id="newsPublicada" class="w-full p-2 border rounded-lg bg-slate-50">
                             <option value="1">Sí</option>
                             <option value="0">No</option>
                             </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Imagen</label>
                        <input type="file" id="newsImage" class="w-full p-2 border rounded-lg bg-slate-50 " accept="image/*">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeNewsModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- News Details Modal -->
    <div id="newsDetailsModal" class="modal-overlay" style="display: none;" onclick="closeNewsDetailsModal(event)">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 id="newsDetailsTitle" class="text-xl font-bold">📰 Noticia</h2>
                <button onclick="closeNewsDetailsModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <div id="newsDetailsContent">
                <!-- News details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Directory Modal (Ver todas) -->
    <div id="directoryModal" class="modal-overlay" style="display: none;" onclick="closeDirectoryModal(event)">
        <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">📞 Directorio Completo</h2>
                <button onclick="closeDirectoryModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <div id="directoryModalList" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Directory items will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Directory Edit/Add Modal (Admin only) -->
    <div id="directoryEditModal" class="modal-overlay" style="display: none;" onclick="closeDirectoryEditModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 id="directoryEditTitle" class="text-xl font-bold">📞 Editar Contacto</h2>
                <button onclick="closeDirectoryEditModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <form id="directoryEditForm" onsubmit="saveDirectoryItem(event)">
                <input type="hidden" id="directoryEditId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre</label>
                        <input type="text" id="directoryEditNombre" class="w-full p-2 border rounded-lg bg-slate-50 " required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Extensión</label>
                        <input type="text" id="directoryEditExtension" class="w-full p-2 border rounded-lg bg-slate-50 " required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Icono (emoji)</label>
                        <input type="text" id="directoryEditIcono" class="w-full p-2 border rounded-lg bg-slate-50 " placeholder="📞">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeDirectoryEditModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="directoryDeleteModal" class="modal-overlay" style="display: none;" onclick="closeDirectoryDeleteModal(event)">
        <div class="modal-content" style="max-width: 400px;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">⚠️ Confirmar Eliminación</h2>
                <button onclick="closeDirectoryDeleteModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <p class="text-slate-400 mb-6">¿Estás seguro de que deseas eliminar este contacto del directorio?</p>
            <input type="hidden" id="directoryDeleteId">
            <div class="flex justify-end gap-2">
                <button onclick="closeDirectoryDeleteModal()" class="btn btn-secondary">Cancelar</button>
                <button onclick="confirmDeleteDirectory()" class="btn bg-red-500 hover:bg-red-600 text-white">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Event Modal (Add/Edit) -->
    <div id="eventModal" class="modal-overlay" style="display: none;" onclick="closeEventModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 id="eventModalTitle" class="text-xl font-bold">📅 Agregar Evento</h2>
                <button onclick="closeEventModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <form id="eventForm" onsubmit="saveEvent(event)">
                <input type="hidden" id="eventId">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título del Evento *</label>
                        <input type="text" id="eventTitle" class="w-full p-2 border rounded-lg bg-slate-50 " placeholder="Ej: Reunión de equipo" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea id="eventDescription" class="w-full p-2 border rounded-lg bg-slate-50 " rows="3" placeholder="Detalles del evento..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Fecha/Hora Inicio *</label>
                            <input type="datetime-local" id="eventStart" class="w-full p-2 border rounded-lg bg-slate-50 " required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Fecha/Hora Fin</label>
                            <input type="datetime-local" id="eventEnd" class="w-full p-2 border rounded-lg bg-slate-50 ">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Color</label>
                        <div class="flex gap-2">
                            <input type="radio" name="eventColor" value="#3b82f6" id="colorBlue" class="hidden peer/colorBlue" checked>
                            <label for="colorBlue" class="w-8 h-8 rounded-full bg-blue-500 cursor-pointer ring-2 ring-transparent peer-checked/colorBlue:ring-white transition-all" onclick="selectEventColor('#3b82f6')"></label>

                            <input type="radio" name="eventColor" value="#10b981" id="colorGreen" class="hidden peer/colorGreen">
                            <label for="colorGreen" class="w-8 h-8 rounded-full bg-green-500 cursor-pointer ring-2 ring-transparent peer-checked/colorGreen:ring-white transition-all" onclick="selectEventColor('#10b981')"></label>

                            <input type="radio" name="eventColor" value="#f59e0b" id="colorAmber" class="hidden peer/colorAmber">
                            <label for="colorAmber" class="w-8 h-8 rounded-full bg-amber-500 cursor-pointer ring-2 ring-transparent peer-checked/colorAmber:ring-white transition-all" onclick="selectEventColor('#f59e0b')"></label>

                            <input type="radio" name="eventColor" value="#ef4444" id="colorRed" class="hidden peer/colorRed">
                            <label for="colorRed" class="w-8 h-8 rounded-full bg-red-500 cursor-pointer ring-2 ring-transparent peer-checked/colorRed:ring-white transition-all" onclick="selectEventColor('#ef4444')"></label>

                            <input type="radio" name="eventColor" value="#8b5cf6" id="colorPurple" class="hidden peer/colorPurple">
                            <label for="colorPurple" class="w-8 h-8 rounded-full bg-purple-500 cursor-pointer ring-2 ring-transparent peer-checked/colorPurple:ring-white transition-all" onclick="selectEventColor('#8b5cf6')"></label>

                            <input type="radio" name="eventColor" value="#ec4899" id="colorPink" class="hidden peer/colorPink">
                            <label for="colorPink" class="w-8 h-8 rounded-full bg-pink-500 cursor-pointer ring-2 ring-transparent peer-checked/colorPink:ring-white transition-all" onclick="selectEventColor('#ec4899')"></label>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="eventAllDay" class="w-4 h-4 rounded">
                        <label for="eventAllDay" class="text-sm">Evento de todo el día</label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="closeEventModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div id="eventDetailsModal" class="modal-overlay" style="display: none;" onclick="closeEventDetailsModal(event)">
        <div class="modal-content" style="max-width: 450px;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">📅 Detalles del Evento</h2>
                <button onclick="closeEventDetailsModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <div id="eventDetailsContent">
                <!-- Event details will be loaded here -->
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button onclick="closeEventDetailsModal()" class="btn btn-secondary">Cerrar</button>
                <button id="editEventBtn" onclick="editCurrentEvent()" class="btn btn-secondary">✏️ Editar</button>
                <button id="deleteEventBtn" onclick="deleteCurrentEvent()" class="btn bg-red-500 hover:bg-red-600 text-white">🗑️ Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Event Delete Confirmation Modal -->
    <div id="eventDeleteModal" class="modal-overlay" style="display: none;" onclick="closeEventDeleteModal(event)">
        <div class="modal-content" style="max-width: 400px;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">⚠️ Confirmar Eliminación</h2>
                <button onclick="closeEventDeleteModal()" class="modal-close-btn" title="Cerrar">&times;</button>
            </div>
            <p class="text-slate-400 mb-6">¿Estás seguro de que deseas eliminar este evento?</p>
            <input type="hidden" id="eventDeleteId">
            <div class="flex justify-end gap-2">
                <button onclick="closeEventDeleteModal()" class="btn btn-secondary">Cancelar</button>
                <button onclick="confirmDeleteEvent()" class="btn bg-red-500 hover:bg-red-600 text-white">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let currentMonthIndex = 0;
        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        // Birthday data
        let birthdayData = null;
        let birthdayCalendarMonth = new Date().getMonth();
        let birthdayCalendarYear = new Date().getFullYear();

        // Event Calendar data
        let eventCalendarMonth = new Date().getMonth();
        let eventCalendarYear = new Date().getFullYear();
        let eventsData = [];
        let selectedEvent = null;
        let selectedEventColor = '#3b82f6';

        function showToast(msg, type = 'success') {
            if (typeof Swal !== 'undefined' && (type === 'error' || type === 'warning')) {
                Swal.fire({
                    icon: type,
                    title: type === 'error' ? 'Error' : 'Atención',
                    html: msg,
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }
            if (typeof window.showAppToast === 'function') {
                window.showAppToast(msg, type);
                return;
            }
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');
            if (!toast || !msgEl) return;
            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // ==================== Event Calendar Functions ====================

        function loadEvents() {
            fetch(`/api/events?month=${eventCalendarMonth + 1}&year=${eventCalendarYear}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        eventsData = data.data;
                        renderEventCalendar();
                        renderEventList();
                    } else {
                        document.getElementById('eventCalendarDays').innerHTML = `
                            <div class="col-span-7 text-center py-8 text-slate-400">
                                <p class="text-sm">Error al cargar eventos</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    document.getElementById('eventCalendarDays').innerHTML = `
                        <div class="col-span-7 text-center py-8 text-slate-400">
                            <p class="text-sm">Error al cargar eventos</p>
                        </div>
                    `;
                });
        }

        function renderEventCalendar() {
            document.getElementById('eventCurrentMonth').textContent = months[eventCalendarMonth] + ' ' + eventCalendarYear;

            const firstDay = new Date(eventCalendarYear, eventCalendarMonth, 1).getDay();
            const daysInMonth = new Date(eventCalendarYear, eventCalendarMonth + 1, 0).getDate();
            const today = new Date();

            let html = '';

            // Empty cells for days before the first day
            for (let i = 0; i < firstDay; i++) {
                const prevMonth = eventCalendarMonth === 0 ? 11 : eventCalendarMonth - 1;
                const prevYear = eventCalendarMonth === 0 ? eventCalendarYear - 1 : eventCalendarYear;
                const prevDays = new Date(prevYear, prevMonth + 1, 0).getDate();
                const day = prevDays - firstDay + i + 1;
                html += `<div class="calendar-day other-month">${day}</div>`;
            }

            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = today.getDate() === day && today.getMonth() === eventCalendarMonth && today.getFullYear() === eventCalendarYear;

                // Check for events on this day
                const dateStr = `${eventCalendarYear}-${String(eventCalendarMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const eventsOnDay = eventsData.filter(e => e.start.startsWith(dateStr));

                let classes = 'calendar-day';
                if (isToday) classes += ' today';
                if (eventsOnDay.length > 0) classes += ' has-event';

                let dayContent = day;
                if (eventsOnDay.length > 0) {
                    // Paint the day with event color (light background)
                    const eventColor = eventsOnDay[0].color;
                    dayContent = `<div class="relative w-full h-full flex items-center justify-center" style="background-color: ${eventColor}20; border-radius: 0.5rem; width: calc(100% - 4px); height: calc(100% - 4px);">
                        ${day}
                        <span class="absolute bottom-1 w-1.5 h-1.5 rounded-full" style="background-color: ${eventColor}"></span>
                    </div>`;
                }

                html += `<div class="${classes}" onclick="showDayEvents('${dateStr}')">${dayContent}</div>`;
            }

            // Fill remaining cells
            const totalCells = firstDay + daysInMonth;
            const remainingCells = 42 - totalCells;
            for (let day = 1; day <= remainingCells; day++) {
                html += `<div class="calendar-day other-month">${day}</div>`;
            }

            document.getElementById('eventCalendarDays').innerHTML = html;
        }

        function renderEventList() {
            const container = document.getElementById('eventList');

            // Get current month's events
            const currentMonthEvents = eventsData.filter(e => {
                const eventDate = new Date(e.start);
                return eventDate.getMonth() === eventCalendarMonth && eventDate.getFullYear() === eventCalendarYear;
            }).sort((a, b) => new Date(a.start) - new Date(b.start));

            if (currentMonthEvents.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-slate-400 text-sm">No hay eventos para este mes</div>';
                return;
            }

            container.innerHTML = currentMonthEvents.slice(0, 4).map(event => {
                const startDate = new Date(event.start);
                const day = startDate.getDate();
                const monthName = months[startDate.getMonth()];
                const time = event.allDay ? 'Todo el día' : startDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });

                return `
                    <div class="flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-white/5 transition-colors" onclick="viewEvent(${event.id})" style="border-left: 3px solid ${event.color}">
                        <div class="flex-1">
                            <p class="text-sm font-semibold">${event.title}</p>
                            <p class="text-xs text-slate-400">${day} de ${monthName} - ${time}</p>
                        </div>
                    </div>
                `;
            }).join('');

            if (currentMonthEvents.length > 4) {
                container.innerHTML += `
                    <div class="text-center py-2">
                        <p class="text-xs text-slate-400">+ ${currentMonthEvents.length - 4} eventos más este mes</p>
                    </div>
                `;
            }
        }

        function previousEventMonth() {
            eventCalendarMonth--;
            if (eventCalendarMonth < 0) {
                eventCalendarMonth = 11;
                eventCalendarYear--;
            }
            loadEvents();
        }

        function nextEventMonth() {
            eventCalendarMonth++;
            if (eventCalendarMonth > 11) {
                eventCalendarMonth = 0;
                eventCalendarYear++;
            }
            loadEvents();
        }

        function showDayEvents(dateStr) {
            const eventsOnDay = eventsData.filter(e => e.start.startsWith(dateStr));

            if (eventsOnDay.length === 0) {
                // No events on this day, open add event modal with this date
                openAddEventModal(dateStr);
            } else if (eventsOnDay.length === 1) {
                // Single event, show details
                viewEvent(eventsOnDay[0].id);
            } else {
                // Multiple events, show list
                const date = new Date(dateStr);
                const dateFormatted = date.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

                let content = `<div class="space-y-3">
                    <p class="text-sm text-slate-400 mb-3">${dateFormatted.charAt(0).toUpperCase() + dateFormatted.slice(1)}</p>`;

                content += eventsOnDay.map(event => {
                    const startDate = new Date(event.start);
                    const endDate = new Date(event.end);
                    const time = event.allDay ? 'Todo el día' : `${startDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })} - ${endDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}`;

                    return `
                        <div class="p-3 rounded-lg bg-white/5 border-l-2" style="border-left-color: ${event.color}">
                            <p class="font-semibold">${event.title}</p>
                            <p class="text-sm text-slate-400">${time}</p>
                            ${event.description ? `<p class="text-xs text-slate-500 mt-1">${event.description}</p>` : ''}
                        </div>
                    `;
                }).join('');

                content += '</div>';

                document.getElementById('eventDetailsContent').innerHTML = content;
                document.getElementById('eventDeleteId').value = eventsOnDay[0].id;
                selectedEvent = eventsOnDay[0];
                const edm = document.getElementById('eventDetailsModal');
                if (edm) { edm.style.display = 'flex'; edm.classList.add('show'); }
            }
        }

        function viewEvent(eventId) {
            const event = eventsData.find(e => e.id === eventId);
            if (!event) return;

            selectedEvent = event;
            const startDate = new Date(event.start);
            const endDate = new Date(event.end);

            const dateFormatted = startDate.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            const timeRange = event.allDay ? 'Todo el día' : `${startDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })} - ${endDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}`;

            document.getElementById('eventDetailsContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" style="background-color: ${event.color}"></div>
                        <p class="text-sm font-semibold text-slate-400">Evento</p>
                    </div>
                    <h3 class="text-xl font-bold">${event.title}</h3>
                    <div class="p-3 rounded-lg bg-white/5">
                        <p class="text-sm text-slate-400">📅 ${dateFormatted.charAt(0).toUpperCase() + dateFormatted.slice(1)}</p>
                        <p class="text-sm text-slate-400">🕐 ${timeRange}</p>
                    </div>
                    ${event.description ? `
                        <div class="p-3 rounded-lg bg-white/5">
                            <p class="text-sm font-semibold mb-1">Descripción</p>
                            <p class="text-sm text-slate-400">${event.description}</p>
                        </div>
                    ` : ''}
                </div>
            `;

            document.getElementById('eventDeleteId').value = event.id;
            const edm = document.getElementById('eventDetailsModal');
            if (edm) { edm.style.display = 'flex'; edm.classList.add('show'); }
        }

        function openAddEventModal(date = null) {
            document.getElementById('eventModalTitle').textContent = '📅 Agregar Evento';
            document.getElementById('eventId').value = '';
            document.getElementById('eventTitle').value = '';
            document.getElementById('eventDescription').value = '';
            document.getElementById('eventAllDay').checked = false;

            // Set default date/time
            const now = new Date();
            if (date) {
                const selectedDate = new Date(date + 'T08:00');
                document.getElementById('eventStart').value = selectedDate.toISOString().slice(0, 16);
            } else {
                document.getElementById('eventStart').value = now.toISOString().slice(0, 16);
            }

            const endDate = new Date(now.getTime() + 60 * 60 * 1000); // 1 hour later
            document.getElementById('eventEnd').value = endDate.toISOString().slice(0, 16);

            // Reset color selection
            selectedEventColor = '#3b82f6';
            document.getElementById('colorBlue').checked = true;

            const em = document.getElementById('eventModal');
            if (em) { em.style.display = 'flex'; em.classList.add('show'); }
        }

        function editCurrentEvent() {
            if (!selectedEvent) return;

            closeEventDetailsModal();

            document.getElementById('eventModalTitle').textContent = '📅 Editar Evento';
            document.getElementById('eventId').value = selectedEvent.id;
            document.getElementById('eventTitle').value = selectedEvent.title;
            document.getElementById('eventDescription').value = selectedEvent.description || '';
            document.getElementById('eventAllDay').checked = selectedEvent.allDay || false;

            const startDate = new Date(selectedEvent.start);
            const endDate = new Date(selectedEvent.end);

            document.getElementById('eventStart').value = startDate.toISOString().slice(0, 16);
            document.getElementById('eventEnd').value = endDate.toISOString().slice(0, 16);

            // Set color
            selectedEventColor = selectedEvent.color;
            const colorMap = {
                '#3b82f6': 'colorBlue',
                '#10b981': 'colorGreen',
                '#f59e0b': 'colorAmber',
                '#ef4444': 'colorRed',
                '#8b5cf6': 'colorPurple',
                '#ec4899': 'colorPink'
            };

            if (colorMap[selectedEvent.color]) {
                document.getElementById(colorMap[selectedEvent.color]).checked = true;
            }

            const em = document.getElementById('eventModal');
            if (em) { em.style.display = 'flex'; em.classList.add('show'); }
        }

        function selectEventColor(color) {
            selectedEventColor = color;
        }

        function closeEventModal(event) {
            if (!event || event.target === event.currentTarget) {
                const em = document.getElementById('eventModal');
                if (em) {
                    em.classList.remove('show');
                    em.style.display = 'none';
                }
            }
        }

        function saveEvent(event) {
            event.preventDefault();

            const id = document.getElementById('eventId').value;
            const title = document.getElementById('eventTitle').value;
            const descripcion = document.getElementById('eventDescription').value;
            const start = document.getElementById('eventStart').value;
            const end = document.getElementById('eventEnd').value;
            const all_day = document.getElementById('eventAllDay').checked;

            if (!title.trim()) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Campo obligatorio', text: 'El título del evento es obligatorio.', confirmButtonColor: '#2563eb' });
                } else {
                    showToast('El título del evento es obligatorio', 'warning');
                }
                return;
            }

            const url = id ? `/api/events/${id}` : '/api/events';
            const method = id ? 'PUT' : 'POST';
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title, descripcion, start, end, color: selectedEventColor, all_day })
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    let errMsg = data.message || 'Error al guardar el evento';
                    if (data.errors) {
                        errMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    throw new Error(errMsg);
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Evento Guardado!',
                            text: data.message || 'El evento ha sido registrado exitosamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        showToast(data.message, 'success');
                    }
                    closeEventModal();
                    loadEvents();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error', html: data.message || 'Error al guardar el evento' });
                    } else {
                        showToast(data.message || 'Error al guardar', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al registrar evento',
                        html: error.message || 'No se pudo guardar el evento. Intente nuevamente.'
                    });
                } else {
                    showToast(error.message || 'Error al guardar', 'error');
                }
            });
        }

        function deleteCurrentEvent() {
            if (!selectedEvent) return;
            document.getElementById('eventDeleteId').value = selectedEvent.id;
            const edm = document.getElementById('eventDetailsModal');
            if (edm) {
                edm.classList.remove('show');
                edm.style.display = 'none';
            }
            const delModal = document.getElementById('eventDeleteModal');
            if (delModal) {
                delModal.style.display = 'flex';
                delModal.classList.add('show');
            }
        }

        function closeEventDetailsModal(event) {
            if (!event || event.target === event.currentTarget) {
                const edm = document.getElementById('eventDetailsModal');
                if (edm) {
                    edm.classList.remove('show');
                    edm.style.display = 'none';
                }
                selectedEvent = null;
            }
        }

        function closeEventDeleteModal(event) {
            if (!event || event.target === event.currentTarget) {
                const delModal = document.getElementById('eventDeleteModal');
                if (delModal) {
                    delModal.classList.remove('show');
                    delModal.style.display = 'none';
                }
            }
        }

        function confirmDeleteEvent() {
            const id = document.getElementById('eventDeleteId').value;

            fetch(`/api/events/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeEventDeleteModal();
                    loadEvents();
                } else {
                    showToast(data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al eliminar', 'error');
            });
        }

        function previousMonth() {
            currentMonthIndex = (currentMonthIndex - 1 + 12) % 12;
            updateCalendar();
            showToast(months[currentMonthIndex], 'info');
        }

        function nextMonth() {
            currentMonthIndex = (currentMonthIndex + 1) % 12;
            updateCalendar();
            showToast(months[currentMonthIndex], 'info');
        }

        function updateCalendar() {
            document.getElementById('currentMonth').textContent = months[currentMonthIndex] + ' 2024';
        }

        function openSupport() { showToast('Abriendo sistema de soporte...', 'info'); }

        function requestCertificate() {
            showToast('Enviando solicitud de constancia de trabajo...', 'info');

            fetch('/api/constancia-trabajo/solicitar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Constancia de trabajo enviada correctamente', 'success');
                } else {
                    showToast(data.message || 'Error al enviar la constancia', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al procesar la solicitud', 'error');
            });
        }

        function requestPayslip() {
            showToast('Enviando solicitud de recibo de pago...', 'info');

            fetch('/api/recibo-pago/solicitar', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Recibo de pago enviado correctamente', 'success');
                } else {
                    showToast(data.message || 'Error al enviar el recibo', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al procesar la solicitud', 'error');
            });
        }
        function requestForm() { window.location.href = '/intranet/Formato-Solicitud-de-Materiales.xls'; }
        function requestVacation() { window.location.href = '/intranet/FORMATO-SOLICITUD-DISFRUTE-DE-VACACIONES.xls'; }
        function openConsumables() { window.location.href = "{{ route('sso.go', ['module' => 'admin', 'redirect' => '/administracion/almacenamiento_impresoras']) }}"; }
        function openAssets() { window.location.href = "{{ route('sso.go', ['module' => 'admin', 'redirect' => '/administracion/bienes_publicos']) }}"; }
        function openMaterials() { window.location.href = "{{ route('sso.go', ['module' => 'admin', 'redirect' => '/administracion/inventario_oficinas']) }}"; }
        function openDigitalization() { window.location.href = "{{ route('sso.go', ['module' => 'admin', 'redirect' => '/administracion/documentos']) }}"; }
        function openConsultancy() { showToast('Abriendo servicios de consultoría...', 'info'); }

        function readNews(newsId) {
            const titles = ['', 'Nueva Política de Trabajo Remoto', 'Récord de Ventas en Q4', 'Empleado del Mes', 'Nuevo Programa de Formación'];
            showToast('Leyendo: ' + titles[newsId] + '...', 'info');
        }

        function sendWishes(userId, name) {
            showToast('Enviando felicitaciones a ' + name + '...', 'info');

            fetch('/api/dashboard/birthdays/send-wish', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Error al enviar la felicitación', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al procesar la solicitud', 'error');
            });
        }

        function openDirectory() { showToast('Abriendo directorio de empleados...', 'info'); }
        function callExtension(ext) { showToast('Marcando extensión ' + ext + '...', 'info'); }

        function showEventTooltip(event, title, description) {
            const tooltip = document.getElementById('eventTooltip');
            const tooltipTitle = document.getElementById('tooltipTitle');
            const tooltipDescription = document.getElementById('tooltipDescription');
            tooltipTitle.textContent = title;
            tooltipDescription.textContent = description;
            const rect = event.target.getBoundingClientRect();
            tooltip.style.left = (rect.left + (rect.width / 2) - 140) + 'px';
            tooltip.style.top = (rect.top - 80) + 'px';
            tooltip.classList.add('show');
        }

        function hideEventTooltip() {
            document.getElementById('eventTooltip').classList.remove('show');
        }

        // ==================== Birthday Functions ====================

        function loadBirthdays() {
            fetch('/api/dashboard/birthdays')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    birthdayData = data;
                    renderBirthdaysList(data.birthdays);
                })
                .catch(error => {
                    console.error('Error loading birthdays:', error);
                    document.getElementById('birthdaysList').innerHTML = `
                        <div class="text-center py-8 text-slate-400">
                            <div class="text-4xl mb-2">😕</div>
                            <p>Error al cargar cumpleaños</p>
                            <p class="text-xs text-slate-500 mt-1">${error.message}</p>
                        </div>
                    `;
                });
        }

        // Load user statistics for "Empleados Activos" card
        function loadUserStats() {
            fetch('/api/dashboard/user-stats')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const stats = data.data;

                        // Update main numbers
                        document.getElementById('totalUsers').textContent = stats.total_users;
                        document.getElementById('totalRegistered').textContent = stats.total_users;
                        document.getElementById('activeUsers').textContent = stats.active_users;
                        document.getElementById('inactiveUsers').textContent = stats.inactive_users;

                        // Update new users this month
                        const newUsersText = stats.new_users_this_month > 0
                            ? `+${stats.new_users_this_month} este mes`
                            : '0 nuevos este mes';
                        document.getElementById('newUsersThisMonth').textContent = newUsersText;

                        // Update badge
                        document.getElementById('userStatsBadge').textContent = `${stats.active_users} activos`;
                    }
                })
                .catch(error => {
                    console.error('Error loading user stats:', error);
                    document.getElementById('totalUsers').textContent = '?';
                    document.getElementById('totalRegistered').textContent = '?';
                    document.getElementById('activeUsers').textContent = '?';
                    document.getElementById('inactiveUsers').textContent = '?';
                    document.getElementById('userStatsBadge').textContent = 'Error';
                });
        }
        function renderBirthdaysList(birthdays) {
            const container = document.getElementById('birthdaysList');
            const noBirthdays = document.getElementById('noBirthdays');

            if (!birthdays || birthdays.length === 0) {
                container.innerHTML = '';
                noBirthdays.classList.remove('hidden');
                return;
            }

            noBirthdays.classList.add('hidden');

            container.innerHTML = birthdays.map(birthday => {
                let daysText = '';
                let daysClass = '';

                if (birthday.has_today) {
                    daysText = '¡Hoy es su cumpleaños! 🎉';
                    daysClass = 'text-green-600';
                } else if (birthday.days_until === 0) {
                    daysText = '¡Hoy es su cumpleaños! 🎉';
                    daysClass = 'text-green-600';
                } else if (birthday.days_until === 1) {
                    daysText = '¡Mañana cumple años!';
                    daysClass = 'text-green-500';
                } else if (birthday.days_until <= 3) {
                    daysText = `En ${birthday.days_until} días`;
                    daysClass = 'text-green-500';
                } else if (birthday.days_until <= 7) {
                    daysText = `En ${birthday.days_until} días`;
                    daysClass = 'text-blue-400';
                } else {
                    daysText = `En ${birthday.days_until} días`;
                    daysClass = 'text-purple-400';
                }

                const isToday = birthday.has_today;

                return `
                    <div class="birthday-card ${isToday ? 'is-today' : ''}">
                        <div class="birthday-avatar">${birthday.initials}</div>
                        <div class="flex-1">
                            <h3 class="font-semibold">${birthday.fullname}</h3>
                            <p class="text-sm text-slate-400">${birthday.departamento}</p>
                            <p class="text-xs ${daysClass} font-semibold">${birthday.birthday_date} - ${daysText}</p>
                        </div>
                        <button class="btn btn-secondary text-sm" onclick="sendWishes(${birthday.id}, '${birthday.fullname.replace(/'/g, "\\'")}')">❤️ Felicitar</button>
                    </div>
                `;
            }).join('');
        }

        function openBirthdayCalendar() {
            if (!birthdayData) {
                fetch('/api/dashboard/birthdays')
                    .then(r => r.json())
                    .then(data => {
                        birthdayData = data;
                        renderBirthdaysList(data.birthdays);
                        if (data.birthdays && data.birthdays.length > 0) {
                            const currentM = (new Date().getMonth() + 1);
                            if (!data.calendar_data[currentM] || data.calendar_data[currentM].length === 0) {
                                const nextB = data.birthdays[0];
                                if (nextB && nextB.birthday_month) {
                                    birthdayCalendarMonth = nextB.birthday_month - 1;
                                }
                            }
                        }
                        renderBirthdayCalendar();
                    });
            } else if (birthdayData.birthdays && birthdayData.birthdays.length > 0) {
                const currentM = (new Date().getMonth() + 1);
                if (!birthdayData.calendar_data[currentM] || birthdayData.calendar_data[currentM].length === 0) {
                    const nextB = birthdayData.birthdays[0];
                    if (nextB && nextB.birthday_month) {
                        birthdayCalendarMonth = nextB.birthday_month - 1;
                    }
                }
            }
            const bm = document.getElementById('birthdayModal');
            if (bm) { bm.style.display = 'flex'; bm.classList.add('show'); }
            renderBirthdayCalendar();
        }

        function closeBirthdayModal(event) {
            if (!event || event.target === event.currentTarget) {
                const bm = document.getElementById('birthdayModal');
                if (bm) {
                    bm.classList.remove('show');
                    bm.style.display = 'none';
                }
            }
        }

        function previousBirthdayMonth() {
            birthdayCalendarMonth--;
            if (birthdayCalendarMonth < 0) {
                birthdayCalendarMonth = 11;
                birthdayCalendarYear--;
            }
            renderBirthdayCalendar();
        }

        function nextBirthdayMonth() {
            birthdayCalendarMonth++;
            if (birthdayCalendarMonth > 11) {
                birthdayCalendarMonth = 0;
                birthdayCalendarYear++;
            }
            renderBirthdayCalendar();
        }

        function renderBirthdayCalendar() {
            if (!birthdayData || !birthdayData.calendar_data) {
                return;
            }

            document.getElementById('birthdayMonthYear').textContent = months[birthdayCalendarMonth] + ' ' + birthdayCalendarYear;

            const firstDay = new Date(birthdayCalendarYear, birthdayCalendarMonth, 1).getDay();
            const daysInMonth = new Date(birthdayCalendarYear, birthdayCalendarMonth + 1, 0).getDate();
            const today = new Date();

            let html = '';

            // Celdas vacías del mes anterior
            for (let i = 0; i < firstDay; i++) {
                const prevMonth = birthdayCalendarMonth === 0 ? 11 : birthdayCalendarMonth - 1;
                const prevYear = birthdayCalendarMonth === 0 ? birthdayCalendarYear - 1 : birthdayCalendarYear;
                const prevDays = new Date(prevYear, prevMonth + 1, 0).getDate();
                const day = prevDays - firstDay + i + 1;
                html += `<div class="calendar-day other-month">${day}</div>`;
            }

            // Días del mes
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = today.getDate() === day && today.getMonth() === birthdayCalendarMonth && today.getFullYear() === birthdayCalendarYear;

                const monthKey = birthdayCalendarMonth + 1;
                const birthdaysOnDay = birthdayData.calendar_data[monthKey]?.filter(b => b.day === day) || [];

                let classes = 'calendar-day';
                let content = `<span>${day}</span>`;

                if (birthdaysOnDay.length > 0) {
                    classes += ' has-birthday';

                    // Determinar proximidad y color
                    const hasTodayBirthday = birthdaysOnDay.some(b => b.is_today || b.days_until === 0) || isToday;
                    const minDays = Math.min(...birthdaysOnDay.map(b => (typeof b.days_until === 'number' ? b.days_until : 999)));

                    if (hasTodayBirthday) {
                        classes += ' is-today-birthday';
                    } else if (minDays <= 7) {
                        classes += ' is-urgent-birthday';
                    } else if (minDays <= 31) {
                        classes += ' is-near-birthday';
                    } else {
                        classes += ' is-other-birthday';
                    }

                    content = `
                        <span class="text-xs font-bold">${day}</span>
                        <span class="text-[10px] leading-none">🎂</span>
                    `;
                } else if (isToday) {
                    classes += ' today';
                }

                let events = '';
                if (birthdaysOnDay.length > 0) {
                    const safeData = JSON.stringify(birthdaysOnDay).replace(/"/g, '&quot;');
                    events = ` onmouseenter="showBirthdayTooltip(event, ${safeData})" onmouseleave="hideBirthdayTooltip()"`;
                }

                html += `<div class="${classes}"${events}>${content}</div>`;
            }

            // Rellenar celdas restantes del mes siguiente
            const totalCells = firstDay + daysInMonth;
            const remainingCells = 42 - totalCells;
            for (let day = 1; day <= remainingCells; day++) {
                html += `<div class="calendar-day other-month">${day}</div>`;
            }

            document.getElementById('birthdayCalendarDays').innerHTML = html;

            // Actualizar la lista debajo del calendario
            const monthKey = birthdayCalendarMonth + 1;
            const birthdaysOnMonth = birthdayData.calendar_data[monthKey] || [];

            if (birthdaysOnMonth.length === 0) {
                document.getElementById('birthdayModalList').innerHTML = `
                    <div class="text-center py-4 text-slate-400">
                        <p>No hay cumpleaños registrados en ${months[birthdayCalendarMonth]}</p>
                    </div>
                `;
            } else {
                document.getElementById('birthdayModalList').innerHTML = birthdaysOnMonth.map(b => {
                    const tag = (b.is_today || b.days_until === 0)
                        ? '🎉 ¡Hoy es su cumpleaños!'
                        : (b.days_until === 1 ? '¡Mañana!' : (b.days_until <= 30 ? `En ${b.days_until} días` : `${b.day} de ${months[birthdayCalendarMonth]}`));
                    const badgeClass = (b.is_today || b.days_until === 0)
                        ? 'bg-emerald-500/20 text-emerald-600 font-bold'
                        : (b.days_until <= 7 ? 'bg-amber-500/20 text-amber-600 font-bold' : 'bg-blue-500/20 text-blue-600 font-medium');

                    return `
                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100 shadow-sm hover:bg-white transition-all">
                            <div class="birthday-avatar" style="width: 38px; height: 38px; font-size: 0.8rem; font-weight: bold; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center;">
                                ${b.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-slate-800 truncate">${b.name}</p>
                                <p class="text-xs text-slate-400 truncate">${b.departamento || 'Sin asignar'}</p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-xs px-2.5 py-0.5 rounded-full ${badgeClass}">${tag}</span>
                                <span class="text-[11px] text-slate-400 mt-0.5 font-medium">Día ${b.day}</span>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        function showBirthdayTooltip(event, birthdays) {
            const tooltip = document.getElementById('birthdayTooltip');
            const namesEl = document.getElementById('birthdayTooltipNames');
            const deptEl = document.getElementById('birthdayTooltipDept');
            if (!tooltip || !namesEl) return;

            if (Array.isArray(birthdays)) {
                let html = birthdays.map(b => {
                    let status = (b.is_today || b.days_until === 0)
                        ? '<span class="text-emerald-600 font-bold">🎉 ¡Hoy cumple años!</span>'
                        : (b.days_until === 1 ? '<span class="text-amber-600 font-bold">¡Mañana!</span>' : `<span class="text-blue-600 font-semibold">En ${b.days_until} días</span>`);
                    return `
                        <div class="py-1.5 border-b border-slate-100 last:border-b-0">
                            <div class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                                <span>🎂</span>
                                <span>${b.name}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 flex items-center justify-between gap-3">
                                <span>${b.departamento || 'Sin asignar'}</span>
                                ${status}
                            </div>
                        </div>
                    `;
                }).join('');
                namesEl.innerHTML = html;
                if (deptEl) deptEl.textContent = '';
            } else {
                namesEl.textContent = birthdays;
                if (deptEl) deptEl.textContent = 'Cumpleaños';
            }

            const rect = event.target.getBoundingClientRect();
            let left = rect.left + (rect.width / 2) - 130;
            if (left < 10) left = 10;
            if (left + 270 > window.innerWidth) left = window.innerWidth - 280;
            let top = rect.top - 85;
            if (top < 10) top = rect.bottom + 10;

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.classList.add('show');
        }

        function hideBirthdayTooltip() {
            const tooltip = document.getElementById('birthdayTooltip');
            if (tooltip) tooltip.classList.remove('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Asegurar que todos los modales estén estrictamente ocultos al cargar
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.classList.remove('show');
                modal.style.display = 'none';
            });

            loadBirthdays();
            loadDirectory();
            loadEvents();
            loadUserStats();
            loadNews();
        });

        // Cerrar cualquier modal abierto con la tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                });
            }
        });
    </script>

    <!-- ==================== Directory Functions ==================== -->
    <script>
        // Directory data
        let directoryData = [];
        let isAdmin = {{ Auth::check() && Auth::user()->hasRole('Admin') ? 'true' : 'false' }};

        // Load directory from API
        function loadDirectory() {
            fetch('/api/directory')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        directoryData = data.data;
                        renderDirectoryList(data.data.slice(0, 3)); // Show first 3 in card
                    } else {
                        document.getElementById('directoryList').innerHTML = `
                            <div class="text-center py-4 text-slate-400">
                                <p class="text-sm">${data.message || 'Error al cargar el directorio'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading directory:', error);
                    document.getElementById('directoryList').innerHTML = `
                        <div class="text-center py-4 text-slate-400">
                            <p class="text-sm">Error al cargar el directorio</p>
                            <p class="text-xs text-slate-500 mt-1">${error.message}</p>
                        </div>
                    `;
                });
        }

        // Render directory list in main card (first 3 items)
        function renderDirectoryList(items) {
            const container = document.getElementById('directoryList');

            if (!items || items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-slate-400">
                        <p class="text-sm">No hay contactos en el directorio</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = items.map(item => `
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 cursor-pointer" onclick="callExtension('${item.extension}')">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-200 rounded-full flex items-center justify-center text-white">${item.icono || '📞'}</div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm">${item.nombre}</p>
                        <p class="text-xs text-slate-400">Ext. ${item.extension}</p>
                    </div>
                    ${isAdmin === true ? `
                        <div class="flex items-center gap-1" onclick="event.stopPropagation()">
                            <button onclick="editDirectoryItem(${item.id})" class="p-1 hover:bg-white/10 rounded text-blue-400" title="Editar">✏️</button>
                            <button onclick="deleteDirectoryItem(${item.id})" class="p-1 hover:bg-white/10 rounded text-red-400" title="Eliminar">🗑️</button>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        // Open modal with all directory entries
        function openDirectoryModal() {
            fetch('/api/directory')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderDirectoryModal(data.data);
                        const dm = document.getElementById('directoryModal');
                        if (dm) { dm.style.display = 'flex'; dm.classList.add('show'); }
                    } else {
                        showToast('Error al cargar el directorio', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error al cargar el directorio', 'error');
                });
        }

        // Render directory modal list
        function renderDirectoryModal(items) {
            const container = document.getElementById('directoryModalList');

            if (!items || items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4 text-slate-400">
                        <p class="text-sm">No hay contactos en el directorio</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = items.map(item => `
                <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-200 rounded-full flex items-center justify-center text-white text-lg">${item.icono || '📞'}</div>
                    <div class="flex-1">
                        <p class="font-semibold">${item.nombre}</p>
                        <p class="text-sm text-slate-400">Ext. ${item.extension}</p>
                    </div>
                    <div class="flex items-center gap-2">

                        ${isAdmin === true ? `
                            <button onclick="editDirectoryItem(${item.id})" class="p-2 hover:bg-white/10 rounded text-blue-400" title="Editar">✏️</button>
                            <button onclick="deleteDirectoryItem(${item.id})" class="p-2 hover:bg-white/10 rounded text-red-400" title="Eliminar">🗑️</button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        // Close directory modal
        function closeDirectoryModal(event) {
            if (!event || event.target === event.currentTarget) {
                const dm = document.getElementById('directoryModal');
                if (dm) {
                    dm.classList.remove('show');
                    dm.style.display = 'none';
                }
            }
        }

        // Add new directory item
        function addDirectoryItem() {
            document.getElementById('directoryEditTitle').textContent = '📞 Agregar Contacto';
            document.getElementById('directoryEditId').value = '';
            document.getElementById('directoryEditNombre').value = '';
            document.getElementById('directoryEditExtension').value = '';
            document.getElementById('directoryEditIcono').value = '📞';
            const dem = document.getElementById('directoryEditModal');
            if (dem) { dem.style.display = 'flex'; dem.classList.add('show'); }
        }

        // Edit directory item
        function editDirectoryItem(id) {
            const item = directoryData.find(d => d.id === id);
            if (!item) return;

            document.getElementById('directoryEditTitle').textContent = '📞 Editar Contacto';
            document.getElementById('directoryEditId').value = item.id;
            document.getElementById('directoryEditNombre').value = item.nombre;
            document.getElementById('directoryEditExtension').value = item.extension;
            document.getElementById('directoryEditIcono').value = item.icono || '📞';
            const dem = document.getElementById('directoryEditModal');
            if (dem) { dem.style.display = 'flex'; dem.classList.add('show'); }

            // Close directory modal if open
            const dm = document.getElementById('directoryModal');
            if (dm) { dm.classList.remove('show'); dm.style.display = 'none'; }
        }

        // Close edit modal
        function closeDirectoryEditModal(event) {
            if (!event || event.target === event.currentTarget) {
                const dem = document.getElementById('directoryEditModal');
                if (dem) {
                    dem.classList.remove('show');
                    dem.style.display = 'none';
                }
            }
        }

        // Save directory item (create or update)
        function saveDirectoryItem(event) {
            event.preventDefault();

            const id = document.getElementById('directoryEditId').value;
            const nombre = document.getElementById('directoryEditNombre').value;
            const extension = document.getElementById('directoryEditExtension').value;
            const icono = document.getElementById('directoryEditIcono').value || '📞';

            const url = id ? `/api/directory/${id}` : '/api/directory';
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nombre, extension, icono })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeDirectoryEditModal();
                    loadDirectory();
                } else {
                    showToast(data.message || 'Error al guardar', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al guardar', 'error');
            });
        }

        // Delete directory item
        function deleteDirectoryItem(id) {
            document.getElementById('directoryDeleteId').value = id;
            const ddm = document.getElementById('directoryDeleteModal');
            if (ddm) { ddm.style.display = 'flex'; ddm.classList.add('show'); }

            // Close directory modal if open
            const dm = document.getElementById('directoryModal');
            if (dm) { dm.classList.remove('show'); dm.style.display = 'none'; }
        }

        // Close delete modal
        function closeDirectoryDeleteModal(event) {
            if (!event || event.target === event.currentTarget) {
                const ddm = document.getElementById('directoryDeleteModal');
                if (ddm) {
                    ddm.classList.remove('show');
                    ddm.style.display = 'none';
                }
            }
        }

        // Confirm delete
        function confirmDeleteDirectory() {
            const id = document.getElementById('directoryDeleteId').value;

            fetch(`/api/directory/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeDirectoryDeleteModal();
                    loadDirectory();
                } else {
                    showToast(data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al eliminar', 'error');
            });
        }

        // Call extension (visual feedback)
        function callExtension(ext) {
            showToast(`Marcando extensión ${ext}...`, 'info');
        }

        // ==================== News Functions ====================

        let newsData = [];
        let selectedNews = null;

        // Check if user can manage news (same roles as NoticiaController)
        let canManageNews = {{ Auth::check() && (
            Auth::user()->hasRole('Admin') ||
            Auth::user()->hasRole('GTIC') ||
            Auth::user()->hasRole('Gerente-Admin') ||
            Auth::user()->hasRole('gerente_ogh') ||
            Auth::user()->hasRole('coordinacion_ogh')
        ) ? 'true' : 'false' }};

        function loadNews() {
            fetch('/api/noticias')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        newsData = data.data;
                        renderNewsList(data.data);
                    }
                })
                .catch(error => console.error('Error loading news:', error));
        }

        function renderNewsList(news) {
            const container = document.getElementById('newsGridView');
            if (!container) return;

            if (!news || news.length === 0) {
                container.innerHTML = `
                    <div class="col-span-2 text-center py-8 text-slate-400">
                        <p class="text-sm">No hay noticias disponibles</p>
                    </div>
                `;
                return;
            }

            // Only show first 4 news items in grid view
            const newsToShow = news.slice(0, 4);

            const categoryColors = {
                'Corporativo': 'bg-blue-500/20 text-blue-600',
                'Logros': 'bg-green-500/20 text-green-600',
                'Reconocimiento': 'bg-purple-500/20 text-purple-600',
                'Capacitación': 'bg-orange-500/20 text-orange-600',
                'Evento': 'bg-red-500/20 text-red-600',
                'Aviso': 'bg-yellow-500/20 text-yellow-600'
            };

            container.innerHTML = newsToShow.map(item => {
                const colorClass = categoryColors[item.categoria] || 'bg-blue-500/20 text-blue-600';
                const date = new Date(item.fecha_publicacion);
                const formattedDate = date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
                const emoji = getCategoryEmoji(item.categoria);

                return `
                    <div class="news-card" onclick="viewNews(${item.id})">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center text-2xl">${emoji}</div>
                            <div class="flex-1">
                                <span class="text-xs ${colorClass} px-2 py-1 rounded">${item.categoria}</span>
                                <h3 class="font-semibold mt-2 mb-1">${item.titulo}</h3>
                                <p class="text-sm text-slate-400 mb-2">${item.contenido.substring(0, 80)}...</p>
                                <p class="text-xs text-slate-500">${formattedDate}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Render all news in accordion view
        function renderNewsAccordion(news) {
            const container = document.getElementById('newsAccordion');
            if (!container) return;

            if (!news || news.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-slate-400">
                        <p class="text-sm">No hay noticias disponibles</p>
                    </div>
                `;
                return;
            }

            const categoryColors = {
                'Corporativo': 'bg-blue-500/20 text-blue-600',
                'Logros': 'bg-green-500/20 text-green-600',
                'Reconocimiento': 'bg-purple-500/20 text-purple-600',
                'Capacitación': 'bg-orange-500/20 text-orange-600',
                'Evento': 'bg-red-500/20 text-red-600',
                'Aviso': 'bg-yellow-500/20 text-yellow-600'
            };

            container.innerHTML = news.map(item => {
                const colorClass = categoryColors[item.categoria] || 'bg-blue-500/20 text-blue-600';
                const date = new Date(item.fecha_publicacion);
                const formattedDate = date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
                const emoji = getCategoryEmoji(item.categoria);

                return `
                    <div class="news-accordion-item bg-white/30 rounded-lg mb-2">
                        <div class="news-accordion-header" onclick="toggleNewsAccordion(${item.id})">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center text-xl">${emoji}</div>
                                <div>
                                    <span class="text-xs ${colorClass} px-2 py-1 rounded">${item.categoria}</span>
                                    <h3 class="font-semibold text-sm mt-1">${item.titulo}</h3>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500">${formattedDate}</span>
                                <span class="text-slate-400 transform transition-transform" id="news-arrow-${item.id}">▼</span>
                            </div>
                        </div>
                        <div class="news-accordion-content" id="news-content-${item.id}">
                            <div class="px-4 pb-4 pt-1 space-y-2">
                                ${item.imagen ? `
                                <div class="w-full h-44 bg-gray-100 rounded-xl overflow-hidden mb-3 shadow-inner">
                                    <img src="/storage/noticias/${item.imagen}" alt="${item.titulo}" class="w-full h-full object-cover">
                                </div>
                                ` : ''}
                                <p class="text-sm text-slate-600 leading-relaxed">${item.contenido}</p>
                                ${canManageNews ? `
                                <div class="flex justify-end gap-2 mt-3 pt-3 border-t border-slate-200/80">
                                    <button onclick="event.stopPropagation(); editNews(${item.id})" class="btn btn-secondary text-xs">✏️ Editar</button>
                                    <button onclick="event.stopPropagation(); deleteNews(${item.id})" class="btn bg-red-500 hover:bg-red-600 text-white text-xs">🗑️ Eliminar</button>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            container.classList.add('show');
        }

        // Toggle accordion item
        function toggleNewsAccordion(id) {
            const content = document.getElementById('news-content-' + id);
            const arrow = document.getElementById('news-arrow-' + id);

            if (content.classList.contains('open')) {
                content.classList.remove('open');
                arrow.style.transform = 'rotate(0deg)';
            } else {
                // Close all other accordions first
                document.querySelectorAll('.news-accordion-content.open').forEach(el => {
                    el.classList.remove('open');
                });
                document.querySelectorAll('.news-accordion-header span[id^="news-arrow-"]').forEach(el => {
                    el.style.transform = 'rotate(0deg)';
                });

                content.classList.add('open');
                arrow.style.transform = 'rotate(180deg)';
            }
        }
        function getCategoryEmoji(categoria) {
            const emojis = {
                'Corporativo': '📋',
                'Logros': '📈',
                'Reconocimiento': '⭐',
                'Capacitación': '📚',
                'Evento': '🎉',
                'Aviso': '📢'
            };
            return emojis[categoria] || '📰';
        }

        function openNewsModal() {
            document.getElementById('newsTitle').value = '';
            document.getElementById('newsCategory').value = '';
            document.getElementById('newsContent').value = '';
            document.getElementById('newsImage').value = '';
            const nm = document.getElementById('newsModal');
            if (nm) { nm.style.display = 'flex'; nm.classList.add('show'); }
        }

        function closeNewsModal(event) {
            if (!event || event.target === event.currentTarget) {
                const nm = document.getElementById('newsModal');
                if (nm) {
                    nm.classList.remove('show');
                    nm.style.display = 'none';
                }
            }
        }

        function saveNews(event) {
            event.preventDefault();

            const titulo = document.getElementById('newsTitle').value;
            const categoria = document.getElementById('newsCategory').value;
            const contenido = document.getElementById('newsContent').value;
            const publicada = document.getElementById('newsPublicada').value;
            const imagen = document.getElementById('newsImage').files[0];

            const formData = new FormData();
            formData.append('titulo', titulo);
            formData.append('categoria', categoria);
            formData.append('contenido', contenido);
            formData.append('publicada', publicada);
            if (imagen) {
                formData.append('imagen', imagen);
            }

            fetch('/api/noticias', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeNewsModal();
                    loadNews();
                } else {
                    showToast(data.message || 'Error al guardar la noticia', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al guardar la noticia', 'error');
            });
        }

        function viewNews(id) {
            const news = newsData.find(n => n.id === id);
            if (!news) return;

            selectedNews = news;

            const categoryColors = {
                'Corporativo': 'bg-blue-500/20 text-blue-600',
                'Logros': 'bg-green-500/20 text-green-600',
                'Reconocimiento': 'bg-purple-500/20 text-purple-600',
                'Capacitación': 'bg-orange-500/20 text-orange-600',
                'Evento': 'bg-red-500/20 text-red-600',
                'Aviso': 'bg-yellow-500/20 text-yellow-600'
            };

            const colorClass = categoryColors[news.categoria] || 'bg-blue-500/20 text-blue-600';
            const date = new Date(news.fecha_publicacion);
            const formattedDate = date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });

            document.getElementById('newsDetailsTitle').textContent = news.titulo;

            let buttonsHtml = '';
            if (canManageNews) {
                buttonsHtml = `
                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-white/10">
                        <button onclick="editNews(${news.id})" class="btn btn-secondary text-sm">✏️ Editar</button>
                        <button onclick="deleteNews(${news.id})" class="btn bg-red-500 hover:bg-red-600 text-white text-sm">🗑️ Eliminar</button>
                    </div>
                `;
            }

            document.getElementById('newsDetailsContent').innerHTML = `
                <div class="space-y-4">
                    ${news.imagen ? `
                    <div class="w-full h-48 bg-gray-100 rounded-lg overflow-hidden mb-4">
                        <img src="/storage/noticias/${news.imagen}" alt="${news.titulo}" class="w-full h-full object-cover">
                    </div>
                    ` : ''}
                    <div class="flex items-center gap-2">
                        <span class="text-xs ${colorClass} px-2 py-1 rounded">${news.categoria}</span>
                        <span class="text-xs text-slate-400">${formattedDate}</span>
                    </div>
                    <div class="p-4 rounded-lg bg-white/4">
                        <p class="text-sm text-black">${news.contenido}</p>
                    </div>
                    ${buttonsHtml}
                </div>
            `;

            const ndm = document.getElementById('newsDetailsModal');
            if (ndm) { ndm.style.display = 'flex'; ndm.classList.add('show'); }
        }

        function editNews(id) {
            // Redirect to edit page
            window.location.href = '/noticias/' + id + '/edit';
        }

        function deleteNews(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "No podrás revertir esto!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar!",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route("noticias.destroy", ":id") }}'.replace(':id', id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        // Check if it's a redirect (302) - follow the redirect
                        if (response.redirected) {
                            window.location.href = response.url;
                            return;
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.success) {
                            showToast(data.message || 'Noticia eliminada exitosamente', 'success');
                            closeNewsDetailsModal();
                            loadNews();
                        } else if (data && !data.success) {
                            showToast(data.message || 'Error al eliminar la noticia', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error al eliminar la noticia', 'error');
                    });
                }
            });
        }
        function closeNewsDetailsModal(event) {
            if (!event || event.target === event.currentTarget) {
                const ndm = document.getElementById('newsDetailsModal');
                if (ndm) {
                    ndm.classList.remove('show');
                    ndm.style.display = 'none';
                }
            }
        }

        function openAllNews() {
            // Toggle between grid view and accordion view
            const gridView = document.getElementById('newsGridView');
            const accordion = document.getElementById('newsAccordion');
            const btn = document.querySelector('button[onclick="openAllNews()"]');

            if (accordion.classList.contains('show')) {
                // If accordion is showing, hide it and show grid
                accordion.classList.remove('show');
                gridView.classList.remove('hidden');
                btn.textContent = 'Ver todas';
            } else {
                // Show accordion with all news
                renderNewsAccordion(newsData);
                gridView.classList.add('hidden');
                btn.textContent = 'Ver menos';
            }
        }
    </script>
</x-app-layout>


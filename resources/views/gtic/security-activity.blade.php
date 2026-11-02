<x-app-layout>
@section('template_title')
    Actividad de Seguridad
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-600 text-white rounded-lg p-4">
                <h5 class="text-sm font-medium opacity-90">Total Hoy</h5>
                <h2 class="text-3xl font-bold mt-1">{{ $stats['total_today'] }}</h2>
            </div>
            <div class="bg-green-600 text-white rounded-lg p-4">
                <h5 class="text-sm font-medium opacity-90">Exitosos</h5>
                <h2 class="text-3xl font-bold mt-1">{{ $stats['success_today'] }}</h2>
            </div>
            <div class="bg-red-600 text-white rounded-lg p-4">
                <h5 class="text-sm font-medium opacity-90">Fallidos</h5>
                <h2 class="text-3xl font-bold mt-1">{{ $stats['failed_today'] }}</h2>
            </div>
            <div class="bg-yellow-500 text-gray-800 rounded-lg p-4">
                <h5 class="text-sm font-medium opacity-90">IPs Únicas</h5>
                <h2 class="text-3xl font-bold mt-1">{{ $stats['unique_ips'] }}</h2>
            </div>
        </div>

        <!-- Filters -->
        <form action="{{ route('gtic.security-activity.index') }}" method="GET" class="mb-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Exitoso</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallido</option>
                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                </div>
                <div>
                    <input type="text" name="ip_address" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="IP" value="{{ request('ip_address') }}">
                </div>
                <div>
                    <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('date_from') }}">
                </div>
                <div>
                    <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('date_to') }}">
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Filtrar
                    </button>
                </div>
                <div>
                    <a href="{{ route('gtic.security-activity.export', request()->query()) }}" class="block w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors font-medium text-center">
                        Exportar Excel
                    </a>
                </div>
            </div>
        </form>

        @if ($message = Session::get('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 whitespace-nowrap">Usuario</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 whitespace-nowrap">IP</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 whitespace-nowrap">Dispositivo</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 whitespace-nowrap">Estado</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 whitespace-nowrap">Fecha</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 whitespace-nowrap">Detalles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($activities as $activity)
                        <tr class="hover:bg-gray-50 even:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $activity->user ? $activity->user->name : 'N/A' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $activity->ip_address }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $activity->device ?? 'Desconocido' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($activity->status == 'success')
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Exitoso</span>
                                @elseif($activity->status == 'failed')
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Fallido</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Bloqueado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($activity->failure_reason)
                                    <small class="text-red-600 text-sm">{{ $activity->failure_reason }}</small>
                                @else
                                    <small class="text-gray-500 text-sm">-</small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $activities->links() }}
        </div>
    </div>
</x-app-layout>


@extends('layouts.app')

@section('template_title')
    Actividad de Seguridad
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {{ __('Actividad de Seguridad') }}
                            </span>
                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Hoy</h5>
                                        <h2>{{ $stats['total_today'] }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Exitosos</h5>
                                        <h2>{{ $stats['success_today'] }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Fallidos</h5>
                                        <h2>{{ $stats['failed_today'] }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body">
                                        <h5 class="card-title">IPs Únicas</h5>
                                        <h2>{{ $stats['unique_ips'] }}</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <form action="{{ route('gtic.security-activity.index') }}" method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">Todos los estados</option>
                                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Exitoso</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallido</option>
                                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Bloqueado</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="ip_address" class="form-control" placeholder="IP" value="{{ request('ip_address') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('gtic.security-activity.export', request()->query()) }}" class="btn btn-success w-100">
                                        Exportar Excel
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr>
                                        <th>Usuario</th>
                                        <th>IP</th>
                                        <th>Dispositivo</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activities as $activity)
                                        <tr>
                                            <td>{{ $activity->user ? $activity->user->name : 'N/A' }}</td>
                                            <td>{{ $activity->ip_address }}</td>
                                            <td>{{ $activity->device ?? 'Desconocido' }}</td>
                                            <td>
                                                @if($activity->status == 'success')
                                                    <span class="badge bg-success">Exitoso</span>
                                                @elseif($activity->status == 'failed')
                                                    <span class="badge bg-danger">Fallido</span>
                                                @else
                                                    <span class="badge bg-warning">Bloqueado</span>
                                                @endif
                                            </td>
                                            <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td>
                                                @if($activity->failure_reason)
                                                    <small class="text-danger">{{ $activity->failure_reason }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


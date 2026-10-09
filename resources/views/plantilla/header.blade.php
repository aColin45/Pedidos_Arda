<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('web.index') }}" class="nav-link" target="_blank">
                    Tienda
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('contacto.index.panel') }}" class="nav-link">Contacto</a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            {{-- Botón Pantalla Completa --}}
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>

            @if(Auth::check())

            {{-- ========================================================= --}}
            {{-- ||              CAMPANITA DE NOTIFICACIONES            || --}}
            {{-- ========================================================= --}}
            @php 
                // Obtenemos las notificaciones no leídas del usuario actual
                $notificaciones = auth()->user()->unreadNotifications; 
            @endphp
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false" style="position: relative;">
                    <i class="far fa-bell" style="font-size: 1.2rem;"></i>
                    @if($notificaciones->count() > 0)
                        <span class="badge bg-danger rounded-pill position-absolute" style="top: 2px; right: 2px; font-size: 0.6rem; padding: 0.25em 0.4em;">
                            {{ $notificaciones->count() }}
                        </span>
                    @endif
                </a>
                
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 320px; border-radius: 12px;">
                    <span class="dropdown-header text-center bg-light font-weight-bold" style="border-radius: 12px 12px 0 0; padding: 10px;">
                        <i class="fas fa-bell text-warning me-1"></i> {{ $notificaciones->count() }} Alertas Pendientes
                    </span>
                    <div class="dropdown-divider m-0"></div>
                    
                    <div style="max-height: 300px; overflow-y: auto;">
                        @forelse($notificaciones->take(10) as $notificacion)
                            <a href="#" class="dropdown-item py-3 border-bottom text-wrap" style="white-space: normal;">
                                <div class="d-flex align-items-start">
                                    <i class="{{ $notificacion->data['icono'] ?? 'fas fa-bell' }} {{ $notificacion->data['color'] ?? 'text-primary' }} fa-fw mt-1 me-3 fa-lg"></i>
                                    <div>
                                        <h6 class="mb-1 font-weight-bold" style="font-size: 0.85rem;">{{ $notificacion->data['titulo'] }}</h6>
                                        <p class="text-muted mb-1" style="font-size: 0.8rem; line-height: 1.2;">{{ $notificacion->data['mensaje'] }}</p>
                                        <small class="text-secondary" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i>{{ $notificacion->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="dropdown-item text-center py-4 text-muted" style="font-size: 0.85rem;">
                                <i class="fas fa-check-circle text-success mb-2 fa-2x d-block"></i>
                                No tienes alertas nuevas.
                            </div>
                        @endforelse
                    </div>

                    @if($notificaciones->count() > 0)
                        <div class="dropdown-divider m-0"></div>
                        <form action="{{ route('notificaciones.leer') }}" method="POST" class="p-2 text-center m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light text-primary w-100 font-weight-bold" style="border-radius: 8px;">
                                <i class="fas fa-check-double me-1"></i> Marcar todas como leídas
                            </button>
                        </form>
                    @endif
                </div>
            </li>
            {{-- ========================================================= --}}


            {{-- Menú de Usuario Original --}}
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="d-none d-md-inline">{{Auth::user()->name}}</span>

                    {{-- ICONO JUNTO AL NOMBRE --}}
                    @role('admin')
                    <i class="fas fa-user-shield ms-1 text-warning" title="Administrador"></i> 
                    @endrole
                    @role('agente-ventas')
                    <i class="fas fa-briefcase ms-1 text-info" title="Agente de Ventas"></i> 
                    @endrole
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <p>
                            @role('admin')
                            <i class="fas fa-user-shield me-2"></i> Administrador
                            @endrole
                            @role('agente-ventas')
                            <i class="fas fa-briefcase me-2"></i> Agente de Ventas
                            @endrole
                            <small class="d-block mt-1">{{Auth::user()->name}}</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <a href="{{route('perfil.edit')}}" class="btn btn-default btn-flat">
                            <i class="fas fa-user-circle me-1"></i> Perfil
                        </a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="btn btn-default btn-flat float-end">
                            <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
                        </a>
                    </li>
                    <form action="{{route('logout')}}" id="logout-form" method="post" class="d-none">
                        @csrf
                    </form>
                </ul>
            </li>
            @endif
        </ul>
        </div>
    </nav>
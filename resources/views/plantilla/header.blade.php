<nav class="app-header navbar navbar-expand bg-body border-bottom">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('web.index') }}" class="nav-link" target="_blank">
                    <i class="fas fa-store text-primary mr-1"></i> Tienda
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('contacto.index.panel') }}" class="nav-link">Contacto</a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto align-items-center">
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
            <li class="nav-item dropdown me-2">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false" style="position: relative;">
                    <i class="far fa-bell" style="font-size: 1.2rem;"></i>
                    @if($notificaciones->count() > 0)
                        <span class="badge bg-danger rounded-pill position-absolute shadow-sm" style="top: 0px; right: 0px; font-size: 0.6rem; padding: 0.25em 0.4em;">
                            {{ $notificaciones->count() }}
                        </span>
                    @endif
                </a>
                
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 320px; border-radius: 12px; margin-top: 10px;">
                    <span class="dropdown-header text-center bg-light font-weight-bold border-bottom" style="border-radius: 12px 12px 0 0; padding: 12px;">
                        <i class="fas fa-bell text-warning me-1"></i> {{ $notificaciones->count() }} Alertas Pendientes
                    </span>
                    
                    <div style="max-height: 300px; overflow-y: auto;">
                        @forelse($notificaciones->take(10) as $notificacion)
                            <a href="#" class="dropdown-item py-3 border-bottom text-wrap" style="white-space: normal; transition: background-color 0.2s;">
                                <div class="d-flex align-items-start">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-light mr-3" style="width: 35px; height: 35px; min-width: 35px;">
                                        <i class="{{ $notificacion->data['icono'] ?? 'fas fa-bell' }} {{ $notificacion->data['color'] ?? 'text-primary' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 0.85rem;">{{ $notificacion->data['titulo'] }}</h6>
                                        <p class="text-muted mb-1" style="font-size: 0.8rem; line-height: 1.2;">{{ $notificacion->data['mensaje'] }}</p>
                                        <small class="text-secondary font-weight-bold" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i>{{ $notificacion->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="dropdown-item text-center py-4 text-muted" style="font-size: 0.85rem;">
                                <i class="fas fa-check-circle text-success mb-2 fa-2x d-block opacity-75"></i>
                                No tienes alertas nuevas.
                            </div>
                        @endforelse
                    </div>

                    @if($notificaciones->count() > 0)
                        <div class="p-2 text-center bg-light" style="border-radius: 0 0 12px 12px;">
                            <form action="{{ route('notificaciones.leer') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light text-primary w-100 font-weight-bold shadow-sm" style="border-radius: 8px;">
                                    <i class="fas fa-check-double me-1"></i> Marcar todas como leídas
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </li>
            {{-- ========================================================= --}}


            {{-- ========================================================= --}}
            {{-- ||              NUEVO MENÚ DE USUARIO AVATAR           || --}}
            {{-- ========================================================= --}}
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center px-2 rounded hover-bg-light" data-bs-toggle="dropdown" aria-expanded="false" style="transition: background-color 0.2s;">
                    <div class="d-none d-md-flex flex-column text-end me-3" style="line-height: 1.1;">
                        <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                        @php
                            $rolOriginal = auth()->user()->roles->first()->name ?? 'Usuario';
                            $rolFormateado = ucwords(str_replace('-', ' ', $rolOriginal));
                            if($rolFormateado == 'Super Admin') $rolFormateado = 'Super Administrador';
                        @endphp
                        <span class="text-muted font-weight-bold text-uppercase" style="font-size: 0.65rem;">{{ $rolFormateado }}</span>
                    </div>
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 35px; height: 35px;">
                        <i class="fas fa-user"></i>
                    </div>
                </a>
                
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end border-0 shadow-lg p-0" style="border-radius: 12px; margin-top: 10px; overflow: hidden;">
                    
                    {{-- Cabecera del Menú (Fondo degradado azul) --}}
                    <div class="text-white text-center py-4" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <div class="bg-white text-primary rounded-circle d-inline-flex justify-content-center align-items-center shadow mb-2" style="width: 65px; height: 65px; font-size: 2rem;">
                            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
                                <i class="fas fa-user-shield"></i>
                            @else
                                <i class="fas fa-user-tie"></i>
                            @endif
                        </div>
                        <h6 class="font-weight-bold mb-0 text-white">{{ auth()->user()->name }}</h6>
                        <span class="small font-weight-bold text-uppercase" style="opacity: 0.85; letter-spacing: 0.5px;">
                            {{ $rolFormateado }}
                        </span>
                    </div>
                    
                    {{-- Cuerpo del Menú --}}
                    <div class="p-2 bg-white">
                        
                        <a href="{{ route('perfil.edit') }}" class="dropdown-item rounded p-2 mb-1 d-flex align-items-center" style="transition: background-color 0.2s;">
                            <div class="bg-light text-primary rounded d-flex align-items-center justify-content-center mr-3" style="width: 38px; height: 38px;">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div>
                                <span class="d-block font-weight-bold text-dark" style="font-size: 0.9rem;">Mi Perfil</span>
                                <span class="d-block small text-muted" style="font-size: 0.75rem;">Ajustes de cuenta y contraseña</span>
                            </div>
                        </a>
                        
                        <div class="dropdown-divider my-1"></div>
                        
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item rounded p-2 d-flex align-items-center text-danger" style="transition: background-color 0.2s;">
                                <div class="bg-light text-danger rounded d-flex align-items-center justify-content-center mr-3" style="width: 38px; height: 38px;">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <span class="font-weight-bold" style="font-size: 0.9rem;">Cerrar Sesión</span>
                            </button>
                        </form>
                        
                    </div>
                </div>
            </li>
            @endif
        </ul>
    </div>
</nav>
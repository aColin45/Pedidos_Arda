<nav class="navbar navbar-expand-lg navbar-dark navbar-store">
    <div class="container px-4 px-lg-5">
        {{-- Logo (Ajusta la ruta si es diferente) --}}
        <a class="navbar-brand" href="/">
            <img src="{{ asset('assets/img/LOGO.png') }}" alt="Grupo Industrial ARDA" style="height: 60px;">
        </a>
        {{-- Botón Toggler --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span
                class="navbar-toggler-icon"></span></button>

        {{-- Contenido Colapsable del Menú --}}
        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            {{-- Enlaces a la Izquierda --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" aria-current="page" href="/">Inicio</a></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contacto.index.web') ? 'active' : '' }}" 
                    href="{{ route('contacto.index.web') }}">Contacto</a>
                </li>
            </ul>

            {{-- Elementos a la Derecha (Carrito y Usuario/Login) --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center"> 

                @guest
                    {{-- SI ES INVITADO: Mostrar botón de Login --}}
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                        </a>
                    </li>
                @else
                    {{-- SI ESTÁ LOGUEADO: Mostrar Carrito y Menú de Usuario --}}

                    {{-- 1. Botón del Carrito --}}
                    <li class="nav-item me-lg-2 mb-2 mb-lg-0">
                        <a href="{{route('carrito.mostrar')}}" class="btn btn-outline-dark">
                            <i class="bi-cart-fill me-1"></i>
                            Pedido
                            <span class="badge bg-dark text-white ms-1 rounded-pill">
                                {{-- Usar array_sum(array_column(...)) para sumar cantidades --}}
                                {{ session('carrito') ? array_sum(array_column(session('carrito'), 'cantidad')) : 0 }}
                            </span>
                        </a>
                    </li>

                    {{-- 2. Menú Desplegable de Usuario --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" id="navbarDropdownUser" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                            {{-- Icono inteligente --}}
                            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin']))
                                <i class="fas fa-user-shield ms-1 text-warning" title="Administrador"></i>
                            @else
                                <i class="fas fa-user-tie ms-1 text-info" title="Usuario"></i>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            
                            {{-- Solo mostrar enlace a Dashboard a quienes deben verlo --}}
                            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'superadmin', 'agente-ventas']))
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a></li>
                            @else
                                {{-- Si es Almacén o alguien sin permisos, lo mandamos a Inventarios --}}
                                <li><a class="dropdown-item" href="{{ route('inventarios.index') }}"><i class="fas fa-boxes fa-fw me-2"></i> Ir a mi Panel</a></li>
                            @endif
                            
                            {{-- Solo agentes, admins y super-admins deben ver "Mis Pedidos" --}}
                            @if(auth()->user()->can('pedido-list'))
                            <li><a class="dropdown-item" href="{{ route('perfil.pedidos') }}"><i class="fas fa-box fa-fw me-2"></i> Mis Pedidos</a></li>
                            @endif
                            
                            <li><a class="dropdown-item" href="{{ route('perfil.edit') }}"><i class="fas fa-user-circle fa-fw me-2"></i> Mi Perfil</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-web').submit();">
                                   <i class="fas fa-sign-out-alt fa-fw me-2"></i> Cerrar Sesión
                                </a>
                                <form id="logout-form-web" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
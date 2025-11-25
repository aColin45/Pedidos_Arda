<nav class="app-header navbar navbar-expand bg-body">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="{{ route('web.index') }}" class="nav-link" target="_blank">
                    Tienda
                </a>
            </li>
            <li class="nav-item d-none d-md-block">
                <a href="{{ route('contacto.index.panel') }}" class="nav-link">Contacto</a>
            </li>
        </ul>
        <!--end::Start Navbar Links-->
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>
            @if(Auth::check())
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="d-none d-md-inline">{{Auth::user()->name}}</span>

                    {{-- ============================================= --}}
                    {{-- ||       ICONO JUNTO AL NOMBRE             || --}}
                    {{-- ============================================= --}}
                    @role('admin')
                    <i class="fas fa-user-shield ms-1 text-warning" title="Administrador"></i> {{-- Icono Admin --}}
                    @endrole
                    @role('agente-ventas')
                    <i class="fas fa-briefcase ms-1 text-info" title="Agente de Ventas"></i> {{-- Icono Agente --}}
                    @endrole
                    {{-- ============================================= --}}

                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        {{-- Imagen (si la tuvieras) --}}
                        {{-- <img src="{{asset('assets/img/user2-160x160.jpg')}}" class="rounded-circle shadow"
                        alt="User Image" /> --}}

                        {{-- ============================================= --}}
                        {{-- ||    ICONO Y ROL EN CABECERA AZUL         || --}}
                        {{-- ============================================= --}}
                        <p>
                            @role('admin')
                            <i class="fas fa-user-shield me-2"></i> Administrador
                            @endrole
                            @role('agente-ventas')
                            <i class="fas fa-briefcase me-2"></i> Agente de Ventas
                            @endrole
                            <small class="d-block mt-1">{{Auth::user()->name}}</small> {{-- Nombre debajo --}}
                        </p>
                        {{-- ============================================= --}}

                    </li>
                    <li class="user-footer">
                        {{-- ============================================= --}}
                        {{-- ||       ICONOS EN ITEMS DEL MENÚ          || --}}
                        {{-- ============================================= --}}
                        <a href="{{route('perfil.edit')}}" class="btn btn-default btn-flat">
                            <i class="fas fa-user-circle me-1"></i> Perfil
                        </a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="btn btn-default btn-flat float-end">
                            <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
                        </a>
                        {{-- ============================================= --}}
                    </li>
                    <form action="{{route('logout')}}" id="logout-form" method="post" class="d-none">
                        @csrf
                    </form>
                </ul>
            </li>
            @endif
        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
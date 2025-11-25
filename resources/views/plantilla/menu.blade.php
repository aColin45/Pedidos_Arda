<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img src="{{asset('assets/img/LOGO.png')}}" alt="arda-logo" 
                class="brand-image arda-logo opacity-75 shadow" style="width: 50px; height: 50px;" />
            <span class="brand-text fw-light arda-titulo">Pedidos - ARDA</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                {{-- 1. DASHBOARD --}}
                {{-- La corrección principal es agregar 'active' si la ruta es 'dashboard' --}}
                <li class="nav-item">
                    <a href="{{route('dashboard')}}" class="nav-link @if(request()->routeIs('dashboard')) active @endif" id="mnuDashboard">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                {{-- 2. PEDIDOS (para el listado) --}}
                {{-- Activo si la ruta es 'perfil.pedidos' --}}
                <li class="nav-item">
                    <a href="{{route('perfil.pedidos')}}" class="nav-link @if(request()->routeIs('perfil.pedidos')) active @endif" id="mnuPedidos">
                        <i class="nav-icon bi bi-bag-fill"></i>
                        <p>Pedidos</p>
                    </a>
                </li>

                {{-- ---------------------------------------------------- --}}
                
                {{-- 4. SEGURIDAD (Usuarios y Roles) --}}
                {{-- Clase 'menu-open' y 'active' para el menú desplegable --}}
                @php
                    $isSeguridadActive = request()->routeIs('usuarios.*') || request()->routeIs('roles.*');
                @endphp
                @canany(['user-list', 'rol-list'])
                <li class="nav-item @if($isSeguridadActive) menu-open @endif" id="mnuSeguridad">
                    <a href="#" class="nav-link @if($isSeguridadActive) active @endif">
                        <i class="nav-icon bi bi-shield-lock"></i> 
                        <p>Seguridad<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('user-list')
                        <li class="nav-item">
                            <a href="{{route('usuarios.index')}}" class="nav-link @if(request()->routeIs('usuarios.*')) active @endif" id="itemUsuario">
                                <i class="nav-icon bi bi-circle"></i><p>Usuarios</p>
                            </a>
                        </li>
                        @endcan
                        @can('rol-list')
                        <li class="nav-item">
                            <a href="{{route('roles.index')}}" class="nav-link @if(request()->routeIs('roles.*')) active @endif" id="itemRole">
                                <i class="nav-icon bi bi-circle"></i><p>Roles</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- ---------------------------------------------------- --}}

                {{-- 5. ALMACÉN/GESTIÓN (Productos) - Unificando tu código 1 y 2 --}}
                {{-- Clase 'menu-open' y 'active' para el menú desplegable --}}
                @php
                    $isGestionActive = request()->routeIs('productos.*') || request()->routeIs('clientes.*');
                @endphp
                @canany(['producto-list', 'cliente-list'])
                <li class="nav-item @if($isGestionActive) menu-open @endif" id="mnuGestion">
                    <a href="#" class="nav-link @if($isGestionActive) active @endif">
                        <i class="nav-icon bi bi-box-seam"></i> {{-- Icono de Gestión --}}
                        <p>Gestión<i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        
                        {{-- GESTIÓN DE CLIENTES --}}
                        @can('cliente-list')
                        <li class="nav-item">
                            <a href="{{route('clientes.index')}}" class="nav-link @if(request()->routeIs('clientes.*')) active @endif" id="itemClientes">
                                <i class="nav-icon bi bi-person-lines-fill"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        @endcan

                        {{-- GESTIÓN DE PRODUCTOS --}}
                        @can('producto-list')
                        <li class="nav-item">
                            <a href="{{route('productos.index')}}" class="nav-link @if(request()->routeIs('productos.*')) active @endif" id="itemProducto">
                                <i class="nav-icon bi bi-box"></i>
                                <p>Productos</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                
            </ul>
            </nav>
    </div>
    </aside>
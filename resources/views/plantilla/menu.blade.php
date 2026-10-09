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
                
                {{-- 1. DASHBOARD (Suele ser público para todos los logueados) --}}
                <li class="nav-item">
                    <a href="{{route('dashboard')}}" class="nav-link @if(request()->routeIs('dashboard')) active @endif" id="mnuDashboard">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- 2. PROSPECTOS --}}
                @can('prospecto-list')
                <li class="nav-item">
                    <a href="{{ route('prospectos.index') }}" class="nav-link @if(request()->routeIs('prospectos.*')) active @endif" id="navProspectos">
                        <i class="nav-icon fas fa-user-tag"></i>
                        <p>Prospectos</p>
                    </a>
                </li>
                @endcan

                {{-- 2.1 BITÁCORA DE VISITAS --}}
                @can('visita-list')
                <li class="nav-item">
                    <a href="{{ route('visitas.index') }}" class="nav-link @if(request()->routeIs('visitas.*')) active @endif" id="navVisitas">
                        <i class="nav-icon fas fa-route"></i>
                        <p>Bitácora de Visitas</p>
                    </a>
                </li>
                @endcan

                {{-- 3. ALTAS DE CLIENTES --}}
                @can('alta-list')
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('altas.*')) active @endif" href="{{ route('altas.index') }}">
                        <i class="nav-icon fas fa-user-plus"></i> 
                        <p>Altas de Clientes</p>
                    </a>
                </li>
                @endcan

                {{-- 4. COTIZACIONES --}}
                @can('cotizacion-list')
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('cotizaciones.*')) active @endif" href="{{ route('cotizaciones.index') }}">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i> 
                        <p>Cotizaciones</p>
                    </a>
                </li>
                @endcan
                
                {{-- 5. PEDIDOS --}}
                @can('pedido-list')
                <li class="nav-item">
                    <a href="{{route('perfil.pedidos')}}" class="nav-link @if(request()->routeIs('perfil.pedidos')) active @endif" id="mnuPedidos">
                        <i class="nav-icon bi bi-bag-fill"></i>
                        <p>Pedidos</p>
                    </a>
                </li>
                @endcan

                {{-- 6. DEVOLUCIONES --}}
                @can('devolucion-list')
                <li class="nav-item {{ Request::is('devoluciones*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('devoluciones.index') }}">
                        <i class="fas fa-fw fa-undo-alt" style="margin-right: 8px;"></i>
                        <span>Devoluciones</span>
                    </a>
                </li>
                @endcan

                {{-- ---------------------------------------------------- --}}
                
                {{-- 7. SEGURIDAD (Usuarios y Roles) --}}
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

                {{-- 8. GESTIÓN (Clientes, Productos, Inventarios y Desarrollos) --}}
                @php
                    // Actualizamos la variable para que la carpeta se abra si estamos en cualquiera de los 4 módulos
                    $isGestionActive = request()->routeIs('productos.*') || request()->routeIs('clientes.*') || request()->routeIs('desarrollos.*') || request()->routeIs('inventarios.*');
                @endphp
                @canany(['producto-list', 'cliente-list', 'inventario-list', 'desarrollo-list'])
                <li class="nav-item @if($isGestionActive) menu-open @endif" id="mnuGestion">
                    <a href="#" class="nav-link @if($isGestionActive) active @endif">
                        <i class="nav-icon bi bi-box-seam"></i>
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

                        {{-- GESTIÓN DE INVENTARIOS --}}
                        @can('inventario-list')
                        <li class="nav-item">
                            <a href="{{route('inventarios.index')}}" class="nav-link @if(request()->routeIs('inventarios.*')) active @endif" id="itemInventario">
                                <i class="nav-icon bi bi-boxes"></i>
                                <p>Gestión de Inventarios</p>
                            </a>
                        </li>
                        @endcan

                        {{-- PRODUCTOS NUEVOS Y DESARROLLOS --}}
                        @can('desarrollo-list')
                        <li class="nav-item">
                            <a href="{{ route('desarrollos.index') }}" class="nav-link @if(request()->routeIs('desarrollos.*')) active @endif" id="navDesarrollos">
                                <i class="nav-icon fas fa-lightbulb"></i>
                                <p>Productos Nuevos / Desarrollos</p>
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
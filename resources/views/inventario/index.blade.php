@extends('plantilla.app')

@section('contenido')
<div class="app-content pb-5">
    <div class="container-fluid">

        {{-- Encabezado --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-2">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold"><i class="fas fa-boxes text-primary mr-2"></i>Gestión de Inventarios</h1>
                <p class="text-muted small mb-0">
                    @if(auth()->user()->hasPermissionTo('inventario-import') || auth()->user()->hasPermissionTo('inventario-edit'))
                        Sincronización masiva con CONTPAQi y ajustes de stock en vivo.
                    @else
                        Consulta de existencias globales.
                    @endif
                </p>
            </div>
        </div>

        {{-- Alertas del Sistema --}}
        @if(session('mensaje'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('mensaje') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger shadow-sm border-0">
                <ul class="mb-0">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- ======================================================= --}}
        {{-- SÓLO ADMINS/ALMACÉN: PANELES DE CARGA Y REVERSIÓN       --}}
        {{-- ======================================================= --}}
        @can('inventario-import')
            
            <div class="row">
                {{-- COLUMNA IZQUIERDA: IMPORTAR Y VACIAR --}}
                <div class="col-xl-8">
                    {{-- TARJETA: CARGA MASIVA EXCEL --}}
                    <div class="card shadow border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #1cc88a !important;">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-file-excel mr-2"></i>Sincronización Masiva (Excel CONTPAQi)</h6>
                        </div>
                        <div class="card-body bg-light">
                            <form action="{{ route('inventarios.importar') }}" method="POST" enctype="multipart/form-data" class="form-confirmar" data-title="¿Iniciar Sincronización?" data-text="Esto actualizará el stock de todos los productos incluidos en el Excel para el almacén seleccionado." data-icon="info" data-color="#1cc88a" data-btn-text="Sí, sincronizar">
                                @csrf
                                <div class="row align-items-center">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="font-weight-bold text-dark mb-1">1. Selecciona el Almacén</label>
                                        <select name="almacen_id" class="form-select shadow-sm" required>
                                            <option value="">-- Elige un almacén --</option>
                                            @foreach($almacenes as $almacen)
                                                <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 mb-3 mb-md-0">
                                        <label class="font-weight-bold text-dark mb-1">2. Sube el Excel (.xlsx, .xls)</label>
                                        <input type="file" name="archivo_excel" class="form-control shadow-sm" accept=".xlsx, .xls, .csv" required>
                                    </div>
                                    <div class="col-md-3 mt-3 mt-md-0 text-md-end">
                                        <label class="d-none d-md-block mb-1">&nbsp;</label>
                                        <button type="submit" class="btn btn-success fw-bold shadow-sm w-100">
                                            <i class="fas fa-sync-alt mr-2"></i>Sincronizar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- TARJETA: VACIAR ALMACÉN --}}
                    <div class="card shadow border-0 mb-4" style="border-radius: 12px; border-left: 4px solid #e74a3b;">
                        <div class="card-body py-2 d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <div>
                                <h6 class="m-0 font-weight-bold text-danger mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>¿Subiste un archivo equivocado?</h6>
                            </div>
                            <form action="{{ route('inventarios.vaciar') }}" method="POST" class="form-confirmar mt-2 mt-md-0 d-flex gap-2" data-title="¿Vaciar Almacén?" data-text="Esto borrará todo el inventario registrado en este almacén." data-icon="warning" data-color="#e74a3b" data-btn-text="Sí, vaciar inventario">
                                @csrf
                                <select name="almacen_id_vaciar" class="form-select form-select-sm shadow-sm" style="min-width: 180px;" required>
                                    <option value="">-- Elige un almacén --</option>
                                    @foreach($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-danger fw-bold shadow-sm text-nowrap">
                                    <i class="fas fa-trash-alt mr-1"></i>Vaciar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: RESTRICCIONES (Solo para SuperAdmin / Admin) --}}
                @can('inventario-config')
                <div class="col-xl-4">
                    <div class="card shadow border-0 mb-4 h-100" style="border-radius: 12px; border-top: 4px solid #f6c23e !important;">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-ban mr-2"></i>Filtro de Palabras</h6>
                            <small class="text-muted">Ignorar del Excel (Ej. DAÑADO, OBSOLETO)</small>
                        </div>
                        <div class="card-body bg-light">
                            <form action="{{ route('inventarios.restricciones.store') }}" method="POST" class="d-flex gap-2 mb-3">
                                @csrf
                                <input type="text" name="palabra" class="form-control form-control-sm shadow-sm" placeholder="Nueva palabra..." required>
                                <button type="submit" class="btn btn-sm btn-warning fw-bold shadow-sm"><i class="fas fa-plus"></i></button>
                            </form>
                            <div class="d-flex flex-wrap gap-1" style="max-height: 100px; overflow-y: auto;">
                                @forelse($restricciones as $restriccion)
                                    <span class="badge bg-dark d-flex align-items-center shadow-sm py-1 px-2">
                                        {{ $restriccion->palabra }}
                                        <form action="{{ route('inventarios.restricciones.destroy', $restriccion->id) }}" method="POST" class="d-inline ms-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 m-0 border-0" title="Eliminar"><i class="fas fa-times"></i></button>
                                        </form>
                                    </span>
                                @empty
                                    <span class="text-muted small w-100 text-center mt-2">Sin restricciones.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

            </div>
        @endcan
        {{-- ======================================================= --}}

        {{-- TARJETA: TABLA DE INVENTARIO --}}
        <div class="card shadow border-0" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex flex-column flex-xl-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary mb-3 mb-xl-0"><i class="fas fa-boxes mr-2"></i>Catálogo de Existencias</h6>
                
                {{-- Buscador en Vivo, Almacén y ORDENAMIENTO --}}
                <form action="{{ route('inventarios.index') }}" method="GET" id="form-busqueda-inventario" class="d-flex w-100 flex-column flex-md-row gap-2 justify-content-xl-end" style="max-width: 900px;">
                    
                    {{-- NUEVO: Selector de Ordenamiento --}}
                    <div class="input-group shadow-sm" style="min-width: 200px;">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-sort-amount-down"></i></span>
                        <select name="orden" id="filtro_orden" class="form-select border-start-0 text-secondary fw-bold">
                            <option value="">Orden predeterminado</option>
                            <option value="mayor" {{ request('orden') == 'mayor' ? 'selected' : '' }}>Mayor Stock Primero</option>
                            <option value="menor" {{ request('orden') == 'menor' ? 'selected' : '' }}>Menor Stock Primero</option>
                        </select>
                    </div>

                    {{-- Selector de Almacén (Sólo visible para Admins/Almacén) --}}
                    @can('inventario-import')
                    <div class="input-group shadow-sm" style="min-width: 220px;">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-warehouse"></i></span>
                        <select name="filtro_almacen" id="filtro_almacen" class="form-select border-start-0">
                            <option value="">Todos los almacenes</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ request('filtro_almacen') == $almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endcan

                    {{-- Caja de Texto (Visible para todos) --}}
                    <div class="input-group shadow-sm" style="min-width: 250px;">
                        <input type="text" name="buscar" id="input_buscar" class="form-control border-end-0" placeholder="Buscar código/nombre..." value="{{ request('buscar') }}" autocomplete="off">
                        <span class="input-group-text bg-white text-primary border-start-0" id="icono-busqueda"><i class="fas fa-search"></i></span>
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive" id="tabla-inventario-container" style="transition: opacity 0.2s ease;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-gray-700">
                            <tr>
                                <th class="border-0 pl-4">Código</th>
                                <th class="border-0">Producto</th>
                                <th class="border-0 text-center">Stock Global</th>
                                
                                @can('inventario-import')
                                    <th class="border-0">Desglose por Almacén</th>
                                    <th class="border-0 text-center pr-4">Acciones</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $producto)
                            <tr>
                                <td class="pl-4 font-weight-bold text-secondary">{{ $producto->codigo }}</td>
                                <td><div class="text-dark fw-bold" style="max-width: 300px; white-space: normal;">{{ $producto->nombre }}</div></td>
                                <td class="text-center">
                                    <span class="badge {{ $producto->stock_calculado > 0 ? 'bg-success' : 'bg-danger' }} fs-6 shadow-sm">
                                        {{ number_format($producto->stock_calculado, 2) }}
                                    </span>
                                </td>
                                
                                @can('inventario-import')
                                    <td>
                                        @if($producto->almacenes->count() > 0 && $producto->stock_calculado > 0)
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($producto->almacenes as $alm)
                                                    @if($alm->pivot->cantidad > 0)
                                                        <span class="badge bg-light text-dark border shadow-sm" style="font-size: 0.8rem;">
                                                            <i class="fas fa-warehouse text-muted mr-1"></i>{{ $alm->nombre }}: <strong class="text-primary">{{ number_format($alm->pivot->cantidad, 2) }}</strong>
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small">Sin existencias registradas</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center pr-4">
                                        @can('inventario-edit')
                                        <button class="btn btn-sm btn-outline-warning fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjuste-{{ $producto->id }}">
                                            <i class="fas fa-edit mr-1"></i>Ajustar
                                        </button>
                                        @endcan
                                    </td>

                                    {{-- MODAL PARA AJUSTE MANUAL --}}
                                    @can('inventario-edit')
                                    <div class="modal fade" id="modalAjuste-{{ $producto->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                                                <div class="modal-header bg-light border-0">
                                                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-sliders-h text-warning mr-2"></i>Ajuste Manual de Stock</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('inventarios.manual') }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                                        <div class="mb-3">
                                                            <label class="text-muted small mb-1">Producto</label>
                                                            <div class="font-weight-bold text-dark">{{ $producto->codigo }} - {{ $producto->nombre }}</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="font-weight-bold mb-1">Almacén a afectar</label>
                                                            <select name="almacen_id" class="form-select" required>
                                                                @foreach($almacenes as $almacen)
                                                                    @php
                                                                        $stockActual = $producto->almacenes->firstWhere('id', $almacen->id);
                                                                        $cantidadActual = $stockActual ? $stockActual->pivot->cantidad : 0;
                                                                    @endphp
                                                                    <option value="{{ $almacen->id }}">{{ $almacen->nombre }} (Actual: {{ number_format($cantidadActual, 2) }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="font-weight-bold mb-1">Nueva Cantidad Total</label>
                                                            <input type="number" step="0.01" min="0" name="cantidad" class="form-control form-control-lg text-center fw-bold text-primary" required placeholder="0.00">
                                                            <small class="text-danger mt-1 d-block"><i class="fas fa-info-circle mr-1"></i>Esta cantidad reemplazará el valor anterior en el almacén seleccionado.</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 bg-light">
                                                        <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-warning fw-bold shadow-sm"><i class="fas fa-save mr-1"></i>Guardar Ajuste</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endcan
                                @endcan
                                
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasPermissionTo('inventario-import') ? '5' : '3' }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 text-gray-300 d-block"></i>No se encontraron productos.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3" id="paginacion-inventario-container">
                <div class="d-flex justify-content-center">
                    {{ $productos->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('input_buscar');
    const selectAlmacen = document.getElementById('filtro_almacen'); 
    const selectOrden = document.getElementById('filtro_orden'); // NUEVO
    const formBusqueda = document.getElementById('form-busqueda-inventario');
    
    const tablaContainer = document.getElementById('tabla-inventario-container');
    const paginacionContainer = document.getElementById('paginacion-inventario-container');
    const iconoBusqueda = document.getElementById('icono-busqueda');
    
    let timeoutId;

    function actualizarVista(urlObj) {
        tablaContainer.style.opacity = '0.4';
        iconoBusqueda.innerHTML = '<i class="fas fa-spinner fa-spin text-warning"></i>';

        fetch(urlObj.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            document.querySelectorAll('body > .modal[id^="modalAjuste-"]').forEach(m => m.remove());

            if(doc.getElementById('tabla-inventario-container') && doc.getElementById('paginacion-inventario-container')) {
                tablaContainer.innerHTML = doc.getElementById('tabla-inventario-container').innerHTML;
                paginacionContainer.innerHTML = doc.getElementById('paginacion-inventario-container').innerHTML;
                
                const nuevosModales = tablaContainer.querySelectorAll('.modal');
                nuevosModales.forEach(modal => {
                    document.body.appendChild(modal);
                });
            }

            tablaContainer.style.opacity = '1';
            iconoBusqueda.innerHTML = '<i class="fas fa-search"></i>';
            window.history.replaceState({}, '', urlObj.toString());
        })
        .catch(error => {
            console.error('Error AJAX:', error);
            tablaContainer.style.opacity = '1';
            iconoBusqueda.innerHTML = '<i class="fas fa-search"></i>';
        });
    }

    function realizarBusquedaEnVivo() {
        const url = new URL(formBusqueda.action);
        url.searchParams.set('buscar', inputBuscar.value);
        
        if (selectAlmacen) { url.searchParams.set('filtro_almacen', selectAlmacen.value); }
        if (selectOrden) { url.searchParams.set('orden', selectOrden.value); }
        
        url.searchParams.set('page', '1'); 
        actualizarVista(url);
    }

    if (inputBuscar) {
        inputBuscar.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(realizarBusquedaEnVivo, 400);
        });
    }

    if (selectAlmacen) { selectAlmacen.addEventListener('change', realizarBusquedaEnVivo); }
    if (selectOrden) { selectOrden.addEventListener('change', realizarBusquedaEnVivo); }

    if (formBusqueda) {
        formBusqueda.addEventListener('submit', function(e) {
            e.preventDefault();
            realizarBusquedaEnVivo();
        });
    }

    if (paginacionContainer) {
        paginacionContainer.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link) {
                e.preventDefault(); 
                const url = new URL(link.href);
                actualizarVista(url);
            }
        });
    }
});
</script>
@endpush
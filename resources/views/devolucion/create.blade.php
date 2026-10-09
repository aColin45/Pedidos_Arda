@extends('plantilla.app')

@section('contenido')
<div class="container-fluid">
    {{-- Título y Volver --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Nueva Solicitud de Devolución</h1>
        <a href="{{ route('devoluciones.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al listado
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-left-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- IMPORTANTE: Aquí está el enctype para que funcionen las fotos --}}
    <form action="{{ route('devoluciones.store') }}" method="POST" id="formDevolucion" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            {{-- ========================================== --}}
            {{-- SECCIÓN IZQUIERDA: DATOS GENERALES --}}
            {{-- ========================================== --}}
            <div class="col-xl-4 col-lg-5">
                
                {{-- Tarjeta 1: Cliente --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white border-bottom-primary">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-tag mr-1"></i> Datos del Cliente</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold small text-uppercase">Cliente</label>
                            <select name="cliente_id" id="cliente_selector" class="form-control" required>
                                <option value="">-- Buscar Cliente --</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" 
                                            data-codigo="{{ $c->codigo }}"
                                            data-agente="{{ $c->agente->name ?? 'Sin Asignar' }}"
                                            data-descuento="{{ $c->descuento ?? 0 }}">
                                        {{ $c->nombre }} ({{ $c->descuento ?? 0 }}% Desc)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-6 mb-3">
                                <label class="small text-gray-500 font-weight-bold">Código Cliente</label>
                                <input type="text" id="view_codigo_cliente" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small text-gray-500 font-weight-bold">Agente</label>
                                <input type="text" id="view_agente" class="form-control bg-light" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tarjeta 2: Factura y Motivo --}}
                <div class="card shadow mb-4 border-left-danger">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-file-invoice mr-1"></i> Origen de Devolución</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold small text-uppercase text-danger">Factura a la que pertenece</label>
                            <input type="text" 
                                   name="factura_origen" 
                                   class="form-control border-left-danger @error('factura_origen') is-invalid @enderror" 
                                   placeholder="Ej: F-2309" 
                                   value="{{ old('factura_origen') }}" 
                                   required>
                            
                            @error('factura_origen')
                                <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Indique el folio de factura física.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-uppercase">Motivo Devolución</label>
                            <select name="motivo_devolucion" class="form-control" required>
                                @foreach($motivos as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ========================================== --}}
            {{-- SECCIÓN DERECHA: EXTRAS Y PRODUCTOS --}}
            {{-- ========================================== --}}
            <div class="col-xl-8 col-lg-7">
                
                {{-- Fila interna: Observaciones y Evidencia lado a lado --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white">
                                <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-comment-alt mr-1"></i> Observaciones</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="comentarios_adicionales" class="form-control" rows="3" placeholder="Detalles adicionales sobre el material..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card shadow mb-4 border-left-info">
                            <div class="card-header py-3 bg-white">
                                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-camera mr-1"></i> Evidencia (Opcional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-0">
                                    <label class="small text-muted mb-2">Adjuntar fotos del material (JPG/PNG)</label>
                                    {{-- Un toque de CSS para que el input file se vea más grueso y alineado con el textarea --}}
                                    <input type="file" name="evidencias[]" class="form-control" accept="image/jpeg, image/png, image/jpg" multiple style="padding-bottom: 35px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tarjeta 3: Productos --}}
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-white border-bottom-success d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-boxes mr-1"></i> Detalle de Productos</h6>
                        <button type="button" class="btn btn-success btn-sm shadow-sm" id="btnAdd">
                            <i class="fas fa-plus"></i> Agregar Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="tablaProductos">
                                <thead class="bg-light text-gray-700">
                                    <tr>
                                        <th width="20%">Código</th>
                                        <th width="35%">Descripción</th>
                                        <th width="15%" class="text-right">Precio Unit.</th>
                                        <th width="15%" class="text-center">Cant.</th>
                                        <th width="15%" class="text-right">Total</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyProductos">
                                    <tr class="fila-base">
                                        <td>
                                            <input type="text" class="form-control form-control-sm input-codigo" placeholder="Buscar..." required>
                                            <input type="hidden" name="productos[0][producto_id]" class="input-id">
                                            <div class="invalid-feedback">No existe</div>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm input-desc bg-light border-0" readonly tabindex="-1"></td>
                                        <td><input type="text" class="form-control form-control-sm input-precio bg-light border-0 text-right" readonly tabindex="-1"></td>
                                        <td>
                                            <input type="number" step="any" name="productos[0][cantidad]" class="form-control form-control-sm input-cant text-center font-weight-bold" value="0" required>
                                        </td>
                                        <td class="text-right align-middle font-weight-bold text-dark input-total">$0.00</td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-del border-0" disabled><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-100">
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bold text-uppercase pt-3">Totales:</td>
                                        <td class="text-center font-weight-bold h5 text-primary pt-3" id="totalCantidades">0</td>
                                        <td class="text-right font-weight-bold h4 text-success pt-3" id="totalDinero">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-right">
                        {{-- Botón Guardar más llamativo --}}
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5">
                            <i class="fas fa-save mr-2"></i> Guardar Solicitud
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- SCRIPT JAVASCRIPT INTACTO (Sin cambios en tu lógica funcional) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Cliente -> Agente/Código
    const clienteSelect = document.getElementById('cliente_selector');
    const viewCodigo = document.getElementById('view_codigo_cliente');
    const viewAgente = document.getElementById('view_agente');

    clienteSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            viewCodigo.value = opt.getAttribute('data-codigo');
            viewAgente.value = opt.getAttribute('data-agente');
        } else {
            viewCodigo.value = ''; viewAgente.value = '';
        }
    });

    // 2. Tabla Productos
    let rowIdx = 0;
    const tbody = document.getElementById('tbodyProductos');
    
    // Agregar Fila
    document.getElementById('btnAdd').addEventListener('click', function() {
        rowIdx++;
        const row = tbody.querySelector('.fila-base').cloneNode(true);
        row.querySelector('.input-codigo').value = '';
        row.querySelector('.input-codigo').classList.remove('is-valid', 'is-invalid');
        row.querySelector('.input-desc').value = '';
        row.querySelector('.input-precio').value = '';
        row.querySelector('.input-total').innerText = '$0.00';
        
        row.querySelector('.input-id').name = `productos[${rowIdx}][producto_id]`;
        row.querySelector('.input-cant').name = `productos[${rowIdx}][cantidad]`;
        row.querySelector('.input-cant').value = 0;
        
        row.querySelector('.btn-del').disabled = false;
        tbody.appendChild(row);
        bindEvents(row);
    });

    function bindEvents(row) {
        const inputCodigo = row.querySelector('.input-codigo');
        const inputCant = row.querySelector('.input-cant');
        const btnDel = row.querySelector('.btn-del');
        let timer = null;

        // AJAX Búsqueda
        inputCodigo.addEventListener('keyup', function() {
            clearTimeout(timer);
            const val = this.value;
            if(!val) return;
            timer = setTimeout(() => {
                fetch(`/api/producto-info/${val}`).then(r=>r.json()).then(d => {
                    if(d.success) {
                        row.querySelector('.input-id').value = d.id;
                        row.querySelector('.input-desc').value = d.nombre;
                        row.querySelector('.input-precio').value = d.precio;
                        this.classList.remove('is-invalid'); this.classList.add('is-valid');
                        calcTotales();
                    } else {
                        row.querySelector('.input-id').value = '';
                        row.querySelector('.input-desc').value = 'No encontrado';
                        row.querySelector('.input-precio').value = 0;
                        this.classList.add('is-invalid');
                    }
                });
            }, 400);
        });

        inputCant.addEventListener('input', calcTotales);
        btnDel.addEventListener('click', function() { row.remove(); calcTotales(); });
    }

    function calcTotales() {
        let sumC = 0, sumD = 0;
        
        // 1. Buscamos usando tu ID exacto: cliente_selector
        const selectCliente = document.getElementById('cliente_selector');
        let descuentoCliente = 0;
        
        // Verificamos que sí haya un cliente seleccionado
        if (selectCliente && selectCliente.selectedIndex >= 0) {
            const opcionSeleccionada = selectCliente.options[selectCliente.selectedIndex];
            descuentoCliente = parseFloat(opcionSeleccionada.getAttribute('data-descuento')) || 0;
        }

        // 2. Recorrer los productos y aplicar la matemática
        document.querySelectorAll('#tbodyProductos tr').forEach(tr => {
            const inputPrecio = tr.querySelector('.input-precio');
            const inputCant = tr.querySelector('.input-cant');
            
            if (inputPrecio && inputCant) {
                const precioLista = parseFloat(inputPrecio.value) || 0;
                const cantidad = parseFloat(inputCant.value) || 0;
                
                // Calculamos el descuento
                const precioNeto = precioLista - (precioLista * (descuentoCliente / 100));

                tr.querySelector('.input-total').innerText = `$${(precioNeto * cantidad).toFixed(2)}`;
                sumC += cantidad; 
                sumD += (precioNeto * cantidad);
            }
        });
        
        const spanTotalC = document.getElementById('totalCantidades');
        const spanTotalD = document.getElementById('totalDinero');
        if(spanTotalC) spanTotalC.innerText = sumC;
        if(spanTotalD) spanTotalD.innerText = `$${sumD.toFixed(2)}`;
    }

    // 3. Escuchamos cuando cambies de cliente (usando tu ID exacto)
    const selector = document.getElementById('cliente_selector');
    if (selector) {
        selector.addEventListener('change', calcTotales);
    }

    // ===============================================================
    // 4. DESPERTAR LA PRIMERA FILA 
    // ===============================================================
    // Esto toma todas las filas que ya existan en el HTML y les inyecta los "poderes"
    document.querySelectorAll('#tbodyProductos tr').forEach(tr => {
        if (typeof bindEvents === 'function') {
            bindEvents(tr);
        } else {
            // Código de respaldo por si tu función bindEvents tiene otro nombre
            const inputCod = tr.querySelector('.input-codigo');
            if (inputCod && typeof bindBuscarProducto === 'function') bindBuscarProducto(inputCod);
            const inputCant = tr.querySelector('.input-cant');
            if (inputCant) inputCant.addEventListener('input', calcTotales);
        }
    });
});
</script>
@endsection
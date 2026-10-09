@extends('web.app')
@section('contenido')
<section class="py-5">
    <div class="container px-4 px-lg-12 my-5">
        <h2 class="fw-bold mb-4">Detalle de su Pedido</h2>
        <div class="row">
            {{-- ======================================================= --}}
            {{-- ||       COLUMNA IZQUIERDA: CARRITO DE COMPRAS       || --}}
            {{-- ======================================================= --}}
            <div class="col-lg-8">
                <div class="card mb-4">
                    {{-- Encabezado visible solo en pantallas medianas y grandes --}}
                    <div class="card-header bg-light d-none d-md-block">
                        <div class="row">
                            <div class="col-md-3"><strong>Producto</strong></div>
                            <div class="col-md-2 text-center"><strong>Precio Base</strong></div>
                            <div class="col-md-2 text-center text-success"><strong>Precio Final</strong></div>
                            <div class="col-md-1 text-center"><strong>Inner</strong></div>
                            <div class="col-md-2 text-center"><strong>Cantidad</strong></div>
                            <div class="col-md-2 text-end"><strong>Subtotal</strong></div>
                        </div>
                    </div>

                    <div class="card-body" id="cartItems">
                        @forelse($carrito as $id => $item)
                        {{-- Estructura de Fila para cada item --}}
                        <div class="row align-items-center mb-3 cart-item border-bottom pb-3">
                            
                            {{-- Columna Producto (Siempre visible) --}}
                            <div class="col-12 col-md-3 mb-2 mb-md-0"> 
                                <div class="d-flex align-items-center">
                                    <img src="{{ !empty($item['imagen']) ? asset('uploads/productos/' . $item['imagen']) : asset('assets/img/placeholder.png') }}"
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"
                                         alt="{{ $item['nombre'] ?? 'Producto' }}">
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ $item['nombre'] ?? 'N/A' }}</h6>
                                        <small class="text-muted d-block">{{ $item['codigo'] ?? 'N/A' }}</small>
                                        @if (!($item['aplica_iva'] ?? true))
                                        <span class="badge bg-secondary text-white mt-1">No aplica IVA</span>
                                        @endif
                                        @if (($item['inner'] ?? 1) > 1)
                                        <span class="badge bg-warning text-dark mt-1">Múltiplo de {{ $item['inner'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Columnas Precio e Inner (Agrupadas en móvil) --}}
                            {{-- Columna Precio de Lista --}}
                            <div class="col-6 col-md-2 text-center text-md-center">
                                <small class="text-muted d-md-none">Precio Base:</small>
                                <span class="text-muted text-decoration-line-through d-block d-md-inline base-price" data-price="{{ $item['precio'] ?? 0 }}">
                                    ${{ number_format($item['precio'] ?? 0, 2) }}
                                </span>
                            </div>

                            {{-- NUEVA Columna Precio con Descuento --}}
                            <div class="col-6 col-md-2 text-center text-md-center">
                                <small class="text-muted d-md-none text-success">Precio Final:</small>
                                <span class="fw-bold text-success d-block d-md-inline discounted-price">
                                    ${{ number_format($item['precio'] ?? 0, 2) }}
                                </span>
                            </div>

                            {{-- Columna Inner --}}
                            <div class="col-6 col-md-1 text-center text-md-center">
                                <small class="text-muted d-md-none">Inner:</small> 
                                <span class="text-muted d-block d-md-inline">{{ $item['inner'] ?? 1 }}</span>
                            </div>

                            {{-- Columna Cantidad --}}
                            <div class="col-6 col-md-2 d-flex justify-content-center align-items-center mt-2 mt-md-0">
                                <small class="text-muted d-md-none me-2">Cant:</small> 
                                <div class="input-group input-group-sm" style="max-width: 120px;">
                                    <a class="btn btn-outline-secondary" href="{{ route('carrito.restar', ['producto_id' => $id]) }}"> - </a>
                                    <input type="number" class="form-control text-center px-1 item-qty" 
                                        value="{{ $item['cantidad'] ?? 0 }}" 
                                        min="{{ $item['inner'] ?? 1 }}" 
                                        step="{{ $item['inner'] ?? 1 }}"
                                        onchange="validarCantidad(this, '{{ $id }}', {{ $item['inner'] ?? 1 }})">
                                    <a href="{{ route('carrito.sumar', ['producto_id' => $id]) }}" class="btn btn-outline-secondary"> + </a>
                                </div>
                            </div>

                            {{-- Columna Subtotal y Eliminar --}}
                            <div class="col-12 col-md-2 d-flex align-items-center justify-content-end mt-2 mt-md-0">
                                <small class="text-muted d-md-none me-2">Subt:</small> 
                                <div class="text-end me-2 me-md-3">
                                    <span class="fw-bold subtotal">
                                        ${{ number_format(($item['precio'] ?? 0) * ($item['cantidad'] ?? 0), 2) }}
                                    </span>
                                </div>
                                <a class="btn btn-sm btn-outline-danger" href="{{ route('carrito.eliminar', $id) }}">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>

                        </div> {{-- Fin .row .cart-item --}}
                        @empty
                        <div class="text-center">
                            <p>Tu carrito esta vacío</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- MENSAJES DE SESIÓN --}}
                    @if (session('mensaje'))
                    <div class="alert alert-success alert-dismissible fade show mt-3 mx-3" role="alert">
                        {{ session('mensaje') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3 mx-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                    @endif

                    {{-- PIE DE LA TABLA (Vaciar Carrito) --}}
                    <div class="card-footer bg-light">
                        <div class="row">
                            <div class="col text-end">
                                <a class="btn btn-outline-danger me-2" href="{{route('carrito.vaciar')}}">
                                    <i class="bi bi-x-circle me-1"></i>Vaciar carrito
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================= --}}
            {{-- ||       COLUMNA DERECHA: RESUMEN Y CONTROLES        || --}}
            {{-- ======================================================= --}}
            <div class="col-lg-4">
                
                {{-- CARD DE RESUMEN PRINCIPAL --}}
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Resumen del Pedido</h5>
                    </div>

                    {{-- Data attributes con valores iniciales para JS --}}
                    <div class="card-body" id="resumen-pedido-card" 
                         data-subtotal-bruto="{{ $subtotalBruto ?? 0 }}"
                         data-subtotal-gravable="{{ $subtotalNetoGravable ?? 0 }}"
                         data-subtotal-exento="{{ $subtotalNetoExento ?? 0 }}">

                        {{-- SELECTOR DE CLIENTE --}}
                        <div class="form-group mb-3">
                            <label for="cliente_id" class="fw-bold mb-1">Cliente / Asignación:</label>
                            <select name="cliente_id" id="cliente_id" form="form-pedido" 
                                    class="form-select @error('cliente_id') is-invalid @enderror" required>
                                <option value="">-- Seleccione un cliente --</option>
                                @forelse($clientesParaSelector as $cliente)
                                <option value="{{ $cliente->id }}" 
                                        data-descuento="{{ $cliente->descuento }}"
                                        data-codigo="{{ $cliente->codigo }}" 
                                        data-nombre="{{ strtoupper($cliente->nombre) }}"
                                        {{ old('cliente_id', session('current_client_id')) == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre }}
                                </option>
                                @empty
                                <option value="" disabled>No hay clientes disponibles.</option>
                                @endforelse
                            </select>
                            @error('cliente_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- SELECTOR MANUAL DE DESCUENTO --}}
                        <div id="div-descuento-manual" class="mb-3" style="display: none; background-color: #f0f8ff; padding: 10px; border-radius: 5px; border: 1px solid #b6d4fe;">
                            <label for="descuento_manual" class="fw-bold text-primary mb-1">
                                <i class="bi bi-percent"></i> Descuento Cotización:
                            </label>
                            <select id="descuento_manual" name="descuento_manual" form="form-pedido" class="form-select form-select-sm">
                                <option value="40" selected>40% (Estándar)</option>
                                <option value="36">36%</option>
                                <option value="34">34%</option>
                                <option value="32">32%</option>
                                <option value="0">0% (Sin descuento)</option>
                            </select>
                            <small class="text-muted" style="font-size: 0.8em;">Modifique este valor para ajustar la cotización del cliente de mostrador.</small>
                        </div>

                        {{-- TOTALES --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal Bruto</span>
                            <span>${{ number_format($subtotalBruto ?? 0, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <strong id="resumen-descuento-texto">Descuento (0%)</strong>
                            <strong id="resumen-descuento-monto">-$0.00</strong>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted"><small>Subtotal Neto Gravable</small></span>
                            <span class="fw-bold" id="resumen-subtotal-neto-gravable">${{ number_format($subtotalNetoGravable ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><small>Subtotal Neto Exento</small></span>
                            <span class="fw-bold" id="resumen-subtotal-neto-exento">${{ number_format($subtotalNetoExento ?? 0, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (16%)</span>
                            <span id="resumen-iva-monto">+${{ number_format($montoIVA ?? 0, 2) }}</span>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total Final</strong>
                            <strong id="orderTotal" class="fs-5">${{ number_format($totalFinal ?? 0, 2) }}</strong>
                        </div>

                        {{-- FORMULARIO REALIZAR PEDIDO (POST) --}}
                        <form action="{{route('pedido.realizar')}}" method="POST" id="form-pedido">
                            @csrf

                            {{-- CAMPO: DIRECCIÓN DE ENTREGA --}}
                            <div class="form-group mb-3">
                                <label for="direccion_entrega" class="fw-bold mb-1 text-dark">
                                    <i class="fas fa-map-marker-alt text-danger"></i> Dirección de Entrega:
                                </label>
                                <textarea name="direccion_entrega" id="direccion_entrega" class="form-control" rows="2" placeholder="Calle, Número, Colonia, C.P., Ciudad, Estado...">{{ old('direccion_entrega', session('direccion_entrega', '')) }}</textarea>
                            </div>

                            {{-- Comentarios --}}
                            <div class="form-group mb-3">
                                <label for="comentarios" class="fw-bold mb-1">Comentarios (Opcional):</label>
                                <textarea name="comentarios" id="comentarios" class="form-control" rows="2"
                                    placeholder="Instrucciones especiales...">{{ old('comentarios') }}</textarea>
                            </div>

                            {{-- Flete Pagado --}}
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="flete_pagado"
                                    name="flete_pagado" {{ old('flete_pagado') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="flete_pagado">
                                    ¿Flete Pagado?
                                </label>
                            </div>

                            {{-- Botones Submit (Cotización o Pedido) --}}
                            <button type="submit" name="accion_guardado" value="cotizacion" class="btn text-white w-100 mb-2 shadow-sm" style="background-color: #17a2b8;"
                                @if($clientesParaSelector->isEmpty()) disabled @endif>
                                <i class="bi bi-save me-1"></i> Guardar como Cotización
                            </button>

                            <button type="submit" name="accion_guardado" value="pedido" class="btn btn-warning w-100 fw-bold shadow-sm" id="checkout"
                                @if($clientesParaSelector->isEmpty()) disabled @endif>
                                <i class="bi bi-check-circle me-1"></i> Realizar Pedido
                            </button>
                        </form>
                    </div>
                </div>

                {{-- TARJETA DE GENERAR COTIZACION (LA PRIMERA): Oculta el bloque por completo sin romper nada --}}
                @if(false)
                <div class="card border-info shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-info fw-bold mb-2"><i class="bi bi-file-earmark-pdf-fill"></i> ¿Necesitas Cotización?</h6>
                        <p class="small text-muted mb-3">Descarga un documento PDF con los datos de su cotización.</p>
                        
                        <form action="{{ route('carrito.pdf') }}" method="GET" target="_blank" id="form-pdf">
                            <input type="hidden" name="cliente_id" id="pdf_cliente_id">
                            <input type="hidden" name="descuento_manual" id="pdf_descuento_manual">
                            <input type="hidden" name="comentarios_pdf" id="pdf_comentarios">
                            
                            <button type="submit" class="btn btn-outline-info w-100">
                                <i class="bi bi-download me-1"></i> Generar Cotización como PDF
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <a href="/" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="bi bi-arrow-left me-1"></i>Continuar comprando
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ======================================================= --}}
{{-- ||       INICIA SCRIPT JAVASCRIPT REVISADO       || --}}
{{-- ======================================================= --}}
<script>
// Función para validar cantidad y recargar
function validarCantidad(input, idProducto, inner) {
    let cantidad = parseInt(input.value);
    if (isNaN(cantidad) || cantidad < 1) {
        alert("La cantidad debe ser mayor a 0");
        location.reload(); 
        return;
    }
    if (cantidad % inner !== 0) {
        alert(`La cantidad debe ser múltiplo del Inner (${inner}).\nEjemplos válidos: ${inner}, ${inner*2}, ${inner*3}...`);
        location.reload(); 
        return;
    }
    let urlBase = "{{ route('carrito.actualizar', ['producto_id' => 'ID_TEMP', 'cantidad' => 'CANT_TEMP']) }}";
    let urlFinal = urlBase.replace('ID_TEMP', idProducto).replace('CANT_TEMP', cantidad);
    window.location.href = urlFinal;
}

document.addEventListener('DOMContentLoaded', function() {
    const selectCliente = document.getElementById('cliente_id');
    const selectDescuentoManual = document.getElementById('descuento_manual');
    const divDescuentoManual = document.getElementById('div-descuento-manual');
    
    // Referencias a inputs ocultos del formulario PDF
    const pdfClienteInput = document.getElementById('pdf_cliente_id');
    const pdfDescuentoInput = document.getElementById('pdf_descuento_manual');
    
    // Referencias para Comentarios
    const comentariosTextarea = document.getElementById('comentarios'); 
    const pdfComentariosInput = document.getElementById('pdf_comentarios');

    // --- FUNCIÓN DE ACTUALIZACIÓN VISUAL (JS) ---
    function actualizarTotales() {
        if (!selectCliente) return;

        const ivaTasa = 0.16;
        const selectedOption = selectCliente.options[selectCliente.selectedIndex];
        let descuentoPct = 0;
        const codigoCliente = selectedOption.dataset.codigo; 
        const nombreCliente = selectedOption.dataset.nombre || '';

        // Si es GENERAL o el nombre dice MOSTRADOR, activamos el descuento dinámico
        if (codigoCliente === 'GENERAL' || nombreCliente.includes('VENTAS DE MOSTRADOR') || nombreCliente.includes('VENTAS MOSTRADOR')) {
            divDescuentoManual.style.display = 'block';
            descuentoPct = parseFloat(selectDescuentoManual.value) || 0;
        } else {
            divDescuentoManual.style.display = 'none';
            descuentoPct = parseFloat(selectedOption.dataset.descuento) || 0;
        }

        // Sincronizar inputs ocultos
        if(pdfClienteInput) pdfClienteInput.value = selectCliente.value;
        if(pdfDescuentoInput) pdfDescuentoInput.value = descuentoPct; 

        // Cálculos
        const resumenCard = document.getElementById('resumen-pedido-card');
        const subtotalBruto = parseFloat(resumenCard.dataset.subtotalBruto) || 0;
        const subtotalNetoGravableOrig = parseFloat(resumenCard.dataset.subtotalGravable) || 0;
        const subtotalNetoExentoOrig = parseFloat(resumenCard.dataset.subtotalExento) || 0;
        const subtotalNetoOrigTotal = subtotalNetoGravableOrig + subtotalNetoExentoOrig;

        const montoDescuento = subtotalBruto * (descuentoPct / 100);
        const subtotalNetoActual = subtotalBruto - montoDescuento;

        let gravableActual = 0;
        let exentoActual = 0;
        
        if (subtotalNetoOrigTotal > 0.001) { 
            const prop = subtotalNetoGravableOrig / subtotalNetoOrigTotal;
            gravableActual = subtotalNetoActual * prop;
            exentoActual = subtotalNetoActual * (1 - prop);
        } else {
            gravableActual = 0;
            exentoActual = subtotalNetoActual;
        }

        const iva = gravableActual * ivaTasa;
        const total = gravableActual + exentoActual + iva;

        // Actualizar DOM
        const fmt = (n) => `$${n.toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

        document.getElementById('resumen-descuento-texto').innerText = `Descuento (${descuentoPct.toFixed(0)}%)`;
        document.getElementById('resumen-descuento-monto').innerText = `-${fmt(montoDescuento)}`;
        document.getElementById('resumen-subtotal-neto-gravable').innerText = fmt(gravableActual);
        document.getElementById('resumen-subtotal-neto-exento').innerText = fmt(exentoActual);
        document.getElementById('resumen-iva-monto').innerText = `+${fmt(iva)}`;
        document.getElementById('orderTotal').innerText = fmt(total);

        // NUEVO: Actualizar los precios y subtotales por fila en tiempo real
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach(item => {
            const priceElement = item.querySelector('.base-price');
            if(priceElement) {
                const basePrice = parseFloat(priceElement.dataset.price);
                const currentPrice = basePrice - (basePrice * (descuentoPct / 100));
                
                // Actualizar texto del precio con descuento
                const descElement = item.querySelector('.discounted-price');
                if(descElement) descElement.innerText = fmt(currentPrice);
                
                // Actualizar el subtotal de la línea (Precio Descuento * Cantidad)
                const qtyInput = item.querySelector('.item-qty');
                if(qtyInput) {
                    const qty = parseInt(qtyInput.value) || 0;
                    const subElement = item.querySelector('.subtotal');
                    if(subElement) subElement.innerText = fmt(currentPrice * qty);
                }
            }
        });
    }

    if (selectCliente) {
        // EVENTO CHANGE: Recargar página para actualizar precios del servidor
        selectCliente.addEventListener('change', function() {
            const clienteId = this.value;
            if (clienteId) {
                // Al recargar, enviamos el ID. El controlador (función 'mostrar')
                // lo recibirá, actualizará la sesión y recalculará los precios.
                window.location.href = "?cliente_id=" + clienteId;
            }
        });

        // Para el descuento manual (solo visual, no requiere recarga de servidor)
        if (selectDescuentoManual) {
            selectDescuentoManual.addEventListener('change', actualizarTotales);
        }

        // Ejecutar visualización inicial
        actualizarTotales();
    }

    // Sincronizar Comentarios
    if (comentariosTextarea && pdfComentariosInput) {
        comentariosTextarea.addEventListener('input', function() {
            pdfComentariosInput.value = comentariosTextarea.value;
        });
        pdfComentariosInput.value = comentariosTextarea.value;
    }
});
</script>
@endsection
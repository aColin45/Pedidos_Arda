@extends('web.app')
@section('contenido')
<section class="py-5">
    <div class="container px-4 px-lg-12 my-5">
        <h2 class="fw-bold mb-4">Detalle de su Pedido</h2>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    {{-- ======================================================= --}}
                    {{-- ||       CARRITO        || --}}
                    {{-- ======================================================= --}}

                    {{-- Encabezado visible solo en pantallas medianas y grandes --}}
                    <div class="card-header bg-light d-none d-md-block">
                        <div class="row">
                            <div class="col-md-4"><strong>Producto</strong></div>
                            <div class="col-md-2 text-center"><strong>Precio</strong></div>
                            <div class="col-md-2 text-center"><strong>Inner</strong></div>
                            <div class="col-md-2 text-center"><strong>Cantidad</strong></div>
                            <div class="col-md-2 text-end"><strong>Subtotal</strong></div>
                        </div>
                    </div>

                    <div class="card-body" id="cartItems">
                        @forelse($carrito as $id => $item)
                        {{-- Estructura de Fila para cada item --}}
                        <div class="row align-items-center mb-3 cart-item border-bottom pb-3">
                            
                            {{-- Columna Producto (Siempre visible) --}}
                            <div class="col-12 col-md-4 mb-2 mb-md-0"> 
                                <div class="d-flex align-items-center">
                                    <img src="{{ $item['imagen'] ? asset('uploads/productos/' . $item['imagen']) : asset('assets/img/placeholder.png') }}"
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;"
                                         alt="{{ $item['nombre'] ?? 'Producto' }}">
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ $item['nombre'] ?? 'N/A' }}</h6>
                                        <small class="text-muted d-block">{{ $item['codigo'] ?? 'N/A' }}</small>
                                        @if (!($item['aplica_iva'] ?? true))
                                        <span class="badge bg-secondary text-white mt-1">No aplica IVA</span>
                                        @endif
                                        @if (($item['inner'] ?? 1) > 1)
                                        <span class="badge bg-warning text-dark mt-1">Múltiplo de
                                            {{ $item['inner'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Columnas Precio e Inner (Agrupadas en móvil) --}}
                            <div class="col-6 col-md-2 text-center text-md-center">
                                <small class="text-muted d-md-none">Precio:</small> 
                                <span class="fw-bold d-block d-md-inline">${{ number_format($item['precio'] ?? 0, 2) }}</span>
                            </div>
                            <div class="col-6 col-md-2 text-center text-md-center">
                                <small class="text-muted d-md-none">Inner:</small> 
                                <span class="text-muted d-block d-md-inline">{{ $item['inner'] ?? 1 }}</span>
                            </div>

                            {{-- Columna Cantidad (MODIFICADA PARA EDICIÓN MANUAL) --}}
                            <div class="col-6 col-md-2 d-flex justify-content-center align-items-center mt-2 mt-md-0">
                                <small class="text-muted d-md-none me-2">Cant:</small> 
                                <div class="input-group input-group-sm" style="max-width: 120px;">
                                    {{-- Botón Restar --}}
                                    <a class="btn btn-outline-secondary"
                                       href="{{ route('carrito.restar', ['producto_id' => $id]) }}"> - </a>
                                    
                                    {{-- INPUT EDITABLE 
                                         - step: Ayuda a sumar de inner en inner con flechas del teclado/navegador
                                         - onchange: Dispara la función JS validarCantidad
                                    --}}
                                    <input type="number" 
                                           class="form-control text-center px-1"
                                           value="{{ $item['cantidad'] ?? 0 }}" 
                                           min="{{ $item['inner'] ?? 1 }}"
                                           step="{{ $item['inner'] ?? 1 }}"
                                           onchange="validarCantidad(this, '{{ $id }}', {{ $item['inner'] ?? 1 }})"
                                    >
                                    
                                    {{-- Botón Sumar --}}
                                    <a href="{{ route('carrito.sumar', ['producto_id' => $id]) }}"
                                       class="btn btn-outline-secondary"> + </a>
                                </div>
                            </div>

                            {{-- Columna Subtotal y Eliminar --}}
                            <div class="col-6 col-md-2 d-flex align-items-center justify-content-end mt-2 mt-md-0">
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
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        {{ session('mensaje') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
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

            {{-- RESUMEN Y CHECKOUT (Sin cambios en lógica, solo visualización) --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Resumen del Pedido</h5>
                    </div>

                    <div class="card-body" id="resumen-pedido-card" data-subtotal-bruto="{{ $subtotalBruto ?? 0 }}"
                        data-subtotal-gravable="{{ $subtotalNetoGravable ?? 0 }}"
                        data-subtotal-exento="{{ $subtotalNetoExento ?? 0 }}">

                        {{-- Cliente en Curso --}}
                        @if(session('current_client_name'))
                        <div class="alert alert-info py-2 mb-3">
                            Pedido para: <strong>{{ session('current_client_name') }}</strong>
                        </div>
                        @endif

                        {{-- Subtotal Bruto --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal Bruto</span>
                            <span>${{ number_format($subtotalBruto ?? 0, 2) }}</span>
                        </div>

                        {{-- Descuento Aplicado --}}
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <strong id="resumen-descuento-texto">Descuento
                                ({{ number_format($descuentoCliente ?? 0, 2) }}%)</strong>
                            <strong id="resumen-descuento-monto">-${{ number_format($montoDescuento ?? 0, 2) }}</strong>
                        </div>

                        <hr class="my-2">

                        {{-- Subtotal Neto Desglosado --}}
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted"><small>Subtotal Neto Gravable (aplica IVA)</small></span>
                            <span class="fw-bold"
                                id="resumen-subtotal-neto-gravable">${{ number_format($subtotalNetoGravable ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><small>Subtotal Neto Exento (no aplica IVA)</small></span>
                            <span class="fw-bold"
                                id="resumen-subtotal-neto-exento">${{ number_format($subtotalNetoExento ?? 0, 2) }}</span>
                        </div>

                        {{-- Monto de IVA --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (16%)</span>
                            <span id="resumen-iva-monto">+${{ number_format($montoIVA ?? 0, 2) }}</span>
                        </div>

                        <hr class="my-3">

                        {{-- TOTAL FINAL --}}
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total Final</strong>
                            <strong id="orderTotal">${{ number_format($totalFinal ?? 0, 2) }}</strong>
                        </div>


                        <form action="{{route('pedido.realizar')}}" method="POST">
                            @csrf

                            {{-- Comentarios --}}
                            <div class="form-group mb-3">
                                <label for="comentarios" class="fw-bold mb-1">Comentarios u Observaciones
                                    (Opcional):</label>
                                <textarea name="comentarios" id="comentarios" class="form-control" rows="3"
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

                            {{-- Selector de Cliente --}}
                            <div class="form-group mb-3">
                                <label for="cliente_id" class="fw-bold mb-1">Asignar Pedido al Cliente:</label>
                                <select name="cliente_id" id="cliente_id"
                                    class="form-select @error('cliente_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccione un cliente --</option>
                                    @forelse($clientesParaSelector as $cliente)
                                    <option value="{{ $cliente->id }}" data-descuento="{{ $cliente->descuento }}"
                                        {{ old('cliente_id', session('current_client_id')) == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre }} ({{ $cliente->descuento }}% Dcto.)
                                    </option>
                                    @empty
                                    <option value="" disabled>No hay clientes disponibles.</option>
                                    @endforelse
                                </select>
                                @error('cliente_id')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Botón Submit --}}
                            <button type="submit" class="btn btn-primary w-100" id="checkout"
                                @if($clientesParaSelector->isEmpty()) disabled @endif>
                                <i class="bi bi-credit-card me-1"></i>
                                {{ $clientesParaSelector->isEmpty() ? 'No hay clientes disponibles' : 'Realizar pedido' }}
                            </button>
                        </form>

                        <a href="/" class="btn btn-outline-secondary w-100 mt-3">
                            <i class="bi bi-arrow-left me-1"></i>Continuar comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ======================================================= --}}
{{-- ||       INICIA SCRIPT JAVASCRIPT        || --}}
{{-- ======================================================= --}}
<script>
// --- FUNCIÓN DE VALIDACIÓN Y ACTUALIZACIÓN MANUAL ---
function validarCantidad(input, idProducto, inner) {
    let cantidad = parseInt(input.value);

    // 1. Validar que sea número y mayor a 0
    if (isNaN(cantidad) || cantidad < 1) {
        alert("La cantidad debe ser mayor a 0");
        location.reload(); // Recargar para deshacer cambios
        return;
    }

    // 2. Validar que sea múltiplo del Inner
    if (cantidad % inner !== 0) {
        alert(`La cantidad debe ser múltiplo del Inner (${inner}).\nEjemplos válidos: ${inner}, ${inner*2}, ${inner*3}...`);
        location.reload(); // Recargar para deshacer cambios
        return;
    }

    // 3. Si todo está bien, redirigir a la ruta de actualización
    // Creamos la URL usando el helper de Laravel pero con placeholders
    // NOTA: Asegúrate de tener definida la ruta 'carrito.actualizar' en web.php
    let urlBase = "{{ route('carrito.actualizar', ['producto_id' => 'ID_TEMP', 'cantidad' => 'CANT_TEMP']) }}";
    
    // Reemplazamos los marcadores con los datos reales
    let urlFinal = urlBase.replace('ID_TEMP', idProducto).replace('CANT_TEMP', cantidad);
    
    // Redirigimos para procesar en backend
    window.location.href = urlFinal;
}

// --- TU SCRIPT EXISTENTE DE TOTALES ---
document.addEventListener('DOMContentLoaded', function() {
    const selectCliente = document.getElementById('cliente_id');

    function actualizarTotales() {
        if (!selectCliente) return;

        const ivaTasa = 0.16;
        const selectedOption = selectCliente.options[selectCliente.selectedIndex];
        const descuentoPct = parseFloat(selectedOption.dataset.descuento) || 0;

        const resumenCard = document.getElementById('resumen-pedido-card');
        const subtotalBruto = parseFloat(resumenCard.dataset.subtotalBruto) || 0;
        const subtotalNetoGravableOriginal = parseFloat(resumenCard.dataset.subtotalGravable) || 0;
        const subtotalNetoExentoOriginal = parseFloat(resumenCard.dataset.subtotalExento) || 0;
        const subtotalNetoOriginalTotal = subtotalNetoGravableOriginal + subtotalNetoExentoOriginal;

        const montoDescuento = subtotalBruto * (descuentoPct / 100);
        const subtotalNetoTotalActual = subtotalBruto - montoDescuento;

        let subtotalNetoGravableActual = 0;
        let subtotalNetoExentoActual = 0;
        
        if (subtotalNetoOriginalTotal > 0.001) { 
            const proporcionGravable = subtotalNetoGravableOriginal / subtotalNetoOriginalTotal;
            subtotalNetoGravableActual = subtotalNetoTotalActual * proporcionGravable;
            subtotalNetoExentoActual = subtotalNetoTotalActual * (1 - proporcionGravable);
        } else {
            subtotalNetoGravableActual = 0;
            subtotalNetoExentoActual = subtotalNetoTotalActual; 
        }

        const montoIva = subtotalNetoGravableActual * ivaTasa;
        const totalFinal = subtotalNetoGravableActual + subtotalNetoExentoActual + montoIva;

        const formatCurrency = (num) => {
            const number = parseFloat(num); 
            if (isNaN(number)) return '$0.00';
            return `$${number.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        const descuentoTextoEl = document.getElementById('resumen-descuento-texto');
        if (descuentoTextoEl) descuentoTextoEl.innerText = `Descuento (${descuentoPct.toFixed(2)}%)`;

        const descuentoMontoEl = document.getElementById('resumen-descuento-monto');
        if (descuentoMontoEl) descuentoMontoEl.innerText = `-${formatCurrency(montoDescuento)}`;

        const subtotalNetoGravableEl = document.getElementById('resumen-subtotal-neto-gravable');
        if (subtotalNetoGravableEl) subtotalNetoGravableEl.innerText = formatCurrency(subtotalNetoGravableActual);

        const subtotalNetoExentoEl = document.getElementById('resumen-subtotal-neto-exento');
        if (subtotalNetoExentoEl) subtotalNetoExentoEl.innerText = formatCurrency(subtotalNetoExentoActual);

        const ivaMontoEl = document.getElementById('resumen-iva-monto');
        if (ivaMontoEl) ivaMontoEl.innerText = `+${formatCurrency(montoIva)}`;

        const orderTotalEl = document.getElementById('orderTotal');
        if (orderTotalEl) orderTotalEl.innerText = formatCurrency(totalFinal);
    }

    if (selectCliente) {
        selectCliente.addEventListener('change', actualizarTotales);
        if (selectCliente.value || document.querySelectorAll('#cartItems .cart-item').length > 0) {
            actualizarTotales();
        }
    }
});
</script>
@endsection
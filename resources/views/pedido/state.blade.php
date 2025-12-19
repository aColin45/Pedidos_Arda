{{-- Modal para Cambiar Estado --}}
<div class="modal fade" id="modal-estado-{{$reg->id}}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <form action="{{ route('pedido.cambiarEstado', $reg->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar estado del pedido #{{$reg->id}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <p>
                        <strong>Estado actual:</strong> 
                        {{-- Usamos los mismos colores que definimos en index --}}
                        @php
                        $coloresState = [
                            'pendiente' => 'bg-warning',
                            'parcialmente_surtido' => 'bg-info text-dark',
                            'enviado' => 'bg-success',
                            'enviado_completo' => 'bg-success',
                            'anulado' => 'bg-danger',
                            'cancelado' => 'bg-secondary',
                            'entregado' => 'bg-primary'
                        ];
                        @endphp
                        <span class="badge {{ $coloresState[$reg->estado] ?? 'bg-dark' }}">
                            {{ ucfirst(str_replace('_', ' ', $reg->estado)) }}
                        </span>
                    </p>

                    <div class="form-group">
                        <label for="estado-{{$reg->id}}">Seleccione el nuevo estado:</label>
                        <select name="estado" id="estado-{{$reg->id}}" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            
                            {{-- ========================================================== --}}
                            {{-- ||       LÓGICA DE TRANSICIONES DE ESTADO               || --}}
                            {{-- ========================================================== --}}

                            {{-- 1. SI ESTÁ PENDIENTE --}}
                            @if ($reg->estado == 'pendiente')
                                @can('pedido-cancel')
                                    <option value="cancelado">Cancelar Pedido</option>
                                @endcan
                                
                                @can('pedido-anulate')
                                    {{-- Nuevas opciones para el almacén --}}
                                    <option value="parcialmente_surtido">Marcar como Parcialmente Surtido</option>
                                    <option value="enviado_completo">Marcar como Enviado Completo</option>
                                @endcan
                            
                            {{-- 2. SI ESTÁ PARCIALMENTE SURTIDO --}}
                            @elseif ($reg->estado == 'parcialmente_surtido')
                                @can('pedido-anulate')
                                    {{-- De parcial solo puede pasar a completo --}}
                                    <option value="enviado_completo">Completar Envío (Enviado Completo)</option>
                                @endcan

                            {{-- 3. SI YA FUE ENVIADO (O ENVIADO COMPLETO) --}}
                            @elseif ($reg->estado == 'enviado' || $reg->estado == 'enviado_completo')
                                @can('pedido-anulate')
                                    <option value="entregado">Marcar como Entregado</option>
                                    <option value="anulado">Anular Pedido</option>
                                @endcan
                            
                            @endif
                            
                        </select>
                    </div>

                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Cambiar estado</button>
                </div>

            </form>
        </div>
    </div>
</div>
{{-- Este es el Modal para Cambiar Estado --}}
<div class="modal fade" id="modal-estado-{{$reg->id}}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            
            {{-- Usamos la ruta 'pedido.cambiarEstado' --}}
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
                        {{-- Usamos la variable $colores definida en index.blade.php --}}
                        <span class="badge {{ $colores[$reg->estado] ?? 'bg-dark' }}">
                            {{ ucfirst($reg->estado) }}
                        </span>
                    </p>

                    <div class="form-group">
                        <label for="estado-{{$reg->id}}">Seleccione el nuevo estado:</label> {{-- ID único para el label/select --}}
                        <select name="estado" id="estado-{{$reg->id}}" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            
                            {{-- ========================================================== --}}
                            {{-- ||       LÓGICA DE ESTADOS Y PERMISOS                   || --}}
                            {{-- ========================================================== --}}

                            @if ($reg->estado == 'pendiente')
                                {{-- Agente puede cancelar si tiene permiso --}}
                                @can('pedido-cancel')
                                    <option value="cancelado">Cancelar Pedido</option>
                                @endcan
                                
                                {{-- Admin puede enviar si tiene permiso --}}
                                @can('pedido-anulate')
                                    <option value="enviado">Marcar como Enviado</option>
                                @endcan
                            
                            @elseif ($reg->estado == 'enviado')
                                {{-- Admin puede marcar como entregado O anular si tiene permiso --}}
                                @can('pedido-anulate')
                                    <option value="entregado">Marcar como Entregado</option> {{-- <-- NUEVA OPCIÓN --}}
                                    <option value="anulado">Anular Pedido</option>
                                @endcan
                            
                            @endif
                            
                            {{-- Pedidos en estado 'cancelado', 'anulado' o 'entregado' no tienen más opciones --}}
                            
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
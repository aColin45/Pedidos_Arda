<div class="modal fade" id="modal-estado-{{$reg->id}}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- AGREGAMOS LA CLASE form-confirmar Y LOS DATOS DE SWEETALERT2 --}}
            <form action="{{ route('pedido.cambiarEstado', $reg->id) }}" method="POST" class="form-confirmar"
                  data-title="¿Actualizar Pedido?" 
                  data-text="El pedido cambiará al nuevo estado que hayas seleccionado." 
                  data-icon="question" 
                  data-color="#0d6efd" 
                  data-btn-text="Sí, actualizar">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar estado del pedido #{{$reg->id}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        <strong>Estado actual:</strong> 
                        @php
                        $coloresState = [
                            'pendiente' => 'bg-warning text-dark',
                            'aprobado' => 'bg-success',
                            'parcialmente_surtido' => 'bg-info text-dark',
                            'enviado' => 'bg-primary',
                            'enviado_completo' => 'bg-primary',
                            'entregado' => 'bg-success',
                            'rechazado' => 'bg-danger',
                            'anulado' => 'bg-danger',
                            'cancelado' => 'bg-secondary'
                        ];
                        @endphp
                        <span class="badge {{ $coloresState[$reg->estado] ?? 'bg-dark' }}">
                            {{ ucfirst(str_replace('_', ' ', $reg->estado)) }}
                        </span>
                    </p>

                    <div class="form-group">
                        <label for="estado-{{$reg->id}}">Seleccione el nuevo estado:</label>
                        <select name="estado" id="estado-{{$reg->id}}" class="form-select" onchange="mostrarMotivo(this, {{$reg->id}})" required>
                            <option value="">-- Seleccionar --</option>
                            
                            @if ($reg->estado == 'pendiente' || $reg->estado == 'rechazado')
                                @can('pedido-anulate')
                                    <option value="aprobado">Aceptar Pedido (Pasar a Logística)</option>
                                    @if($reg->estado == 'pendiente')
                                        <option value="rechazado">Rechazar Pedido (No procede)</option>
                                    @endif
                                @endcan
                                
                                @if($reg->estado == 'pendiente')
                                    @can('pedido-cancel')
                                        <option value="cancelado">Cancelar mi Pedido</option>
                                    @endcan
                                @endif
                            
                            @elseif ($reg->estado == 'aprobado')
                                @can('pedido-anulate')
                                    <option value="parcialmente_surtido">Marcar como Parcialmente Surtido</option>
                                    <option value="enviado_completo">Marcar como Enviado Completo</option>
                                    <option value="cancelado">Cancelar Pedido</option>
                                    {{-- NUEVA OPCIÓN PARA REVERTIR A RECHAZADO --}}
                                    <option value="rechazado">Rechazar Pedido (Revertir aprobación)</option>
                                @endcan

                            @elseif ($reg->estado == 'parcialmente_surtido')
                                @can('pedido-anulate')
                                    <option value="enviado_completo">Completar Envío (Enviado Completo)</option>
                                    {{-- OPCIONAL: Si también quieres poder cancelarlo cuando está parcialmente surtido --}}
                                    <option value="cancelado">Cancelar Resto del Pedido</option>
                                @endcan

                            @elseif ($reg->estado == 'enviado' || $reg->estado == 'enviado_completo')
                                @can('pedido-anulate')
                                    <option value="entregado">Marcar como Entregado</option>
                                    <option value="anulado">Anular Pedido</option>
                                @endcan
                            @endif
                        </select>
                    </div>

                    <div class="form-group mt-3" id="divMotivo-{{$reg->id}}" style="display: none;">
                        <label class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Motivo del Rechazo/Cancelación:</label>
                        <textarea name="motivo_rechazo" id="motivo_rechazo-{{$reg->id}}" class="form-control" rows="3" placeholder="Explique por qué se rechaza o cancela este pedido..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="fas fa-save me-1"></i> Cambiar estado</button>
                </div>
            </form>
        </div>
    </div>
</div>
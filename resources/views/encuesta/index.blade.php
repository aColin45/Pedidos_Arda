@extends('plantilla.app')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-top-primary">
        <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold text-primary mb-3">
                <i class="fas fa-chart-line mr-2"></i> Seguimiento de Encuesta de Mercado
            </h3>
            
            <div class="mb-3">
                {{-- Enlace HTML directo: Infalible, seguro y sin Javascript --}}
                <a href="{{ route('encuesta.exportar') }}" class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Exportar a Excel
                </a>
                <a href="{{ route('encuesta.responder') }}" class="btn btn-outline-info btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-eye mr-1"></i> Previsualizar Encuesta
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-top-0" style="padding-left: 20px;">Agente de Ventas</th>
                            <th class="border-top-0 text-center">Estado</th>
                            <th class="border-top-0">Fecha de Respuesta</th>
                            <th class="border-top-0 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentes as $agente)
                        <tr>
                            <td class="align-middle" style="padding-left: 20px;">
                                <span class="font-weight-bold text-dark d-block">{{ $agente->name }}</span>
                                <small class="text-muted">{{ $agente->email }}</small>
                            </td>
                            <td class="align-middle text-center">
                                @if($agente->encuestaRespuesta)
                                    <span class="badge shadow-sm px-3 py-2" style="font-size: 0.85rem; background-color: #28a745 !important; color: white !important; display: inline-block; min-width: 110px;">
                                        <i class="fas fa-check-circle mr-1"></i> COMPLETADA
                                    </span>
                                @else
                                    <span class="badge shadow-sm px-3 py-2" style="font-size: 0.85rem; background-color: #dc3545 !important; color: white !important; display: inline-block; min-width: 110px;">
                                        <i class="fas fa-exclamation-circle mr-1"></i> PENDIENTE
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle text-muted">
                                @if($agente->encuestaRespuesta)
                                    <i class="far fa-calendar-alt mr-1"></i> {{ $agente->encuestaRespuesta->created_at->format('d/m/Y') }}
                                    <br><i class="far fa-clock mr-1"></i> {{ $agente->encuestaRespuesta->created_at->format('h:i A') }}
                                @else
                                    ---
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @if($agente->encuestaRespuesta)
                                    <a href="{{ route('encuesta.detalle', $agente->encuestaRespuesta->id) }}" class="btn btn-primary btn-sm px-3 font-weight-bold shadow-sm">
                                        <i class="fas fa-search-plus mr-1"></i> Ver Detalles
                                    </a>
                                @else
                                    <button class="btn btn-light btn-sm px-3 text-muted border" disabled>
                                        <i class="fas fa-ban mr-1"></i> Bloqueado
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('plantilla.app')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-top-primary">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-primary">
                        <i class="fas fa-poll-h mr-2"></i> Informe de Encuesta: {{ $respuesta->user->name }}
                    </h3>
                    <a href="{{ route('encuesta.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                    </a>
                </div>
                <div class="card-body bg-light">
                    @php
                        $resp = $respuesta->respuestas;
                        $diccionario = [
                            'p1_canal' => '1. ¿Qué tipo de canal conforman la mayoría de tus clientes?',
                            'p1_otros' => '1. Otros canales especificados:',
                            'p2' => '2. ¿Tus clientes actualmente venden o manejan accesorios de gas LP?',
                            'p3' => '3. ¿Qué tan importante sería para tus clientes tener reguladores de gas?',
                            'p4' => '4. ¿Con qué frecuencia te han pedido tus clientes reguladores?',
                            'p5' => '5. Rango de precio al público aceptado:',
                            'p6' => '6. Precio de mayoreo que pagan actualmente:',
                            'p7' => '7. Si pudieras ofrecer manguera + regulador como kit:',
                            'comentarios' => 'Comentarios Adicionales:'
                        ];
                    @endphp

                    <div class="list-group shadow-sm">
                        @foreach($diccionario as $key => $pregunta)
                            <div class="list-group-item">
                                <h6 class="mb-1 font-weight-bold text-dark">{{ $pregunta }}</h6>
                                <p class="mb-1 text-primary" style="font-size: 1.15rem;">
                                    @if(isset($resp[$key]))
                                        @if(is_array($resp[$key]))
                                            {{ implode(', ', $resp[$key]) }}
                                        @else
                                            {{ $resp[$key] }}
                                        @endif
                                    @else
                                        <em class="text-muted small">No proporcionado</em>
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-white text-muted small">
                    Respuesta registrada el: {{ $respuesta->created_at->format('d/m/Y h:i A') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('plantilla.app')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            
            <div class="card shadow-sm border-top-primary">
                <div class="card-header bg-white pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="font-weight-bold text-primary mb-0">
                            <i class="fas fa-poll-h mr-2"></i> Sondeo de Mercado: Reguladores de Gas
                        </h3>
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin'))
                            <span class="badge badge-info p-2"><i class="fas fa-eye mr-1"></i> Modo Previsualización</span>
                        @endif
                    </div>
                </div>

                <div class="card-body bg-light">
                    {{-- SECCIÓN DE IMÁGENES ILUSTRATIVAS --}}
                    <div class="row mb-4">
                        <div class="col-md-6 text-center">
                            <img src="{{ asset('assets/img/regulador1.png') }}" class="img-fluid rounded shadow-sm border" alt="Referencia 1" style="max-height: 250px;">
                        </div>
                        <div class="col-md-6 text-center">
                            <img src="{{ asset('assets/img/regulador2.png') }}" class="img-fluid rounded shadow-sm border" alt="Referencia 2" style="max-height: 250px;">
                        </div>
                    </div>

                    <form action="{{ route('encuesta.guardar') }}" method="POST" id="formEncuesta">
                        @csrf

                        <div class="form-group bg-white p-4 rounded border mb-4">
                            <label class="font-weight-bold text-dark">1. ¿Qué tipo de canal conforman la mayoría de tus clientes? <span class="text-danger">*</span> <small class="text-muted">(Marca hasta 2 opciones)</small></label>
                            @foreach(['Distribuidores', 'Mayoristas', 'Ferreterías independientes', 'Tlapalerías', 'Tiendas de materiales para construcción'] as $opcion)
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input limit-checkbox" name="p1_canal[]" id="c{{ $loop->index }}" value="{{ $opcion }}">
                                    <label class="custom-control-label" for="c{{ $loop->index }}">{{ $opcion }}</label>
                                </div>
                            @endforeach
                            <div class="mt-3">
                                <label class="small font-weight-bold">Otros:</label>
                                <input type="text" name="p1_otros" class="form-control form-control-sm" placeholder="Especificar...">
                            </div>
                        </div>

                        @php
                            $preguntas = [
                                'p2' => ['t' => '2. ¿Tus clientes actualmente venden o manejan accesorios de gas LP?', 'o' => ['Sí, la mayoría', 'Algunos', 'Muy pocos / ninguno']],
                                'p3' => ['t' => '3. ¿Qué tan importante sería para tus clientes tener reguladores de gas de una vía en su catálogo?', 'o' => ['Muy importante', 'Importante, pero no indispensable', 'Poco importante', 'No saben / no les importa']],
                                'p4' => ['t' => '4. ¿Con qué frecuencia te han pedido tus clientes reguladores de gas LP?', 'o' => ['Muy Frecuentemente', 'Ocasionalmente', 'Rara vez', 'Nunca me lo han pedido']],
                                'p5' => ['t' => '5. ¿Qué rango de precio al público crees que aceptaría tu mercado para un regulador de calidad con certificación NOM?', 'o' => ['$150 MXN', '$151–$160 MXN', '$161–$200 MXN']],
                                'p6' => ['t' => '6. ¿A qué precio de mayoreo venden esos reguladores tus clientes actualmente (lo que ellos pagan)?', 'o' => ['No tengo ese dato', 'Menos de $100 MXN', '$120–$150 MXN', 'Más de $170 MXN']],
                                'p7' => ['t' => '7. Si pudieras ofrecer manguera + regulador como kit, ¿crees que tus clientes lo comprarían junto?', 'o' => ['Sí, sería más fácil venderlos así', 'Algunos preferirían el kit, otros por separado', 'No creo, prefieren comprar suelto', 'No sé, nunca lo he ofrecido así']]
                            ];
                        @endphp

                        @foreach($preguntas as $key => $p)
                            <div class="form-group bg-white p-4 rounded border mb-4">
                                <label class="font-weight-bold text-dark">{{ $p['t'] }} <span class="text-danger">*</span></label>
                                @foreach($p['o'] as $opt)
                                    <div class="custom-control custom-radio mt-2">
                                        <input type="radio" class="custom-control-input" name="{{ $key }}" id="{{ $key . $loop->index }}" value="{{ $opt }}" required>
                                        <label class="custom-control-label" for="{{ $key . $loop->index }}">{{ $opt }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="form-group bg-white p-4 rounded border mb-4">
                            <label class="font-weight-bold text-dark">Comentarios adicionales:</label>
                            <textarea name="comentarios" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle mr-1"></i> Las respuestas se revisan de forma generalizada para fines estadísticos.
                        </div>

                        <div class="text-right mt-4 pb-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary mr-2 font-weight-bold">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                                <i class="fas fa-paper-plane mr-1"></i> Enviar Encuesta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.limit-checkbox');
        checkboxes.forEach(box => {
            box.addEventListener('change', function() {
                if (document.querySelectorAll('.limit-checkbox:checked').length > 2) {
                    this.checked = false;
                    alert('Por favor selecciona un máximo de 2 opciones para la pregunta 1.');
                }
            });
        });
    });
</script>
@endsection